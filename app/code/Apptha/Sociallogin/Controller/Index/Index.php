<?php

/**
 * Apptha
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.apptha.com/LICENSE.txt
 *
 * ==============================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * ==============================================================
 * This package designed for Magento COMMUNITY edition
 * Apptha does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * Apptha does not provide extension support in case of
 * incorrect edition usage.
 * ==============================================================
 *
 * @category    Apptha
 * @package     Apptha_Sociallogin
 * @version     1.0
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2016 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
namespace Apptha\Sociallogin\Controller\Index;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlFactory;

class Index extends \Magento\Framework\App\Action\Action {
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;
    
    /** @var Validator */
    protected $formKeyValidator;
    /**
     *
     * @var AccountRedirect
     */
    protected $accountRedirect;
    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;
    /**
     *
     * @var Session
     */
    protected $session;
    protected $resultJsonFactory;
    public $storeManager;
    /**
     *
     * @param Context $context            
     * @param Session $customerSession            
     * @param AccountManagementInterface $customerAccountManagement            
     * @param CustomerUrl $customerHelperData            
     * @param Validator $formKeyValidator            
     * @param AccountRedirect $accountRedirect            
     */
    public function __construct(Context $context, Session $customerSession, AccountManagementInterface $customerAccountManagement, UrlFactory $urlFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, CustomerUrl $customerHelperData, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, Validator $formKeyValidator, AccountRedirect $accountRedirect) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->urlModel = $urlFactory->create ();
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountRedirect = $accountRedirect;
        $this->_storeManager = $storeManager;
        parent::__construct ( $context );
    }
    
    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute() {
        $isSeller = '';
        if ($this->getRequest ()->isPost ()) {
            $login = $this->getRequest ()->getPost ();
            $isSeller = $login ['isseller'];
            if (! empty ( $login ['email'] ) && ! empty ( $login ['password'] )) {
                try {
                    $customer = $this->customerAccountManagement->authenticate ( $login ['email'], $login ['password'] );
                    $this->session->setCustomerDataAsLoggedIn ( $customer );
                    $this->session->regenerateId ();
                } catch ( EmailNotConfirmedException $e ) {
                    $value = $this->customerUrl->getEmailConfirmationUrl ( $login ['email'] );
                    $message = __ ( 'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.', $value );
                    $this->session->setUsername ( $login ['email'] );
                    return $this->resultJsonFactory->create ()->setData ( $message );
                } catch ( AuthenticationException $e ) {
                    $message = __ ( 'Invalid login or password.' );
                    $this->session->setUsername ( $login ['email'] );
                    return $this->resultJsonFactory->create ()->setData ( $message );
                } catch ( \Exception $e ) {
                    $message = __ ( 'An unspecified error occurred. Please contact us for assistance.' );
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    return $this->resultJsonFactory->create ()->setData ( $message );
                }
            } else {
                $this->messageManager->addError ( __ ( 'A login and a password are required.' ) );
            }
        }
        $redirectToDasboard = $this->_objectManager->get ( 'Apptha\Sociallogin\Helper\Data' )->getConfig ( 'customer/startup/redirect_dashboard' );
        $link = $this->_storeManager->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
        $requestPath = trim ( $link, '/' );
        /**
         * If customer session link is empty, set the request path URL
         *
         * @return string $requestpath
         *        
         */
        if ($redirectToDasboard) {
            if ($isSeller == 1) {
                $url = $this->urlModel->getUrl ( 'marketplace/seller/dashboard', [ 
                        '_secure' => true 
                ] );
            } else {
                $url = $this->urlModel->getUrl ( 'customer/account', [ 
                        '_secure' => true 
                ] );
            }
        } else {
            $url = $_SERVER ["HTTP_REFERER"];
        }
        
        return $this->resultJsonFactory->create ()->setData ( $url );
    }
}
