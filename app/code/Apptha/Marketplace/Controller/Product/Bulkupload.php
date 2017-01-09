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
use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;

/**
 * This class contains seller bulk product upload functionality
 */
class Bulkupload extends \Magento\Framework\App\Action\Action {
    
    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     *
     * @var Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;
    
    /**
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;
    
    /**
     *
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatConfig;
    
    /**
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    /**
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory            
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository            
     * @param CustomOptionFactory $customOptionFactory            
     * @param \Magento\Catalog\Helper\Category $categoryHelper            
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState            
     * @param Action\Context $context            
     */
    public function __construct(\Magento\Catalog\Api\ProductRepositoryInterface $productRepository, CustomOptionFactory $customOptionFactory, \Magento\Catalog\Helper\Category $categoryHelper, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState, Action\Context $context) {
        $this->productRepository = $productRepository;
        $this->categoryHelper = $categoryHelper;
        $this->productFactory = $productFactory;
        $this->customOptionFactory = $customOptionFactory;
        $this->categoryFlatConfig = $categoryFlatState;
        parent::__construct ( $context );
    }
    /**
     * Function to upload csv and data
     */
    public function execute() {
        $customerId = '';
        $customerSession = $this->_objectManager->get ( 'Magento\Customer\Model\Session' );
        $customerId = $customerSession->getId ();
        $productData = array ();
        if ($_FILES ['bulk-product-upload-csv-file'] ['name'] == '') {
            $this->messageManager->addNotice ( __ ( 'Please Upload Csv File' ) );
            $this->_redirect ( 'marketplace/product/manage' );
        }
        
        if (isset ( $_FILES ['bulk-product-upload-csv-file'] ['name'] ) && $_FILES ['bulk-product-upload-csv-file'] ['name'] != '') {
            $uploader = $this->_objectManager->create ( 'Magento\MediaStorage\Model\File\Uploader', [ 
                    'fileId' => 'bulk-product-upload-csv-file' 
            ] );
            $uploader->setAllowedExtensions ( [ 
                    'csv' 
            ] );
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->_objectManager->get ( 'Magento\Framework\Image\AdapterFactory' )->create ();
            $uploader->setAllowRenameFiles ( true );
            $uploader->setFilesDispersion ( true );
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get ( 'Magento\Framework\Filesystem' )->getDirectoryRead ( DirectoryList::MEDIA );
            $config = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' );
            $result = $uploader->save ( $mediaDirectory->getAbsolutePath ( 'Marketplace/Sellerlogo' ) );
            unset ( $result ['tmp_name'] );
            unset ( $result ['path'] );
            $result ['url'] = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' )->getTmpMediaUrl ( $result ['file'] );
            $logoName = $result ['file'];
            $absPath = $this->_objectManager->get ( 'Magento\Store\Model\StoreManagerInterface' )->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'Marketplace/Sellerlogo' . $logoName;
            $data = array ();
            $file = fopen ( $absPath, "r" );
            while ( ! feof ( $file ) ) {
                $data [] = fgetcsv ( $file );
            }
            fclose ( $file );
            $line = $lines = '';
            $keys = array_shift ( $data );
            /**
             * Getting instance for catalog product collection
             */
            $createProductData = array ();
            foreach ( $data as $lines => $line ) {
                if (count ( $keys ) == count ( $line )) {
                    $data [$lines] = array_combine ( $keys, $line );
                }
            }
            if (count ( $data ) <= 1 && count ( $keys ) >= 1 && ! empty ( $line ) && count ( $keys ) == count ( $line )) {
                $data [$lines + 1] = array_combine ( $keys, $line );
            }
            $sellerModel = $this->_objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
            $status = $sellerModel->load ( $customerId, 'customer_id' )->getStatus ();
            if (! $customerSession->isLoggedIn () && $status != 0) {
                $this->_redirect ( 'marketplace/seller/dashboard' );
                return;
            }
            $resultData = $this->createProductDataValue($data,$createProductData,$productData);            
            $productData = $resultData['product_data'];
            $createProductData = $resultData['create_product_data'];       
            if (! empty ( $createProductData )) {
                $productData [] = $createProductData;
            }
            $productData = $productData [0];
        }
        $homeFolder = '';
        if (isset ( $_FILES ['bulk-product-upload-image-file'] ['name'] ) && $_FILES ['bulk-product-upload-image-file'] ['name'] != '') {
            $uploaderImageObject = $this->_objectManager->create ( 'Magento\MediaStorage\Model\File\Uploader', [ 
                    'fileId' => 'bulk-product-upload-image-file' 
            ] );
            $uploaderImageObject->setAllowedExtensions ( [ 
                    'zip' 
            ] );
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->_objectManager->get ( 'Magento\Framework\Image\AdapterFactory' )->create ();
            $uploaderImageObject->setAllowRenameFiles ( true );
            $uploaderImageObject->setFilesDispersion ( true );
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get ( 'Magento\Framework\Filesystem' )->getDirectoryRead ( DirectoryList::MEDIA );
            $config = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' );
            $result = $uploaderImageObject->save ( $mediaDirectory->getAbsolutePath ( 'Marketplace/zip' ) );
            unset ( $result ['tmp_name'] );
            unset ( $result ['path'] );
            $result ['url'] = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' )->getTmpMediaUrl ( $result ['file'] );
            $logoName = $result ['file'];
            $base = $mediaDirectory->getAbsolutePath () . 'Marketplace' . DIRECTORY_SEPARATOR . 'zip' . $logoName;
            $homeFolder = $mediaDirectory->getAbsolutePath ( 'tmp/catalog/product' );
            
            $zip = new \ZipArchive ();
            $result = $zip->open ( $base );
            if ($result === TRUE) {
                $zip->extractTo ( $homeFolder );
                $zip->close ();
            }
            $fileName = $_FILES ['bulk-product-upload-image-file'] ['name'];
            $folderName = basename ( $fileName, '.zip' );
            $files = scandir ( $homeFolder . DIRECTORY_SEPARATOR . $folderName . "/" );
            $source = $homeFolder . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;
            $newPath = $homeFolder . DIRECTORY_SEPARATOR . "seller-" . $customerId;
            if (is_dir ( $newPath )) {
                $destination = $newPath;
            } else {
                $destination = mkdir ( $newPath, 0777, true );
            }
            
            $this->unlinkFiles($files,$source,$homeFolder,$customerId);
        }
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $customerObj = $objectManager->get ( 'Magento\Customer\Model\Session' );
        $customerId = $customerObj->getId ();
        $isSellerSubscriptionEnabled = $objectManager->get ( 'Apptha\Marketplace\Helper\Data' )->isSellerSubscriptionEnabled ();
        if($isSellerSubscriptionEnabled == 1){    
        $date = $objectManager->get ( 'Magento\Framework\Stdlib\DateTime\DateTime' )->gmtDate ();
        $sellerSubscribedPlan = $objectManager->get ( 'Apptha\Marketplace\Model\Subscriptionprofiles' )->getCollection ();
        $sellerSubscribedPlan->addFieldToFilter ( 'seller_id', $customerId );
        $sellerSubscribedPlan->addFieldToFilter ( 'status', 1 );
        $sellerSubscribedPlan->addFieldtoFilter ( 'ended_at', array (array ('gteq' => $date), array ('ended_at','null' => '')) );
        if (count ( $sellerSubscribedPlan )) {
        $maximumCount = '';      
        foreach ( $sellerSubscribedPlan as $subscriptionProfile ) {
        $maximumCount = $subscriptionProfile->getMaxProductCount ();
        break;
        }
        $sellerProduct = $objectManager->get ( 'Magento\Catalog\Model\Product' )->getCollection ()
        ->addFieldToFilter ( 'seller_id', $customerId );
        $sellerIdForProducts = $sellerProduct->getAllIds ();
        $productDataTotalCount = 0;
        if(isset($productData ['sku'])){
        $productDataTotalCount = count($productData ['sku']);
        }
        $sellerProductcount = count ( $sellerIdForProducts ) + $productDataTotalCount;
        
        if ($maximumCount < $sellerProductcount && $maximumCount != 'unlimited') {
        $this->messageManager->addNotice ( __ ( 'You have reached your product limit. If you want to add more product(s) kindly upgrade your subscription plan.' ) );
        $this->_redirect ( 'marketplace/seller/subscriptionplans' );
        return;
        }    
        }else {
        $this->messageManager->addNotice ( __ ( 'You have not subscribed any plan yet. Kindly subscribe for adding product(s).' ) );
        $this->_redirect ( 'marketplace/seller/subscriptionplans' );
        return;
        }
        }
        $this->saveProductData ( $productData, $homeFolder );
    }
    
