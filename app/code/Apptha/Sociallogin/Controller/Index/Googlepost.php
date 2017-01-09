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
 * This class contains Googlepost action
 */
class Googlepost extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
    protected $accountRedirect;
    protected $_storeManager;
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
        $storeManager = $this->_objectManager->get ( '\Magento\Store\Model\StoreManagerInterface' );
        $baseUrl = $storeManager->getStore ()->getBaseUrl ();
        $error =  $this->getRequest ()->getParam ( 'error' );
        if($error) {
           $resultRedirect = $this->resultRedirectFactory->create ();
            $resultRedirect->setPath ( $baseUrl );
            return $resultRedirect;
        }
        
        
        $isSellerUsingGoogle = $this->getRequest ()->getParam ( 'gplus' );
        $clientId = $this->_objectManager->get ( 'Apptha\Sociallogin\Helper\Data' )->getConfig ( 'sociallogin/googlelogin/clientid' );
        $clientSecret = $this->_objectManager->get ( 'Apptha\Sociallogin\Helper\Data' )->getConfig ( 'sociallogin/googlelogin/google_secret' );
        
        include_once ("src/Google_Client.php");
        include_once ("src/contrib/Google_Oauth2Service.php");
        $redirectUrl = $baseUrl . 'sociallogin/index/googlepost/';
        $gClient = new \Google_Client ();
        $gClient->setApplicationName ( 'login' );
        $gClient->setClientId ( $clientId );
        $gClient->setClientSecret ( $clientSecret );
        $gClient->setRedirectUri ( $redirectUrl );
        $gClient->setAccessType ( 'online' );
        $gClient->setScopes ( array (
                'https://www.googleapis.com/auth/userinfo.email' 
        ) );
        $google_oauthV2 = new \Google_Oauth2Service ( $gClient );
        $code = $this->getRequest ()->getParam ( 'code' );
        /**
         * Get Google Token
         */
        $token = $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->getGoogleToken ();
        $reset = $this->getRequest ()->getParam ( 'reset' );
        if ($reset) {
            unset ( $token );
            $gClient->revokeToken ();
            $this->_redirectUrl ( filter_var ( $redirectUrl, FILTER_SANITIZE_URL ) );
        }
        if (isset ( $code )) {
            $gClient->authenticate ();
            $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setGoogleToken ( $gClient->getAccessToken () );
            $resultRedirect = $this->resultRedirectFactory->create ();
            $resultRedirect->setPath ( filter_var ( $redirectUrl, FILTER_SANITIZE_URL ) );
            return $resultRedirect;
            return;
        }
        
        if (isset ( $token )) {
            $gClient->setAccessToken ( $token );
        }
        
        if ($gClient->getAccessToken ()) {
            /**
             * Retrieve user details If user succesfully in Google
             */
            $user = $google_oauthV2->userinfo->get();
            $user_id = $user ['id'];
            $user_name = filter_var ( $user ['name'], FILTER_SANITIZE_SPECIAL_CHARS );
            $email = filter_var ( $user ['email'], FILTER_SANITIZE_EMAIL );
            $profile_url = filter_var ( $user ['link'], FILTER_VALIDATE_URL );
            $token = $gClient->getAccessToken ();
            $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setGoogleToken ( $token );
        } else {
             /**
             * get google Authendication URL
             */
            $authUrl = $gClient->createAuthUrl ();
        }
         if (isset ( $authUrl )) {
            $resultRedirect = $this->resultRedirectFactory->create ();
            $resultRedirect->setPath ( $authUrl );
            return $resultRedirect;
        } else {
            /**
             * Fetching user infor from google array $user
             *
             * @var string $firstname, , general info for users from @google account.
             * @var string $familyname
             * @var string $email
             * @var string $id
             */
            $firstname = $user ['given_name'];
            $lastname = $user ['family_name'];
            $email = $user ['email'];
            $google_user_id = $user ['id'];
             /**
             * If @var $email is empty throws failure message.
             */
            if ($email == '') {
                $this->messageManager->addError ( $this->__ ( 'Google Login connection failed' ) );
                $url = $this->_objectManager->create ( '\Magento\Customer\Model\Url' )->getAccountUrl ();
                 return $this->_redirectUrl ( $url );
            } else {
                
                $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite()->getWebsiteId();
                $customerFactory = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
                $customerFactory->setWebsiteId ( $websiteId );
                $customer = $customerFactory->loadByEmail ( $email );
                if (empty ( $customer->getId () )) {
                    $customer = $this->createUser ( $email, $firstname, $firstname, $isSellerUsingGoogle );
                }
                if ($customer->getId ()) {
                    $customerSession = $this->_objectManager->create ( '\Magento\Customer\Model\Session' );
                    $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setCustomerAsLoggedIn ( $customer );
                }
                $resultRedirect = $this->resultRedirectFactory->create ();
                $redirectDasboard = $this->_objectManager->get ( 'Apptha\Sociallogin\Helper\Data' )->getConfig ( 'customer/startup/redirect_dashboard' );
                $redirectlink = $this->_storeManager->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
                $requestPath = trim ( $redirectlink, '/' );
                /**
                 * If customer session link is empty, set the request path URL
                 *
                 * @return string $requestpath
                 *        
                 */
                if ($redirectDasboard) {
                    if ($isSellerUsingGoogle == 1) {
                        $url = 'marketplace/seller/dashboard';
                    } else {
                        $url = 'customer/account/';
                    }
                } else {
                    $url = $this->_storeManager->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
                }
                $resultRedirect->setPath ( $url );
                return $resultRedirect;
            }
        }
    }
    
    /**
     * Function to create User
     *
     * @params email,firstname,last name
     * @return object
     */
    public function createUser($email, $fname, $lname, $sellerUsingFacebook) {
        $isSeller = $sellerUsingFacebook;
        $customerDetails = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
        $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite ()->getWebsiteId ();
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
        $customerDetails->setWebsiteId ( $websiteId );
        $customerDetails->loadByEmail ( $standardInfo ['email'] );
        /**
         * Check if Already registered customer.
         */
        $password = mt_rand ( 100000, 999999 );
        $websiteId = $this->_objectManager->create ( '\Magento\Store\Model\StoreManagerInterface' )->getWebsite ()->getWebsiteId ();
        $customerDetails = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
        $customerDetails->setWebsiteId ( $websiteId );
        $customerDetails->setEmail ( $email );
        $customerDetails->setFirstname ( $fname );
        $customerDetails->setLastname ( $lname );
        $customerDetails->setPassword ( $password );
        $customerDetails->save ();
        $customerDetails->sendNewAccountEmail ();
        $storeName = $this->_storeManager->getStore()->getName();
        $this->messageManager->addSuccess ( __ ( 'Thank you for registering' ) . '. ' . __ ( 'You will receive welcome email with registration info in a moment.' ) );
        $customerSession = $this->_objectManager->create ( '\Magento\Customer\Model\Session' );
        $this->_objectManager->create ( '\Magento\Customer\Model\Session' )->setCustomerAsLoggedIn ( $customerDetails );
        if ($isSeller == 1) {
            $this->saveSeller ( $isSeller );
        }
        return $customerDetails;
    }
    /**
     * Function to redirect dashboard or referrer url
     *
     * @return url
     */
    public function redirection() {
        /**
         * Redirect to the referer page.
         */
        $redirect = $this->_storeManager->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
        return $redirect;
    }
    public function saveSeller($isSeller) {
        if ($isSeller == 1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $customerSession = $this->_objectManager->get ( 'Magento\Customer\Model\Session' );
            if ($customerSession->isLoggedIn ()) {
                $customerId = $customerSession->getId ();
                $customerDetails = $customerSession->getCustomer ();
                $customerEmail = $customerDetails->getEmail ();
                $sellerApproval = $this->_objectManager->get ( 'Apptha\Marketplace\Helper\Data' )->getSellerApproval ();
                $customerGroupSession = $this->_objectManager->get ( 'Magento\Customer\Model\Group' );
                $customerGroupData = $customerGroupSession->load ( 'Marketplace Seller', 'customer_group_code' );
                $sellerGroupId = $customerGroupData->getId ();
                /**
                 * Checking seller approval or not
                 */
                if ($sellerApproval) {
                    $customerDetails->setGroupId ( $sellerGroupId )->save ();
                    $sellerModel = $this->_objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
                    $sellerModel->setEmail ( $customerEmail )->setStatus ( 0 )->setCustomerId ( $customerId )->save ();
                } else {
                    $customerDetails->setGroupId ( $sellerGroupId )->save ();
                    $sellerModel = $this->_objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
                    $sellerModel->setEmail ( $customerEmail )->setStatus ( 1 )->setCustomerId ( $customerId )->save ();
                }
            }
        }
    }
}
