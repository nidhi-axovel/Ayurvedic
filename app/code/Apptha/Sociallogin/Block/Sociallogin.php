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
namespace Apptha\Sociallogin\Block;

class Sociallogin extends \Magento\Customer\Block\Form\Register {
    /**
     * Function to load Layout
     *
     * @return void
     */
    public function _prepareLayout() {
        return parent::_prepareLayout ();
    }
    /**
     * Returns action url for login form
     *
     * @return string
     */
    public function getLoginFormAction() {
        return $this->getUrl ( 'sociallogin/index/index', [ 
                '_secure' => true 
        ] );
    }
    /**
     * Returns action url for signup form
     *
     * @return string
     */
    public function getSignUpFormAction() {
        return $this->getUrl ( 'sociallogin/index/createpost', [ 
                '_secure' => true 
        ] );
    }
    /**
     * Returns action url for forgot password
     *
     * @return string
     */
    public function getForgotpasswordAction() {
        return $this->getUrl ( 'sociallogin/index/forgotpassword', [ 
                '_secure' => true 
        ] );
    }
}