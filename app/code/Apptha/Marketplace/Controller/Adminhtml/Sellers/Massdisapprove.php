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

class Massdisapprove extends Sellers {
    /**
     *
     * @return void
     */
    public function execute() {
        $disApprovalIds = $this->getRequest ()->getParam ( 'approve' );
        foreach ( $disApprovalIds as $disApprovalId ) {
            try {
                /** @var $newsModel \Mageworld\SimpleNews\Model\News */
                $customerFactory = $this->_objectManager->create ( '\Apptha\Marketplace\Model\Seller' );
                $customerFactory->load ( $disApprovalId )->setStatus ( 0 )->save ();
                $sellerDetails = $customerFactory->load ( $disApprovalId );
                $customerId = $sellerDetails->getCustomerId ();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
                $customerObject = $objectManager->create ( 'Magento\Customer\Model\Customer' );
                $customerObject->load ( $customerId );
                $sellerName = $customerObject->getFirstname();
                $sellerEmail = $customerObject->getEmail ();
                $receiverData = [ 
                        'name' => $sellerName,
                        'email' => $sellerEmail 
                ];
                /**
                 * Assign values for your template variables
                 */
                $emailTempVariables = array ();
                $emailTempVariables ['cname'] = $sellerName;
                $adminData = $objectManager->get ( 'Apptha\Marketplace\Helper\Data' );
                $adminEmail = $adminData->getAdminEmail ();
                $adminName = $adminData->getAdminName ();
                /**
                 * Sender Detail
                 */
                $senderData = [ 
                        'name' => $adminName,
                        'email' => $adminEmail 
                ];
                $templateId = 'marketplace_seller_admin_disapproval_template';
                $this->_objectManager->get ( 'Apptha\Marketplace\Helper\Email' )->yourCustomMailSendMethod ( $emailTempVariables, $senderData, $receiverData, $templateId );
            } catch ( \Exception $e ) {
                $this->messageManager->addError ( $e->getMessage () );
            }
        }
        if (count ( $disApprovalIds )) {
            $this->messageManager->addSuccess ( __ ( 'A total of %1 record(s) were disapproved.', count ( $disApprovalIds ) ) );
        }
        $this->_redirect ( '*/*/index' );
    }
}
