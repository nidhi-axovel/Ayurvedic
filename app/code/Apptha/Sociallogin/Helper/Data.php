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
namespace Apptha\Sociallogin\Helper;

/**
 * This class contains data manipulation functions
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    
    public function getConfig($config_path) {
        return $this->scopeConfig->getValue ( $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }
    
    /**
     * Get Twitter authendication URL
     *
     * @return string Twitter authendication URL
     */
    public function getTwitterUrl() {
        require 'src/twitteroauth.php';
        require 'src/twconfig.php';
        $twitteroauth = new \TwitterOAuth ( YOUR_CONSUMER_KEY, YOUR_CONSUMER_SECRET );
        $om = \Magento\Framework\App\ObjectManager::getInstance ();
        /** @var \Magento\Customer\Model\Session $session */
        $session = $om->get ( '\Magento\Customer\Model\Session' );
        /**
         * Request to authendicate token, the @param string URL redirects the authorize page
         */
        $storeManager = $om->get ( '\Magento\Store\Model\StoreManagerInterface' );
        $baseUrl = $storeManager->getStore ()->getBaseUrl ();
        $request_token = $twitteroauth->getRequestToken ($baseUrl.'sociallogin/index/twitterlogin' );
        /**
         * Condition to check the response code is with 200K
         */
        if ($twitteroauth->http_code == 200) {
            $session->setTwToken ( $request_token ['oauth_token'] );
            $session->setTwSecret ( $request_token ['oauth_token_secret'] );
            return $twitteroauth->getAuthorizeURL ( $request_token ['oauth_token'] );
        }
    }
}