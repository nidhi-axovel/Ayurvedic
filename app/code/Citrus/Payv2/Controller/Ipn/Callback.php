<?php
/** 
 * @copyright  Citruspay
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Citrus\Payv2\Controller\Ipn;

use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\App\Action\Action as AppAction;

class Callback extends AppAction
{
    /**
    * @var \Citrus\Payv2\Model\PaymentMethod
    */
    protected $_paymentMethod;

    /**
    * @var \Magento\Sales\Model\Order
    */
    protected $_order;

    /**
    * @var \Magento\Sales\Model\OrderFactory
    */
    protected $_orderFactory;

    /**
    * @var Magento\Sales\Model\Order\Email\Sender\OrderSender
    */
    protected $_orderSender;

    /**
    * @var \Psr\Log\LoggerInterface
    */
    protected $_logger;
	

    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Sales\Model\OrderFactory $orderFactory
    * @param \Citrus\Payv2\Model\PaymentMethod $paymentMethod
    * @param Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    * @param  \Psr\Log\LoggerInterface $logger
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Citrus\Payv2\Model\PaymentMethod $paymentMethod,
    \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,	
    \Psr\Log\LoggerInterface $logger
    ) {
        $this->_paymentMethod = $paymentMethod;
        $this->_orderFactory = $orderFactory;
        $this->_client = $this->_paymentMethod->getClient();
        $this->_orderSender = $orderSender;		
        $this->_logger = $logger;		
        parent::__construct($context);
    }

    /**
    * Handle POST request to Citrus callback endpoint.
    */
    public function execute()
    {
        try {
            // Cryptographically verify authenticity of callback
            if($this->getRequest()->isPost())
			{				
				$this->_success();
				$this->paymentAction();
			}
			else
			{
	            $this->_logger->addError("Citrus: no post back data received in callback");
				return $this->_failure();
			}
        } catch (Exception $e) {
            $this->_logger->addError("Citrus: error processing callback");
            $this->_logger->addError($e->getMessage());
            return $this->_failure();
        }
		
		$this->_logger->addInfo("Citrus Transaction END from Citruspay");
    }
	
	protected function paymentAction()
	{
		$txnid = "";
		$txnrefno = "";
		$txStatus = "";
		$txnmsg = "";
		$firstName = "";
		$lastName = "";
		$email = "";
		$street1 = "";
		$city = "";
		$state = "";
		$country = "";
		$pincode = "";
		$mobileNo = "";
		$signature = "";
		$reqsignature = "";
		$data = "";
		$flag = "dataValid";
		$respdata = "";
		
		
		$apiKey=$this->_paymentMethod->getConfigData('apikey');
		
		$orderid=-1;
		
		
		$postdata = $this->getRequest()->getPost();
					
		$txnid = $postdata['TxId'];
		$data .= $txnid;
		$respdata .= "<br/><strong>Citrus Transaction Id: </strong>".$txnid;
		
		$orderid=$txnid;
		$this->_loadOrder($orderid);
		
		$txStatus = $postdata['TxStatus'];
		$data .= $txStatus;
		$respdata .= "<br/><strong>Transaction Status: </strong>".$txStatus;
		
		$amount = $postdata['amount'];
		$data .= $amount;
		$respdata .= "<br/><strong>Amount: </strong>".$amount;
		
		$pgtxnno = $postdata['pgTxnNo'];
		$data .= $pgtxnno;
		$respdata .= "<br/><strong>PG Transaction Number: </strong>".$pgtxnno;
		
		$issuerrefno = $postdata['issuerRefNo'];
		$data .= $issuerrefno;
		$respdata .= "<br/><strong>Issuer Reference Number: </strong>".$issuerrefno;
		
		$authidcode = $postdata['authIdCode'];
		$data .= $authidcode;
		$respdata .= "<br/><strong>Auth ID Code: </strong>".$authidcode;
		
		$firstName = $postdata['firstName'];
		$data .= $firstName;
		$respdata .= "<br/><strong>First Name: </strong>".$firstName;
		
		$lastName = $postdata['lastName'];
		$data .= $lastName;
		$respdata .= "<br/><strong>Last Name: </strong>".$lastName;
		
		$pgrespcode = $postdata['pgRespCode'];
		$data .= $pgrespcode;
		$respdata .= "<br/><strong>PG Response Code: </strong>".$pgrespcode;
		
		$pincode = $postdata['addressZip'];
		$data .= $pincode;
		$respdata .= "<br/><strong>PinCode: </strong>".$pincode;
		
		$signature = $postdata['signature'];	
		$respSignature = self::_generateHmacKey($data,$apiKey);
		
		if($signature != "" && strcmp($signature, $respSignature) != 0)
		{
			$flag = "dataTampered";
		}
		
		$txMsg = 'CitrusPay: '.$postdata['TxMsg'];
		$respdata .= "<br/><strong>Citrus Transaction Message: </strong>".$txMsg;
		$txnGateway = $postdata['TxGateway'];
		$respdata .= "<br/><strong>Transaction Gateway: </strong>".$txnGateway;
		
		$paymentMode = (isset($postdata['paymentMode']))? $postdata['paymentMode'] : '';
		$cardType = (isset($postdata['cardType']))? $postdata['cardType']: '';
		$maskedCardNumber = (isset($postdata['maskedCardNumber']))? $postdata['maskedCardNumber'] : '';
		if($paymentMode=='CREDIT_CARD' || $paymentMode=='DEBIT_CARD')
		{
			$txMsg .= '. Paid by '.$cardType.' Card (No.'.$maskedCardNumber.').';
		}
		elseif($paymentMode=='NET_BANKING') {
			$txMsg .= '. Paid by Net Banking.';
		}
		//$this->_logger->addInfo("Citrus Response Message is ".$txMsg."-paymentmode:".$paymentMode."-cardType:".$cardType."-maskedCardNumber:".$maskedCardNumber);
		
		if(strtoupper($txStatus) == 'SUCCESS')
		{
			if($flag != "dataValid")
			{	
		        //$this->_order->hold()->save();
				$this->_createCitrusComment("Citrus Response signature does not match. You might have received tampered data", true);
				$this->_order->cancel()->save();

				$this->_logger->addError("Citrus Response signature did not match ");

				//AA display error to customer = where ???
				$this->messageManager->addError("<strong>Error:</strong> Citrus Response signature does not match. You might have received tampered data");
				$this->_redirect('checkout/onepage/failure');
				
			}else{			
				$this->_registerPaymentCapture($pgtxnno, $amount, $txMsg);
				//$this->_logger->addInfo("Citrus Response Order success..".$txMsg);
				
				$redirectUrl = $this->_paymentMethod->getSuccessUrl();
				//AA Where 
				$this->_redirect($redirectUrl);
			}
		}
		else
		{
			//$this->_order->hold()->save();			
			$enquiryurl = $this->_paymentMethod->getEnquiryUrl($txnid);
			
			//Enquiry API will be implemented in future ****************************
			$historymessage = $txMsg;//.'<br/>View Citrus Payment using the following URL: '.$enquiryurl;
			
			$this->_createCitrusComment($historymessage);
			$this->_order->cancel()->save();				

			//$this->_logger->addInfo("Citrus Response Order cancelled ..");
			
			$this->messageManager->addError("<strong>Error:</strong> $txMsg <br/>");
			//AA where 
			$redirectUrl = $this->_paymentMethod->getCancelUrl();
			$this->_redirect($redirectUrl);
		}		
		
	}
	

	//AA - To review - required 
    protected function _registerPaymentCapture($transactionId, $amount, $message)
    {
        $payment = $this->_order->getPayment();
		
		
        $payment->setTransactionId($transactionId)       
        ->setPreparedMessage($this->_createCitrusComment($message))
        ->setShouldCloseParentTransaction(true)
        ->setIsTransactionClosed(0)
        ->registerCaptureNotification(
		//AA
            $amount,
            true 
        );

        $this->_order->save();

        $invoice = $payment->getCreatedInvoice();
        if ($invoice && !$this->_order->getEmailSent()) {
            $this->_orderSender->send($this->_order);
            $this->_order->addStatusHistoryComment(
                __('You notified customer about invoice #%1.', $invoice->getIncrementId())
            )->setIsCustomerNotified(
                true
            )->save();
        }
    }

	//AA Done
    protected function _loadOrder($order_id)
    {
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($order_id);

        if (!$this->_order && $this->_order->getId()) {
            throw new Exception('Could not find Magento order with id $order_id');
        }
    }

	//AA Done
    protected function _success()
    {
        $this->getResponse()
             ->setStatusHeader(200);
    }

	//AA Done
    protected function _failure()
    {
        $this->getResponse()
             ->setStatusHeader(400);
    }

    /**
    * Returns the generated comment or order status history object.
    *
    * @return string|\Magento\Sales\Model\Order\Status\History
    */
	//AA Done
    protected function _createCitrusComment($message = '')
    {       
        if ($message != '')
        {
            $message = $this->_order->addStatusHistoryComment($message);
            $message->setIsCustomerNotified(null);
        }
		
        return $message;
    }
	
	public function _generateHmacKey($data, $apiKey=null){
		//$hmackey = Zend_Crypt_Hmac::compute($apiKey, "sha1", $data);
		$hmackey = hash_hmac('sha1',$data,$apiKey);
		return $hmackey;
	}
}
