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
namespace Apptha\Marketplace\Controller\Adminhtml\Sellers;

use Apptha\Marketplace\Controller\Adminhtml\Sellers;

class MassDelete extends Sellers {
    /**
     *
     * @return void
     */
    public function execute() {
        $sellerIds = $this->getRequest ()->getParam ( 'approve' );
        foreach ( $sellerIds as $sellerId ) {
            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
                $customerGroupSession = $objectManager->get ( 'Magento\Customer\Model\Group' );
                $customerGroupData = $customerGroupSession->load ( 'General', 'customer_group_code' );
                $customerGroupId = $customerGroupData->getId ();
                $sellerFactory = $this->_objectManager->create ( '\Apptha\Marketplace\Model\Seller' );
                $sellerDetails = $sellerFactory->load ( $sellerId );
                $customerId = $sellerDetails->getCustomerId ();
                $customerFactory = $this->_objectManager->create ( '\Magento\Customer\Model\Customer' );
                $customerFactory->load ( $customerId )->setGroupId ( $customerGroupId )->save ();
                $sellerFactory->load ( $sellerId )->delete ();
            } catch ( Exception $e ) {
                $this->messageManager->addError ( $e->getMessage () );
            }
        }
        if (count ( $sellerIds )) {
            $this->messageManager->addSuccess ( __ ( 'A total of %1 record(s) were deleted.', count ( $sellerIds ) ) );
        }
        $this->_redirect ( '*/*/index' );
    }
}
