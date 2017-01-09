<?php
/** 
 *
 * @copyright  Citruspay
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Citrus\Payv2\Model;

use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'citrus';
    protected $_isInitializeNeeded = true;

    /**
    * @var \Magento\Framework\Exception\LocalizedExceptionFactory
    */
    protected $_exception;

    /**
    * @var \Magento\Sales\Api\TransactionRepositoryInterface
    */
    protected $_transactionRepository;

    /**
    * @var Transaction\BuilderInterface
    */
    protected $_transactionBuilder;

    /**
    * @var \Magento\Framework\UrlInterface
    */
    protected $_urlBuilder;

    /**
    * @var \Magento\Sales\Model\OrderFactory
    */
    protected $_orderFactory;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;

    /**
    * @param \Magento\Framework\UrlInterface $urlBuilder
    * @param \Magento\Framework\Exception\LocalizedExceptionFactory $exception
    * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
    * @param Transaction\BuilderInterface $transactionBuilder
    * @param \Magento\Sales\Model\OrderFactory $orderFactory
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\Model\Context $context
    * @param \Magento\Framework\Registry $registry
    * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
    * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
    * @param \Magento\Payment\Helper\Data $paymentData
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param \Magento\Payment\Model\Method\Logger $logger
    * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
    * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
    * @param array $data
    */
    public function __construct(
      \Magento\Framework\UrlInterface $urlBuilder,
      \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
      \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
      \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
      \Magento\Sales\Model\OrderFactory $orderFactory,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\Model\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
      \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
      \Magento\Payment\Helper\Data $paymentData,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Payment\Model\Method\Logger $logger,
      \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
      \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
      array $data = []
    ) {
      $this->_urlBuilder = $urlBuilder;
      $this->_exception = $exception;
      $this->_transactionRepository = $transactionRepository;
      $this->_transactionBuilder = $transactionBuilder;
      $this->_orderFactory = $orderFactory;
      $this->_storeManager = $storeManager;

      parent::__construct(
          $context,
          $registry,
          $extensionFactory,
          $customAttributeFactory,
          $paymentData,
          $scopeConfig,
          $logger,
          $resource,
          $resourceCollection,
          $data
      );
    }

    /**
     * Instantiate state and set it to state object.
     *
     * @param string                        $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

	
	//AA Done
	public function _generateHmacKey($data, $apiKey=null){
		//$hmackey = Zend_Crypt_Hmac::compute($apiKey, "sha1", $data);
		$hmackey = hash_hmac('sha1',$data,$apiKey);
		return $hmackey;
	}

	
	public function getPostHTML($order, $storeId = null)
    {
		
		//$this->_logger->addError("Generate HTML");
		
		$apiKey = $this->getConfigData('apikey');
		$vanityUrl = $this->getConfigData('vanityurl');
		$env = $this->getConfigData('environment');
		
		$returnUrl = self::getReturnUrl();
		$notifyUrl = self::getNotifyUrl();

		$txnid = $order->getIncrementId();	
		$amount = $order->getGrandTotal();	
		$currency = "INR";
		$billingAddress = $order->getBillingAddress();;
		$firstName = $billingAddress->getFirstname();
		$lastName = $billingAddress->getLastname();
		$email = $billingAddress->getEmail();
		$street = '';
		$starr = $billingAddress->getStreet();
		if (isset($starr[0]))
		{
			$street = $starr[0];
		}
		$city = $billingAddress->getCity();
		$postcode = $billingAddress->getPostcode();
		$region = $billingAddress->getRegion();
		$country = $billingAddress->getCountry();
		$telephone = $billingAddress->getTelephone();

		//create security signature
		$data = "$vanityUrl$amount$txnid$currency";
		$signatureData = self::_generateHmacKey($data, $apiKey);

		//AA check for ssl2
		//setup url  SSLV2
		$sslPage = "https://sandbox.citruspay.com/sslperf/";
		if ($env == 'production')
			$sslPage = "https://www.citruspay.com/sslperf/";
		elseif ($env == 'staging')
			$sslPage = "https://stg.citruspay.com/sslperf/";
			
		$form = '<form id="CPForm" name="citruspay_checkout" method="POST" class="citruspay_checkout" action="'.$sslPage.$vanityUrl.'">';

		$form.= $this->addHiddenField(array('name'=>'secSignature', 'value'=>$signatureData));
		$form.= $this->addHiddenField(array('name'=>'merchantTxnId', 'value'=>$txnid));
		$form.= $this->addHiddenField(array('name'=>'orderAmount', 'value'=>$amount));
		$form.= $this->addHiddenField(array('name'=>'currency', 'value'=>$currency));
		
		if ($firstName != null && $firstName != '')
			$form.= $this->addHiddenField(array('name'=>'firstName', 'value'=>$firstName));
		if ($lastName != null && $lastName != '')
			$form.= $this->addHiddenField(array('name'=>'lastName', 'value'=>$lastName));
		if ($email != null && $email != '')
			$form.= $this->addHiddenField(array('name'=>'email', 'value'=>$email));

		if ($street != null && $street != '' && $city != null && $city != '')
		{
			$form.= $this->addHiddenField(array('name'=>'addressStreet1', 'value'=>$street));
			$form.= $this->addHiddenField(array('name'=>'addressCity', 'value'=>$city));
			$form.= $this->addHiddenField(array('name'=>'addressZip', 'value'=>$postcode));
			$form.= $this->addHiddenField(array('name'=>'addressState', 'value'=>$region));
			$form.= $this->addHiddenField(array('name'=>'addressCountry', 'value'=>$country));
		}
		
		if ($telephone != null && $telephone != '')
		{
			$form.= $this->addHiddenField(array('name'=>'phoneNumber', 'value'=>$telephone));
		}
		$form.= $this->addHiddenField(array('name'=>'returnUrl','value'=>$returnUrl));
		$form.= $this->addHiddenField(array('name'=>'notifyUrl','value'=>$notifyUrl));
		$form.= $this->addHiddenField(array('name'=>'reqtime', 'value'=> (time()*1000)));
		
		$form.= '</form>';
		
		$html = '<html><body>';
		$html.= $form;
		$html.= '<script type="text/javascript">document.getElementById("CPForm").submit();</script>';
		$html.= '</body></html>';

		//$this->_logger->addError("Citru Generated HTML ".$html);
		
		//$this->_logger->addError("Generated Citrus checkout for order $txnid");
		
		return $html;
    }

    public function getOrderPlaceRedirectUrl($storeId = null)
    {
        return $this->_getUrl('citrus/start', $storeId);
    }

	protected function addHiddenField($arr)
	{
		$nm = $arr['name'];
		$vl = $arr['value'];	
		$input = "<input name='".$nm."' type='hidden' value='".$vl."' />";	
		
		return $input;
	}
	
    /**
     * Get return URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
	 //AA may not be required
    public function getSuccessUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/success', $storeId);
    }

	/**
     * Get notify (IPN) URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
	 //AA Done
    public function getReturnUrl($storeId = null)
    {
        return $this->_getUrl('citrus/ipn/callback', $storeId, false);
    }
	
    /**
     * Get notify (IPN) URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
	 //AA Done
    public function getNotifyUrl($storeId = null)
    {
        return $this->_getUrl('citrus/ipn/callback', $storeId, false);
    }

    /**
     * Get cancel URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
	 //AA Not required
    public function getCancelUrl($storeId = null)
    {
        return $this->_getUrl('checkout/onepage/failure', $storeId);
    }

	/**
     * Get cancel URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
	 //AA Done
    public function getEnquirylUrl($txnid, $storeId = null)
    {
        return $this->_getUrl('citrus/checkout/enquiry', $storeId).'/txnid/'.$txnid;
    }
	
    /**
     * Build URL for store.
     *
     * @param string    $path
     * @param int       $storeId
     * @param bool|null $secure
     *
     * @return string
     */
	 //AA Done
    protected function _getUrl($path, $storeId, $secure = null)
    {
        $store = $this->_storeManager->getStore($storeId);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
}
