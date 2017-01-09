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
namespace Apptha\Marketplace\Block\Seller;
use Magento\Framework\View\Element\Template;
use Apptha\Marketplace\Model\ResourceModel\Order\Collection;
/**
 * This class used to display the products collection
 */
class Dashboard extends \Magento\Framework\View\Element\Template {
    
    /**
     * Initilize variable for product factory
     *
     * @var \Apptha\Marketplace\Model\ResourceModel\Order\Collection
     */
    protected $commissionObject;
    /**
     *
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $loadCurrency;
    
    /**
     *
     * @param Template\Context $templateContext            
     * @param ProductFactory $productFactory            
     *
     * @param array $data            
     */
    public function __construct(Template\Context $templateContext, Collection $commissionObject, \Magento\Framework\Locale\CurrencyInterface $loadCurrency, array $data = []) {
        $this->commissionObject = $commissionObject;
        $this->loadCurrency = $loadCurrency;
        parent::__construct ( $templateContext, $data );
    }
    
    /**
     * Set product collection uisng ProductFactory object
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct ();
        $objectManagerDashboard = \Magento\Framework\App\ObjectManager::getInstance ();
        $customerObject = $objectManagerDashboard->get ( 'Magento\Customer\Model\Session' );
        $sellerId='';
        if ($customerObject->isLoggedIn ()) {
            $sellerId = $customerObject->getId ();
        }
        /**
         * Order collection filter by seller id
         */
        $sellerOrderCollection = $this->commissionObject->addFieldToSelect ( '*' );
        $sellerOrderCollection->addFieldToFilter ( 'seller_id', $sellerId );
         /**
         * Set order for manage order
         */
        $sellerOrderCollection->setOrder ( 'order_id', 'desc' );
        $this->setCollection ( $sellerOrderCollection );
    }
    
    /**
     * Prepare layout for seller order
     *
     * @return object $this
     */
    protected function _prepareLayout() {
        $this->pageConfig->getTitle ()->set ( __ ( "Recent Orders" ) );
        parent::_prepareLayout ();
        $pagerHtml = $this->getLayout ()->createBlock ( 'Magento\Theme\Block\Html\Pager', 'marketplace.order.manage.pager' );
        $pagerHtml->setLimit ( 5 )->setShowAmounts ( false )->setCollection ( $this->getCollection () );
        $this->setChild ( 'pager', $pagerHtml );
        $this->getCollection ()->load ();
        return $this;
    }
    
    /**
     * Get product name for seller order
     *
     * @param int $orderId            
     * @param int $sellerId            
     *
     * @return array
     */
    public function getProductDetails($orderId, $getSellerId) {
        /**
         * Getting seller product ids from order
         */
        $objectManagerDashboard = \Magento\Framework\App\ObjectManager::getInstance ();
        $orderItemObject = $objectManagerDashboard->get ( 'Apptha\Marketplace\Model\Orderitems' )->getCollection ();
        $orderItemObject->addFieldToSelect ( '*' );
        $orderItemObject->addFieldToFilter ( 'order_id', $orderId );
        $orderItemObject->addFieldToFilter ( 'seller_id', $getSellerId );
        $productIds = array_unique ( $orderItemObject->getColumnValues ( 'product_id' ) );
        
        /**
         * Get seller order items
         */
        $sellerOrderObject = $objectManagerDashboard->get ( 'Magento\Sales\Model\Order' )->load ( $orderId );
        $orderProducts = $sellerOrderObject->getAllItems ();
        
        /**
         * Prepare product names
         */
        $productNames = array ();
        foreach ( $orderProducts as $product ) {
            if (in_array ( $product->getProductId (), $productIds )) {
                $productNames [] = $product->getName ();
            }
        }
        
        /**
         * Return seller product names in particualr order
         */
        return implode ( ',', $productNames );
    }
    /**
     * Get customer name and created at from sales order
     *
     * @param int $orderId            
     * @param int $sellerId            
     * @param int $customerId            
     *
     * @return array
     */
    public function getOrderDetails($orderId, $sellerId, $customerId) {
        $objectManagerDashboard = \Magento\Framework\App\ObjectManager::getInstance ();
        $customer = $objectManagerDashboard->get ( 'Magento\Customer\Model\Customer' );
        $sellerDetails = $customer->load ( $customerId );
        $customerName = $sellerDetails->getFirstname ();
        $order = $objectManagerDashboard->get ( 'Magento\Sales\Model\Order' );
        $orderData = $order->load ( $orderId );
        $createdAt = $orderData->getCreatedAt ();
        return array (
                'customer_name' => $customerName,
                'created_at' => $createdAt 
        );
    }
    
    /**
     * Get pager for seller orders
     *
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml ( 'pager' );
    }
    
    /**
     * Get currency symbol by code
     *
     * @param string $currencyCode            
     *
     * @return string
     */
    public function getCurrencySymbol($currencyCode) {
        return $this->loadCurrency->getCurrency ( $currencyCode )->getSymbol ();
    }
}
