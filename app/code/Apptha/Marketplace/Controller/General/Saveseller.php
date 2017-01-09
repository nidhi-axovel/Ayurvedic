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
 * @package     Apptha_Marketplace
 * @version     1.1
 * @author      Apptha Team <developers@contus.in>
 * @copyright   Copyright (c) 2016 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
namespace Apptha\Marketplace\Controller\General;

/**
 * This class contains save seller data functions
 */
class Saveseller extends \Magento\Framework\App\Action\Action {
    
    /**
     * Execute the result
     *
     * @return $resultPage
     */
    public function execute() {
        $approvedConditions = $this->getRequest ()->getPost ( 'privacy_policy' );
        
        if ($approvedConditions == 1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            /**
             * Get customer object
             */
            $customerSession = $objectManager->get ( 'Magento\Customer\Model\Session' );
            if ($customerSession->isLoggedIn ()) {
                $customerId = $customerSession->getId ();
                $customerObject = $customerSession->getCustomer ();
                $customerEmail = $customerObject->getEmail ();
                $product = $this->_objectManager->get ( 'Apptha\Marketplace\Helper\Data' );
                $sellerApproval = $product->getSellerApproval ();
                $objectGroupManager = \Magento\Framework\App\ObjectManager::getInstance ();
                $customerGroupSession = $objectGroupManager->get ( 'Magento\Customer\Model\Group' );
                $customerGroupData = $customerGroupSession->load ( 'Marketplace Seller', 'customer_group_code' );
                $sellerGroupId = $customerGroupData->getId ();
                /**
                 * Checking customer approval or not
                 */
                if ($sellerApproval) {
                    $customerObject->setGroupId ( $sellerGroupId )->save ();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
                    $sellerModel = $objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
                    $sellerModel->setEmail ( $customerEmail )->setStatus ( 0 )->setCustomerId ( $customerId )->save ();
                } else {
                    
                    $customerObject->setGroupId ( $sellerGroupId )->save ();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
                    $sellerModel = $objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
                    $sellerModel->setEmail ( $customerEmail )->setStatus ( 1 )->setCustomerId ( $customerId )->save ();
                }
                $this->_redirect ( 'marketplace/general/changebuyer' );
            }
        }
        /**
         * Load page layout
         */
        $this->_view->loadLayout ();
        $this->_view->renderLayout ();
    }
}