    /**
     * Un link files 
     * 
     * @param array $files
     * @param string $source
     * @param string $homeFolder
     * @param int $customerId
     * 
     * @return void
     */
    public function unlinkFiles($files,$source,$homeFolder,$customerId){
        /**
         * Cycle through all source files
         */
        $delete = array();
        foreach ( $files as $file ) {
            if (in_array ( $file, array (
                    ".",
                    ".."
            ) )) {
                continue;
            }
            if (preg_match ( '#\.(jpg|jpeg|gif|png)$#i', $file )) {
    
                if (copy ( $source . $file, $homeFolder . DIRECTORY_SEPARATOR . "seller-" . $customerId . "/" . $file )) {
                    $delete [] = $source . $file;
                }
                foreach ( $delete as $file ) {
                    unlink ( $file );
                }
            } else {
                continue;
            }
        }
    }
    
    /**
     * To create product data value
     * 
     * @param array $data
     * @param array $createProductData
     * @param array $productData
     * @return array
     */
    public function createProductDataValue($data,$createProductData,$productData){
        foreach ( $data as $value ) {
            if (isset ( $value ['sku'] )) {
                if (empty ( $value ['sku'] ) && $value ['sku'] != 0) {
                    if (! empty ( $createProductData )) {
                        $productData [] = $createProductData;
                    }
                    $createProductData = array ();
                    $createProductData = $value;
                } else {
                    $createProductData = array_merge_recursive ( $createProductData, $value );
                }
            }
        }
        return array('product_data' => $productData,'create_product_data' => $createProductData);
    }
    /**
     * Function to save product data
     *
     * @param array $productData            
     * @param array $imagePaths            
     *
     * @return void
     */
    public function saveProductData($productData, $imagePaths) {
        $categories = $this->getStoreCategories ( false, false, true );       
        $images = array ();
        /**
         * Getting All Categories
         */
        $categoryDetails = $this->getAllCategoriesData($categories);
        $uploadedProductCount = $existSkuCounts = 0;
        if (isset ( $productData ['sku'] )) {
            $importProductsCount = 0;
            foreach ( $productData ['sku'] as $key => $value ) {
                $categories = $productData ['categories'] [$key];
                $allowedProductTypes = array('simple','virtual','configurable');
                if (! empty ( $productData ['sku'] [$key] ) && in_array($productData ['product_type'] [$key],$allowedProductTypes)) {
                    $images = array ();
                    $sku = $productData ['sku'] [$key];
                    $skuCollection = $this->_objectManager->create ( 'Magento\Catalog\Model\Product' )->getCollection ()->addAttributeToFilter ( 'sku', $sku );
                    $productSkuForCheck = count ( $skuCollection );
                    if ($productSkuForCheck) {
                        $existSkuCounts = $existSkuCounts + 1;
                        continue;
                    }
                    $product = $this->productFactory->create ();
                    /**
                     * Multi row product data
                     */
                    $customerSession = $this->_objectManager->get ( 'Magento\Customer\Model\Session' );
                    $sellerId = $customerId = $customerSession->getId ();                         
                    $qty = $productData ['qty'] [$key];
                    $isInStock = $productData ['is_in_stock'] [$key];                                    
                    
                    $optionsArray = array ();
                    if (isset ( $productData ['custom_options'] [$key] )) {
                        $customOptions = $productData ['custom_options'] [$key];
                        $optionsArray = explode ( "|", $customOptions );
                    }
                    $attributeSetId = 4;
                    $product = $this->setProductInfoUpdate($attributeSetId,$product,$productData,$key,$sellerId,$categories,$categoryDetails);
                    /**
                     * For configurable product
                     */
                    $product = $this->setConfigurableAttributes ( $product, $productData, $key, $attributeSetId );
                    
                    /**
                     * Save product data
                     */
                    $product = $this->productRepository->save ( $product );                    
                    $productId = $product->getId ();
                    $productPrice = $this->_objectManager->create ( 'Magento\Catalog\Model\Product' )->load ( $productId );
                    $productPrice = $this->setProductDatas($productData,$key,$productPrice);                   
                    $productPrice->save ();
                    /**
                     * Checking for product type id
                     */
                    if ($productPrice->getTypeId () == 'configurable') {
                        $this->setAssociatedProductForConfigurable ( $productPrice->getId (), $productData, $key, $attributeSetId );
                    }
                    
                    /**
                     * For saving product Images
                     */
                    if (isset ( $_FILES ['bulk-product-upload-image-file'] ['name'] ) && $_FILES ['bulk-product-upload-image-file'] ['name'] != '') {
                        $mediaDirectory = $this->_objectManager->get ( 'Magento\Framework\Filesystem' )->getDirectoryRead ( DirectoryList::MEDIA );
                        $homeFolder = $mediaDirectory->getAbsolutePath ( 'tmp/catalog/product' );
                        
                        $files = scandir ( $homeFolder . DIRECTORY_SEPARATOR . "seller-" . $customerId . DIRECTORY_SEPARATOR );
                        $imagesPaths = array ();
                        $additionalImages = $productData ['additional_images'] [$key];
                        $addImages = explode ( ",", $additionalImages );
                        
                        $imagesPaths = $this->getImagePaths($files,$addImages,$customerId,$imagesPaths);
                        $baseImage = $productData ['base_image'] [$key];
                        $smallImage = $productData ['small_image'] [$key];
                        $thumbnailImage = $productData ['thumbnail_image'] [$key];
                        $productBaseImage = $this->_objectManager->create ( 'Magento\Catalog\Model\Product' )->load ( $productId );
                        
                        $productBaseImage = $this->setBaseImage($productBaseImage,$baseImage,$files,$customerId,$smallImage,$thumbnailImage);
                        /**
                         * Saving Price Details
                         */
                        $productBaseImage->setPrice ( $price );
                        $productBaseImage->setWeight ( $weight );
                        $productBaseImage->setMetaKeyword ( $metaKeywords );
                        $productBaseImage->setMetaDescription ( $metaDescription );
                        $product = $this->saveSpecialData($product,$specialPrice);
                        $product->setSpecialFromDate ( $specialFromDate );
                        $product->setSpecialToDate ( $specialToDate );
                        $productBaseImage->save ();
                        
                        $this->saveProductImage($imagesPaths,$productId);
                        
                    }
                    
                    if (count ( $optionsArray ) >= 1) {
                        $customOptionData ['options'] = $this->prepareCustomOptions ( $optionsArray );
                    }
                    
                    /**
                     * For saving stock details
                     */
                    $productStockData ['quantity_and_stock_status'] ['qty'] = $qty;
                    $productStockData ['quantity_and_stock_status'] ['is_in_stock'] = $isInStock;
                    
                    $this->_objectManager->get ( 'Apptha\Marketplace\Controller\Product\Savedata' )->updateStockDataForProduct ( $productId, $productStockData );
                    if (count ( $optionsArray ) >= 1) {
                        $this->_objectManager->get ( 'Apptha\Marketplace\Controller\Product\Savedata' )->saveCustomOption ( $productId, $customOptionData );
                    }
                    $uploadedProductCount = $uploadedProductCount + 1;
                    $importProductsCount = $importProductsCount + 1;
                }
            }
            if ($importProductsCount >= 1) {
                $this->messageManager->addSuccess ( __ ( 'Products were saved successfully' ) );
                $this->_redirect ( 'marketplace/product/manage' );
            }
            if ($existSkuCounts >= 1) {
                $this->messageManager->addNotice ( __ ( $existSkuCounts . ' ' . 'Sku(s) already exists' ) );
                $this->_redirect ( 'marketplace/product/manage' );
            }
        }
    }
    /**
     * To save special price
     * 
     * @param object $product
     * @param float $specialPrice
     * 
     * @return object $product
     */
    public function saveSpecialData($product,$specialPrice){
        if (! empty ( $specialPrice )) {
            $product->setSpecialPrice ( $specialPrice );
        }
        return $product;
    }
    
