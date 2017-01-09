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
 * @copyright   Copyright (c) 2015 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 * */
namespace Apptha\Sociallogin\Controller\Index;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;

/**
 * This class contains fb login actions
 */
class Fblogin extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
    protected $accountRedirect;
    public $_storeManager;
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context            
     * @param
     *            \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\Result\PageFactory $resultPageFactory, AccountRedirect $accountRedirect) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountRedirect = $accountRedirect;
        $this->_storeManager = $storeManager;
        parent::__construct ( $context );
    }
    /**
     * Fb login action
     *
     * @return void
     */
    public function execute() {
        $sellerUsingFacebook = '';
        $lname = $this->getRequest ()->getParam ( 'lname' );
        $email = $this->getRequest ()->getParam ( 'email' );
        $fname = $this->getRequest ()->getParam ( 'fname' );
        $sellerUsingFacebook = $this->getRequest ()->getParam ( 'fb' );
        $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite ()->getWebsiteId ();
        $customerFactory = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
        $customerFactory->setWebsiteId ( $websiteId );
        $customer = $customerFactory->loadByEmail ( $email );
        if (empty ( $customer->getId () )) {
            $customer = $this->_objectManager->create ( '\Apptha\Sociallogin\Controller\Index\Googlepost' )->createUser ( $email, $fname, $lname, $sellerUsingFacebook );
        }
        $session = $this->_objectManager->create ( '\Magento\Customer\Model\Session' );
        $session->loginById ( $customer->getId () );
        $session->regenerateId ();
        $resultRedirect = $this->resultRedirectFactory->create ();
        
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
            if ($sellerUsingFacebook == 1) {
                $url = $this->_storeManager->getStore ()->getBaseUrl () . 'marketplace/seller/dashboard';
            } else {
                $url = $this->_storeManager->getStore ()->getBaseUrl () . 'customer/account/';
            }
        } else {
            $url = $_SERVER ["HTTP_REFERER"];
        }
        
        $resultRedirect->setPath ( $url );
        return $resultRedirect;
    }
}

	