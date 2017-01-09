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
 * This class contains twitter login action
 */
class Twitterlogin extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
    protected $accountRedirect;
    public $storeManager;
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
     * Default customer account page
     *
     * @return void
     */
    public function execute() {
        require 'src/twitteroauth.php';
        require 'src/twconfig.php';
        $om = \Magento\Framework\App\ObjectManager::getInstance ();
        /** @var \Magento\Customer\Model\Session $session */
        $session = $om->get('\Magento\Customer\Model\Session');
        /**
         * Retrives @Twitter consumer key and secret key from core session
         */
        $tw_oauth_token = $session->getTwToken ();
        $tw_oauth_token_secret = $session->getTwSecret ();
        
        $twitteroauth = new \TwitterOAuth ( YOUR_CONSUMER_KEY, YOUR_CONSUMER_SECRET, $tw_oauth_token, $tw_oauth_token_secret );
        /**
         * Get Accesss token from @Twitter oAuth
         */
        $oauth_verifier = $this->getRequest()->getParam('oauth_verifier');
        $access_token = $twitteroauth->getAccessToken($oauth_verifier);
        /**
         * Get @Twitter User Details from twitter account
         *
         * @return string Redirect URL or Customer save action
         */
        $user_info = $twitteroauth->get ( 'account/verify_credentials' );
        /**
         * Retrieve the user details into twitter profile info.
         *
         * @var $user_info array
         */
        $firstname = $user_info->name;
        $email = $this->_objectManager->create('\Magento\Customer\Model\Session')->getTwemail ();
        $isSeller = $session->getIsseller ();
        
        $lastname = $user_info->screen_name;
        /**
         * Retrieve the user details into twitter profile info.
         *
         * @var $user_info array If @user_info contains error means throws the error message.
         */
        if (isset ( $user_info->error ) || $email == '' || $firstname == '') {
            $session->addError ( __ ( 'Twitter Login connection failed' ) );
            $url = $this->_objectManager->create ( '\Magento\Customer\Model\Url' )->getAccountUrl ();
            $this->accountRedirect->getRedirect ();
        } else {
            /**
             * If the email and firstname is not retreived print the error.
             */
            $this->createUser ( $email, $firstname, $lastname, $isSeller );
        }
    }
    public function createUser($email, $fname, $lname, $isSeller) {
        $customerInfo = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
        /**
         * Setting email id which is retreived from form.
         */
        $standardInfo ['email'] = $email;
        /**
         * Retrieving the customer form posted values.
         *
         * @param array $standardInfo
         *            array values such as @first_name,@last_name and @email
         */
        $standardInfo ['first_name'] = $fname;
        $standardInfo ['last_name'] = $lname;
        $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite ()->getWebsiteId ();
        $customerInfo->setWebsiteId ( $websiteId );
        $customerInfo->loadByEmail ( $standardInfo ['email'] );
        /**
         * Check if Already registered customer.
         */
        if ($customerInfo->getId ()) {
            /**
             * Initiates the customer account session and logged into the site
             */
            $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setCustomerAsLoggedIn ( $customerInfo );
            /**
             * Get customer current URL from customer session.
             *
             * @return string $link
             */
            $this->redirection ( $isSeller );
            return;
        }
        
        $password = mt_rand ( 100000, 999999 );
        $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite ()->getWebsiteId ();
        $customerInfo = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
        $customerInfo->setWebsiteId ( $websiteId );
        $customerInfo->setEmail ( $email );
        $customerInfo->setFirstname ( $fname );
        $customerInfo->setLastname ( $lname );
        $customerInfo->setPassword ( $password );
        $customerInfo->save ();
        $customerInfo->sendNewAccountEmail ();
        $storeName = $this->_storeManager->getStore ()->getName ();
        $this->messageManager->addSuccess ( __ ( 'Thank you for registering') . '. ' . __ ( 'You will receive welcome email with registration info in a moment.' ) );
        /**
         * Send Account Notification success mail
         */
        /**
         * Set the cutomer login session
         */
        $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setCustomerAsLoggedIn ( $customerInfo );
        if ($isSeller == 1) {
            $this->_objectManager->create ( '\Apptha\Sociallogin\Controller\Index\Googlepost' )->saveSeller ( $isSeller );
        }
        /**
         * Get customer current URL from customer session.
         *
         * @return string $link
         */
        $this->redirection ( $isSeller );
        return;
    }
    
    /**
     * Function to redirect
     *
     * @return url
     */
    public function redirection($isSeller) {
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
                $url = $this->_storeManager->getStore ()->getBaseUrl () . 'marketplace/seller/dashboard';
            } else {
                $url = $this->_storeManager->getStore ()->getBaseUrl () . 'customer/account/';
            }
        } else {
            
            $url = $link;
        }
        
        /**
         * Redirect to the referer page.
         */
        $redirect = $this->_storeManager->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
        $this->messageManager->addSuccess ( __ ( 'Your account has been successfully connected through Twitter' ) );
        $this->_redirect ( $url );
    }
}

	