    /**
     * To save product image
     * 
     * @param array $imagesPaths
     * @param int $productId
     * 
     * @return void
     */
    public function saveProductImage($imagesPaths,$productId){
        $images = [ ];
        if (count ( $imagesPaths ) >= 1) {
            array_unique ( $imagesPaths );
            $productImage = $this->_objectManager->create ( 'Magento\Catalog\Model\Product' )->load ( $productId );
            $inc = 1;
            foreach ( $imagesPaths as $path ) {
                $length = 10;
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen ( $characters );
                $randomString = '';
                for($i = 0; $i < $length; $i ++) {
                    $randomString .= $characters [rand ( 0, $charactersLength - 1 )];
                }
    
                $randomStringArr = array ("position" => $inc,"media_type" => "image","video_provider" => "","file" => $path,"value_id" => "","label" => "",
                        "disabled" => 0,"removed" => "","video_url" => "","video_title" => "","video_description" => "","video_metadata" => "","role" => "");
                $images [$randomString] = $randomStringArr;
                $inc = $inc + 1;
            }
            $productImage->setData ( 'media_gallery', [
                    'images' => $images
            ] );
            $productImage->save ();
        }
    }
    
    /**
     * To st base image
     * 
     * @param object $productBaseImage
     * @param string $baseImage
     * @param array $files
     * @param int $customerId
     * @param string $smallImage
     * @param string $thumbnailImage
     * @return object $productBaseImage
     */
    public function setBaseImage($productBaseImage,$baseImage,$files,$customerId,$smallImage,$thumbnailImage){
        if (in_array ( $baseImage, $files ) && ! empty ( $baseImage )) {
            $productBaseImage->setImage ( "seller-" . $customerId . "/" . $baseImage );
        }
        if (in_array ( $smallImage, $files ) && ! empty ( $smallImage )) {
            $productBaseImage->setSmallImage ( "seller-" . $customerId . "/" . $smallImage );
        }
        if (in_array ( $thumbnailImage, $files ) && ! empty ( $thumbnailImage )) {
            $productBaseImage->setThumbnail ( "seller-" . $customerId . "/" . $thumbnailImage );
        }
        return $productBaseImage;
    }    
    /**
     * Prepare images paths
     * 
     * @param array $files
     * @param array $addImages
     * @param int $customerId
     * @param array $imagesPaths
     * 
     * @return array
     */
    public function getImagePaths($files,$addImages,$customerId,$imagesPaths){
        foreach ( $files as $file ) {
            if (in_array ( $file, array (
                    ".",
                    ".."
            ) )) {
                continue;
            }
            if (in_array ( $file, $addImages )) {
                $imagesPaths [] = "seller-" . $customerId . DIRECTORY_SEPARATOR . $file;
            }
        }
        return $imagesPaths;
    }
    
