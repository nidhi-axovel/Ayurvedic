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
namespace Apptha\Marketplace\Controller\Product;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * This class contains product sku validation functions
 */
class Skuvalidate extends \Magento\Framework\App\Action\Action {
    /**
     *
     * @var $resultRawFactory
     * @var $storeManager
     */
    protected $resultRawFactory;
    protected $storeManager;
    /**
     * Constructor
     *
     * \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
     * \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Store\Model\StoreManagerInterface $storeManager) {
        parent::__construct ( $context );
        $this->resultRawFactory = $resultRawFactory;
        $this->storeManager = $storeManager;
    }
    /**
     * Function to validate product sku
     *
     * @return void
     */
    public function execute() {
        /**
         * Getting sku from query string
         */
        $sku = trim ( $this->getRequest ()->getParam ( 'sku' ) );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        /**
         * Getting product collection
         */
        $productData = $objectManager->create ( 'Magento\Catalog\Model\Product' )->getCollection ()->addAttributeToFilter ( 'sku', $sku );
        /**
         * Getting product count
         */
        $skuCount = count ( $productData );
        /**
         * To print product count
         */
ob_start();
        echo $skuCount;
    }
}