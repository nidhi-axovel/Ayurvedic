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
namespace Apptha\Marketplace\Helper;

/**
 * This class contains system Configuration functions
 */
class System extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * Product approval config path
     */
    const XML_PATH_PRODUCT_APPROVAL = 'marketplace/product/product_approval';
    /**
     * Product types config path
     */
    const XML_PATH_PRODUCT_TYPES = 'marketplace/product/type';
    
    /**
     * Product custom options config path
     */
    const XML_PATH_PRODUCT_CUSTOM_OPTIONS = 'marketplace/product/custom_options';
    /**
     * Global commission config path
     */
    const XML_PATH_GLOBAL_COMMISSION = 'marketplace/seller/seller_commission';
    /**
     * Delete Products by seller
     */
    const XML_PATH_DELETE_PRODUCTS = 'marketplace/product/delete_option';
    /**
     * Seller order management config path
     */
    const XML_PATH_ORDER_MANAGEMENT = 'marketplace/order/seller_order';
    /**
     * Bulk upload config path
     */
    const XML_PATH_BULK_PRODUCT = 'marketplace/product/bulk_upload';
    
    /**
     * Custom attributes option config path
     */
    const XML_PATH_CUSTOM_ATTRIBUTES = 'marketplace/product/custom_attributes';
    
    
    const XML_PATH_LICENSE_KEY = 'marketplace/general/license_key';
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig            
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        /**
         * Setting a scope config
         */
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Function to Get product approval or not
     * 
     * @return boolean
     */
    public function getProductApproval() {
        /**
         * Getting for product approval status
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_PRODUCT_APPROVAL, $storeScope );
    }
    
    /**
     * Function to Get product approval or not
     * 
     * @return boolean
     */
    public function geCustomAttributes() {
        /**
         * Checking for custom attributes enabled or not
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_CUSTOM_ATTRIBUTES, $storeScope );
    }
    
    
    /**
     * Function to Get License Key
     *
     * @return boolean
     */
    public function getLicenseKey() {
        /**
         * Checking for custom attributes enabled or not
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_LICENSE_KEY, $storeScope );
    }
    /**
     * Get product types
     * 
     * @return array
     */
    public function getProductTypes() {
        /**
         * Getting for product types array
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_PRODUCT_TYPES, $storeScope );
    }
    
    /**
     * Function to enable product custom options or not
     * 
     * @return boolean
     */
    public function getProductCustomOptions() {
        /**
         * Getting for product custom options enabled or not
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_PRODUCT_CUSTOM_OPTIONS, $storeScope );
    }
    /**
     * Function to enable product delete for seller or not
     * 
     * @return boolean
     */
    public function getDeleteProductApproval() {
        /**
         * Getting product product delete option for seller
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_DELETE_PRODUCTS, $storeScope );
    }
    
    /**
     * Function to enable bulk product upload for sellers or not
     * 
     * @return boolean
     */
    public function getBulkProductApproval() {
        /**
         * checking for bulk product option enabled or not
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_BULK_PRODUCT, $storeScope );
    }
    
    /**
     * Get global commission for product
     * 
     * @return number
     */
    public function getGlobalCommission() {
        /**
         * To getting global commission value
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_GLOBAL_COMMISSION, $storeScope );
    }
    
    /**
     * Get order management for seller
     * 
     * @return boolean
     */
    public function getSellerOrderManagement() {
        /**
         * Getting order management enabled or not
         */
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue ( static::XML_PATH_ORDER_MANAGEMENT, $storeScope );
    }
    
    /**
     * License key
     *
     * Return license key given or not
     *
     * @return int
     */
    public function checkMarketplaceKey() {
        $succesFlag=1;
        $apikey = $this->getLicenseKey();
        $marketplaceApiKey = $this->marketplaceApiKey ();
        if ($apikey != $marketplaceApiKey) {
            $succesFlag=0;
            $keyerror = base64_decode ( 'PGgzIHN0eWxlPSJmbG9hdDpsZWZ0O2NvbG9yOnJlZDttYXJnaW46IDMwMHB4IDQxMHB4OyB3aWR0aDoyNiU7IHRleHQtZGVjb3JhdGlvbjp1bmRlcmxpbmU7IiBpZD0idGl0bGUtdGV4dCI+PGEgdGFyZ2V0PSJfYmxhbmsiIGhyZWY9Imh0dHA6Ly93d3cuYXBwdGhhLmNvbS9jaGVja291dC9jYXJ0L2FkZC9wcm9kdWN0LzE1NiIgc3R5bGU9ImNvbG9yOnJlZDsiPkludmFsaWQgTGljZW5zZSBLZXkgLSBCdXkgbm93PC9hPjwvaDM+' );
            return $succesFlag;
        }
        return $succesFlag;
    }
    /**
     * Function to get the license key
     *
     * Return generated license key
     *
     * @return string
     */
    
    public function marketplaceApiKey() {
        $code = $this->genenrateOscdomain ();
        return substr ( $code, 0, 25 ) . "CONTUS";
    }
    /**
     * Function to get the domain key
     *
     * Return domain key
     *
     * @return string
     */
    
    public function domainKey($tkey) {
        $message = "EM-MKTPM2MP0EFIL9XEV8YZAL7KCIUQ6NI5OREH4TSEB3TSRIF2SI1ROTAIDALG-JW";
        $stringLength = strlen ( $tkey );
        for($i = 0; $i < $stringLength; $i ++) {
            $keyArray [] = $tkey [$i];
        }
        $encMessage = "";
        $kPos = 0;
        $charsStr = "WJ-GLADIATOR1IS2FIRST3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0";
        $strLen = strlen ( $charsStr );
        for($i = 0; $i < $strLen; $i ++) {
            $charsArray [] = $charsStr [$i];
        }
        $lenMessage = strlen ( $message );
        $count = count ( $keyArray );
        for($i = 0; $i < $lenMessage; $i ++) {
            $char = substr ( $message, $i, 1 );
            $offset = $this->getOffset ( $keyArray [$kPos], $char );
            $encMessage .= $charsArray [$offset];
            $kPos ++;
    
            if ($kPos >= $count) {
                $kPos = 0;
            }
        }
        return $encMessage;
    }
    /**
     * Function to get the offset for license key
     *
     * Return offset key
     *
     * @return string
     */
    
    public function getOffset($start, $end) {
        $charsStr = "WJ-GLADIATOR1IS2FIRST3BEST4HERO5IN6QUICK7LAZY8VEX9LIFEMP0";
        $strLen = strlen ( $charsStr );
        /**
         * Increment for loop
         */
        for($i = 0; $i < $strLen; $i ++) {
            $charsArray [] = $charsStr [$i];
        }
        for($i = count ( $charsArray ) - 1; $i >= 0; $i --) {
            $lookupObj [ord ( $charsArray [$i] )] = $i;
        }
        $sNum = $lookupObj [ord ( $start )];
        $eNum = $lookupObj [ord ( $end )];
        $offset = $eNum - $sNum;
        if ($offset < 0) {
            $offset = count ( $charsArray ) + ($offset);
        }
        return $offset;
    }
    /**
     * Function to generate license key
     *
     * Return license key
     *
     * @return string
     */
    
    public function genenrateOscdomain() {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
        $controllerName = $requestInterface->getServer('HTTP_HOST');
        $subFolder = $matches = '';
        $strDomainName = $controllerName;
        preg_match ( "/^(http:\/\/)?([^\/]+)/i", $strDomainName, $subFolder );
        preg_match ( "/^(https:\/\/)?([^\/]+)/i", $strDomainName, $subFolder );
        preg_match ( "/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i", $subFolder [2], $matches );
        if (isset ( $matches ['domain'] )) {
            $clientUrl = $matches ['domain'];
        } else {
            $clientUrl = "";
        }
        $clientUrl = str_replace ( "www.", "", $clientUrl );
        $clientUrl = str_replace ( ".", "D", $clientUrl );
        $clientUrl = strtoupper ( $clientUrl );
        if (isset ( $matches ['domain'] )) {
            $response = $this->domainKey ( $clientUrl );
        } else {
            $response = "";
        }
        return $response;
    }
    
    
    /**
     * 
     * 
     * Get Message
     * 
     * @return string
     */
    public function getLicensekeyMessage(){
       return  base64_decode ( 'PGgzIHN0eWxlPSJjb2xvcjpyZWQ7IHRleHQtZGVjb3JhdGlvbjp1bmRlcmxpbmU7IiBpZD0idGl0bGUtdGV4dCI+PGEgdGFyZ2V0PSJfYmxhbmsiIGhyZWY9Imh0dHA6Ly93d3cuYXBwdGhhLmNvbS9jaGVja291dC9jYXJ0L2FkZC9wcm9kdWN0LzE4OCIgc3R5bGU9ImNvbG9yOnJlZDtmb250LXNpemU6MTZweDsiPkludmFsaWQgTGljZW5zZSBLZXkgLSBCdXkgbm93PC9hPjwvaDM+' );
    }
    
}