    /**
     * To set product info
     * 
     * @param int $attributeSetId
     * @param object $product
     * @param array $productData
     * @param int $key
     * @param int $sellerId
     * @param object $categories
     * @param array $categoryDetails
     * 
     * @return object $product
     * 
     */
    public function setProductInfoUpdate($attributeSetId,$product,$productData,$key,$sellerId,$categories,$categoryDetails){
    
        $categoryIds = array();
        $sku = $productData ['sku'] [$key];
        $name = $productData ['name'] [$key];
        $productTypeId = $productData ['product_type'] [$key];
        $price = $productData ['price'] [$key];
        $price = floatval ( $price );
    
    
        /**
         * Fetch category info for product
         */
        $categoryDataNames = array ();
        $categoryNames = explode ( ",", $categories );
        foreach ( $categoryNames as $categoryName ) {
            $categoryDataName = explode ( "/", $categoryName );
            $categoryDataNames [] = end ( $categoryDataName );
        }
        foreach ( $categoryDataNames as $catName ) {
            if (! strstr ( $catName, '/' )) {
                $categoryIds [] = array_search ( $catName, $categoryDetails );
            }
        }
    
    
    
        /**
         * Fetch product options
         */
        $product->setSku ( $sku );
        $product->setName ( $name );
        $product->setSellerId ( $sellerId );
        $product->setAttributeSetId ( $attributeSetId );
        $product->setTypeId ( $productTypeId );
        if ($product->getTypeId () != 'configurable') {
            $product->setPrice ( $price );
        }
        $product->setCategoryIds ( $categoryIds );
        $product->setUrlKey ( $name . $sellerId );
        $id = null;
        $manager = $this->_objectManager->get ( 'Magento\Store\Model\StoreManagerInterface' );
        $store = $manager->getStore ( $id );
        $websiteId = $store->getWebsiteId ();
        $product->setStoreId ( 0 );
        $product->setWebsiteIds ( array (
                $websiteId
        ) );
        $product->setStoreId ( 0 );
        $product->setWebsiteIds ( $websiteId );
        return $product;
    }
    
