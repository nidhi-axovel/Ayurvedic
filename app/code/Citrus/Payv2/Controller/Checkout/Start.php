<?php
/**
 *
 * @copyright  Citruspay
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Citrus\Payv2\Controller\Checkout;

class Start extends \Magento\Framework\App\Action\Action
{
    /**
    * @var \Magento\Checkout\Model\Session
    */
    protected $_checkoutSession;

    /**
    * @var \Coinbase\Magento2PaymentGateway\Model\PaymentMethod
    */
    protected $_paymentMethod;

	protected $_resultPageFactory;
	
    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Checkout\Model\Session $checkoutSession
    * @param \Coinbase\Magento2PaymentGateway\Model\PaymentMethod $paymentMethod
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Citrus\Payv2\Model\PaymentMethod $paymentMethod,
	\Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_paymentMethod = $paymentMethod;
        $this->_checkoutSession = $checkoutSession;
		$this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
    * Start checkout by requesting checkout code and dispatching customer to Coinbase.
    */
    public function execute()
    {
		$html = $this->_paymentMethod->getPostHTML($this->getOrder());
        echo $html;
		//AA Not Required $this->getResponse()->setRedirect($this->_paymentMethod->getCheckoutUrl($this->getOrder()));
    }

    /**
    * Get order object.
    *
    * @return \Magento\Sales\Model\Order
    */
    protected function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}
