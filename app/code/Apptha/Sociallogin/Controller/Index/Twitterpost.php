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

class Twitterpost extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
    protected $accountRedirect;
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context            
     * @param
     *            \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, AccountRedirect $accountRedirect) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountRedirect = $accountRedirect;
        parent::__construct ( $context );
    }
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute() {
        /**
         * Retrieve the customer posted email to authendicate @Twitter account
         *
         * @param
         *            string email_value
         */
        $isSeller = 0;
        $twitter_email = ( string ) $this->getRequest ()->getParam ( 'email' );
        $isSeller = ( string ) $this->getRequest ()->getParam ( 'twitter-seller' );
        /**
         * Set the $twitter_email into customer session
         */
        $om = \Magento\Framework\App\ObjectManager::getInstance ();
        /** @var \Magento\Customer\Model\Session $session */
        $session = $om->get ( '\Magento\Customer\Model\Session' );
        $session->setTwemail ( $twitter_email );
        $session->setIsseller ( $isSeller );
        /**
         * Send the response to the customer request for twitter action
         *
         * @return string $url
         */
        $url = $om->create ( '\Apptha\Sociallogin\Helper\Data' )->getTwitterUrl ();
        $url = ($url) ? $url : 'Twitter consumer key or secret key is invalid';
        $this->referalSession ();
        
        $resultRedirect = $this->resultRedirectFactory->create ();
        $resultRedirect->setPath ( $url );
        return $resultRedirect;
    }
    
    /**
     * Function to get AUTH URL
     * 
     * @return void
     */
    public function referalSession() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $redirect = $objectManager->get ( '\Magento\Store\Model\StoreManagerInterface' )->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_LINK );
        $this->_objectManager->create ( '\Magento\Customer\Model\Customer' )->setBeforeAuthUrl ( $redirect );
    }
}

	