    /**
     * Get all categories data
     * 
     * @param object $categories
     * 
     * @return array $categoryDetails
     */
    public function getAllCategoriesData($categories){
        $categoryDetails = array ();
        foreach ( $categories as $category ) {
            $categoryDetails [2] = "Default Category";
            $categoryDetails [$category->getEntityId ()] = $category->getName ();
            if ($childrenCategories = $this->getChildCategories ( $category )) {
                foreach ( $childrenCategories as $childrenCategory ) {
                    if (! $childrenCategory->getIsActive ()) {
                        continue;
                    }
                    $childId = $childrenCategory->getId ();
                     
                    $categoryDetails [$childId] = $childrenCategory->getName ();
                }
            }
        }
        return $categoryDetails;
    }
    
    /**
     *  Set product data
     * 
     * @param object $productPrice
     * @param string $metaKeywords
     * @param string $metaDescription
     * @param float $weight
     * @param float $specialPrice
     * @param date $specialFromDate
     * @param date $specialToDate
     * 
     * @return object $productPrice
     */
    public function setProductDatas($productData,$key,$productPrice){
        $description = $specialPrice = $specialFromDate = $specialToDate = $metaKeywords = $metaDescription = '';
        $specialPrice = $productData ['special_price'] [$key];
        $specialFromDate = $productData ['special_price_from_date'] [$key];
        $specialToDate = $productData ['special_price_to_date'] [$key];
        $metaKeywords = $productData ['meta_keywords'] [$key];
        $metaDescription = $productData ['meta_description'] [$key];
        $weight = $productData ['weight'] [$key];
        $description = $productData ['description'] [$key];
        
        $productPrice->setDescription($description);
        $productPrice->setMetaKeyword ( $metaKeywords );
        $productPrice->setMetaDescription ( $metaDescription );
        $productPrice->setWeight ( $weight );
        if (! empty ( $specialPrice )) {
            $productPrice->setSpecialPrice ( $specialPrice );
        }
        $productPrice->setSpecialFromDate ( $specialFromDate );
        $productPrice->setSpecialToDate ( $specialToDate );
        return $productPrice;
    }
    
    /**
     * Function to get Parent Categories
     * 
     * @param string $sorted            
     * @param string $asCollection            
     * @param string $toLoad            
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true) {
        return $this->categoryHelper->getStoreCategories ( 'name', $asCollection, $toLoad );
    }
    /**
     * Function to get child Categories
     * 
     * @param string $sorted            
     * @param string $asCollection            
     * @param string $toLoad            
     */
    public function getChildCategories($category) {
        if ($this->categoryFlatConfig->isFlatEnabled () && $category->getUseFlatResource ()) {
            $subcategories = ( array ) $category->getChildrenNodes ();
        } else {
            $subcategories = $category->getChildren ();
        }
        return $subcategories;
    }
    
    /**
     * Prepare custom options
     *
     * @param array $optionsArray            
     *
     * @return array $customOptions
     */
    public function prepareCustomOptions($optionsArray) {
        $customOptions = array ();
        $customOptionsCount = 1;
        $previousName = $optionType = '';
        foreach ( $optionsArray as $optionsArr ) {
            $valueFlagCount = $valueFlag = 0;
            $rowArray = explode ( ',', $optionsArr );
            /**
             * Change input data to custom option array
             */
            foreach ( $rowArray as $value ) {
                $valueArr = explode ( '=', $value );
                if (isset ( $valueArr [0] ) && isset ( $valueArr [1] )) {
                    $customOptionReturn = $this->getCustomOptionValues($customOptions,$valueArr,$previousName,$customOptionsCount,$valueFlag,$valueFlagCount);
                    
                    $customOptions = $customOptionReturn['custom_options'];
                    $customOptionsCount = $customOptionReturn['custom_options_count'];
                    $valueArr = $customOptionReturn['value_arr'];
                    $previousName = $customOptionReturn['previous_name'];                   
                    $valueFlag = $customOptionReturn['value_flag'];
                    $valueFlagCount = $customOptionReturn['value_flag_count'];
                 
                    if ($valueArr [0] == 'name' && $valueFlag == 0) {
                        $customOptions [$customOptionsCount] ['title'] = $valueArr [1];
                        $previousName = $valueArr [1];
                    } elseif ($valueArr [0] == 'type' && $valueFlag == 0) {
                        $optionType = $valueArr [1];
                        $customOptions [$customOptionsCount] ['type'] = $valueArr [1];
                    } elseif ($valueArr [0] == 'required' && $valueFlag == 0) {
                        $customOptions [$customOptionsCount] ['is_require'] = $valueArr [1];
                    } else {
                    $customOptions = $this->setCustomOptionValue($customOptions,$valueArr,$optionType,$customOptionsCount,$valueFlagCount);
                    }
                }
            }
            $customOptionsCount = $customOptionsCount + 1;
        }
        return $customOptions;
    }
    
    /**
     * Set custom option value
     * 
     * @param array $customOptions
     * @param array $valueArr
     * @param string $optionType
     * @param int $customOptionsCount
     * @param int $valueFlagCount
     * 
     * @return $customOptions
     */
    public function setCustomOptionValue($customOptions,$valueArr,$optionType,$customOptionsCount,$valueFlagCount){
        /**
         * Set custom option value
         */
        if ($valueArr [0] != 'name' && $valueArr [0] != 'type' && $valueArr [0] != 'required') {
            $optionName = $valueArr [0];
            if ($optionType == 'drop_down' || $optionType == 'radio' || $optionType == 'checkbox' || $optionType == 'multiple') {
                $customOptions [$customOptionsCount] ['values'] [$valueFlagCount] ['option_type_id'] = - 1;
                $customOptions [$customOptionsCount] ['values'] [$valueFlagCount] ['is_delete'] = '';
                $customOptions [$customOptionsCount] ['values'] [$valueFlagCount] ['sort_order'] = '';
                if ($valueArr [0] == 'option_title') {
                    $customOptions [$customOptionsCount] ['values'] [$valueFlagCount] ['title'] = $valueArr [1];
                } else {
                    $customOptions [$customOptionsCount] ['values'] [$valueFlagCount] [$optionName] = $valueArr [1];
                }
            } else {
                $customOptions [$customOptionsCount] [$optionName] = $valueArr [1];
            }
        }
        return $customOptions;
    }
    
    /**
     * Get custom option values
     * 
     * @param array $customOptions
     * @param int $customOptionsCount
     * @param array $valueArr
     * @param string $previousName
     * @param int $customOptionsCount
     * @param bool $valueFlag
     * @param int $valueFlagCount
     * 
     * @return array
     */
    public function getCustomOptionValues($customOptions,$valueArr,$previousName,$customOptionsCount,$valueFlag,$valueFlagCount){
        if ($valueArr [0] == 'name') {
            if ($valueArr [1] == $previousName && $customOptionsCount != 0) {
                $customOptionsCount = $customOptionsCount - 1;
                $valueFlag = 1;
                $valueFlagCount = $valueFlagCount + 1;
            } else {
                $customOptions [$customOptionsCount] ['is_delete'] = '';
                $customOptions [$customOptionsCount] ['previous_type'] = '';
                $customOptions [$customOptionsCount] ['previous_group'] = '';
                $customOptions [$customOptionsCount] ['id'] = $customOptionsCount;
                $customOptions [$customOptionsCount] ['option_id'] = 0;
            }
        }
        return array('custom_options' => $customOptions,'custom_options_count' => $customOptionsCount,'value_arr' => $valueArr,'previous_name' => $previousName,'value_flag' => $valueFlag,'value_flag_count' => $valueFlagCount);
    }
    
    /**
     * Set configurable attributes to product
     *
     * @param object $product            
     * @param array $productData            
     * @param int $key            
     *
     * @return object $product
     */
    public function setConfigurableAttributes($product, $productData, $key, $attributeSetId) {
        /**
         * Declare data array for configurable attributes
         * Declare attribute label value pair
         */
        $data = $attributeLabelValuePair = array ();
        
        /**
         * Checking whether product data have additional attributes for not
         *
         * If not return current product object
         */
        if (isset ( $productData ['additional_attributes'] [$key] ) && $productData ['additional_attributes'] [$key] == '') {
            return $product;
        }
        
        /**
         * Create instance for object manager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        
        /**
         * Getting configurable product attributes
         */
        $attributes = $objectManager->get ( 'Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler' )->getApplicableAttributes ();
        /**
         * Filter configurable attributes by attribute set id
         */
        $attributes->addFieldToFilter ( 'entity_type_id', $attributeSetId );
        
        /**
         * Product Types
         */
        $types = [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ];
        
        /**
         * Declare attribute code array
         */
        $attributeCodeArray = array ();
        /**
         * Iterate attributes
         */
        foreach ( $attributes as $attribute ) {
            /**
             * Checking for configurable attributes
             */
            if (! $attribute->getApplyTo () || count ( array_diff ( $types, $attribute->getApplyTo () ) ) === 0) {
                $attributeCodeArray [] = $attribute->getAttributeCode ();
            }
        }
        
        if (isset ( $productData ['additional_attributes'] [$key] )) {
            $additionalAttributes = $productData ['additional_attributes'] [$key];
            $additionalAttributesArray = explode ( ",", $additionalAttributes );
            
            /**
             * Iterate additional attribute array
             */
            foreach ( $additionalAttributesArray as $additionalAttributesArr ) {
                /**
                 * Prepare attribute code and value array
                 */
                $splitAdditionalAttributesArr = explode ( "=", $additionalAttributesArr );
                /**
                 * Asign attribute code and value to variable
                 */
                $additionalAttributeCode = $splitAdditionalAttributesArr [0];
                $additionalAttributeValue = $splitAdditionalAttributesArr [1];
                if (in_array ( $additionalAttributeCode, $attributeCodeArray )) {
                    if (! array_key_exists ( $additionalAttributeCode, $attributeLabelValuePair )) {
                        $attributeLabelValuePair = $this->getAttributeOptionValues ( $additionalAttributeCode, $attributeLabelValuePair );
                    }
                    
                    /**
                     * Prepare attribute code - value pair array
                     */
                    if (isset ( $attributeLabelValuePair [$additionalAttributeCode] [$additionalAttributeValue] )) {
                        $data [$additionalAttributeCode] = $attributeLabelValuePair [$additionalAttributeCode] [$additionalAttributeValue];
                    }
                }
            }
        }
        
        /**
         * Add product data
         */
        if (count ( $data ) >= 1) {
            $product->addData ( $data );
        }
        
        /**
         * Return product object
         */
        return $product;
    }
    
    /**
     * Get attribute option values
     *
     * @param string $additionalAttributeCode            
     * @param array $attributeLabelValuePair            
     *
     * @return array $attributeLabelValuePair
     */
    public function getAttributeOptionValues($additionalAttributeCode, $attributeLabelValuePair) {
        /**
         * Create instance for object manager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        /**
         * Getting product option values
         */
        $options = $objectManager->create ( 'Magento\Catalog\Model\Product\Attribute\Repository' )->get ( $additionalAttributeCode )->getOptions ();
        /**
         * Iterte the attribute options
         */
        foreach ( $options as $option ) {
            /**
             * Save attribute label
             */
            $optionLabel = $option->getLabel ();
            /**
             * Assign attribute label value pair
             */
            $attributeLabelValuePair [$additionalAttributeCode] [$optionLabel] = $option->getValue ();
        }
        /**
         * Return attribute label value pair
         */
        return $attributeLabelValuePair;
    }
    
    /**
     * Set associated product for configurable product
     *
     * @param int $productId            
     * @param object $productData            
     * @param int $key            
     *
     * @return void
     */
    public function setAssociatedProductForConfigurable($productId, $productData, $key, $attributeSetId) {
        /**
         * Create instance for object manager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        /**
         * Checking for configurable variations exist or not
         */
        if (isset ( $productData ['configurable_variations'] [$key] )) {
            /**
             * Declare edit simple product flag
             */
            $editSimpleProductFlag = 1;
            /**
             * Declare action
             */
            $action = 'add';
            
            /**
             * Declre configurable attributes
             */
            $configurableAttributes = array ();
            
            if ($productData ['configurable_variations'] [$key] == '') {
                return 0;
            }
            $simpleProducts = explode ( "|", $productData ['configurable_variations'] [$key] );
            $simpleProdouctIds =  $objectManager->get ( 'Apptha\Marketplace\Block\Product\Configurable' )->getAssociatedProductIds ( $simpleProducts );
        }
        
        if (isset ( $productData ['configurable_variation_labels'] [$key] )) {
            $splitsAttributes = explode ( ",", $productData ['configurable_variation_labels'] [$key] );
            foreach ( $splitsAttributes as $splitsAttribute ) {
                $splitsAttributeArray = explode ( "=", $splitsAttribute );
                $configurableAttributes [] = $splitsAttributeArray [0];
            }
        }
        $simpleProductData ['selected_attributes'] = $configurableAttributes;
        $objectManager->get ( 'Apptha\Marketplace\Controller\Product\Savedata' )->associateSimpleProductsWithConfigurable ( $action, $productId, $simpleProductData, $simpleProdouctIds, $attributeSetId, $editSimpleProductFlag );
    }
}





