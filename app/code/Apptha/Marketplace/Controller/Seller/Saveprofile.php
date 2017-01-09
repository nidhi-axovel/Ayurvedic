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
namespace Apptha\Marketplace\Controller\Seller;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * This class contains save seller store functions
 */
class Saveprofile extends \Magento\Framework\App\Action\Action {
    /**
     *
     * @var $resultPage
     */
    protected $resultRawFactory;
    /**
     * Constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context            
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory            
     */
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory) {
        parent::__construct ( $context );
        $this->resultRawFactory = $resultRawFactory;
    }
    /**
     * Execute the result
     *
     * @var $resultPage
     */
    public function execute() {
        if (isset ( $_FILES ['store_logo'] ['name'] ) && $_FILES ['store_logo'] ['name'] != '') {
            $fileId = 'store_logo';
            $absolutePath = 'Marketplace/Sellerlogo';
            $logoName = $this->uploadStoreLogo ( $fileId, $absolutePath );
        }
        if (isset ( $_FILES ['store_banner'] ['name'] ) && $_FILES ['store_banner'] ['name'] != '') {
            $bannerUploader = $this->_objectManager->create ( 'Magento\MediaStorage\Model\File\Uploader', [ 
                    'fileId' => 'store_banner' 
            ] );
            $bannerUploader->setAllowedExtensions ( [ 
                    'jpg',
                    'jpeg',
                    'gif',
                    'png' 
            ] );
            $uploadBannerAdapter = $this->_objectManager->get ( 'Magento\Framework\Image\AdapterFactory' )->create ();
            $bannerUploader->addValidateCallback ( 'catalog_product_image', $uploadBannerAdapter, 'validateUploadFile' );
            $bannerUploader->setAllowRenameFiles ( true );
            $bannerUploader->setFilesDispersion ( true );
            $mediaDirectory = $this->_objectManager->get ( 'Magento\Framework\Filesystem' )->getDirectoryRead ( DirectoryList::MEDIA );
            $result = $bannerUploader->save ( $mediaDirectory->getAbsolutePath ( 'Marketplace/Sellerbanner' ) );
            unset ( $result ['tmp_name'] );
            unset ( $result ['path'] );
            $result ['url'] = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' )->getTmpMediaUrl ( $result ['file'] );
            $bannerName = $result ['file'];
            $image = $bannerName;
            $objectModelManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $absPath = $objectModelManager->get ( 'Magento\Store\Model\StoreManagerInterface' )->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'Marketplace/Sellerbanner' . $bannerName;
            $fileFactory = $objectModelManager->get ( 'Magento\Framework\Filesystem' );
            $bannerImageResized = $fileFactory->getDirectoryRead ( DirectoryList::MEDIA )->getAbsolutePath ( 'Marketplace/Sellerbanner/Resized' ) . $image;
            $imageFactory = $objectModelManager->get ( 'Magento\Framework\Image\AdapterFactory' );
            $bannerImageResize = $imageFactory->create ();
            $bannerImageResize->open ( $absPath );
            $bannerImageResize->constrainOnly ( TRUE );
            $bannerImageResize->keepTransparency ( false );
            $bannerImageResize->keepFrame ( FALSE );
            $bannerImageResize->keepAspectRatio ( false );
            $bannerImageResize->resize ( 1100, 230 );
            $dest = $bannerImageResized;
            $bannerImageResize->save ( $dest );
        }
        $removeLogo = $this->getRequest ()->getPost ( 'remove_logo' );
        if ($removeLogo == 1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $customerSession = $objectManager->get ( 'Magento\Customer\Model\Session' );
            if ($customerSession->isLoggedIn ()) {
                $customerId = $customerSession->getId ();
            }
            $sellerModel = $objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
            $sellerDetails = $sellerModel->load ( $customerId, 'customer_id' );
            $sellerDetails->setLogoName ( '' );
            $sellerDetails->save ();
        }
        $removeBanner = $this->getRequest ()->getPost ( 'remove_banner' );
        if ($removeBanner == 1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $customerSession = $objectManager->get ( 'Magento\Customer\Model\Session' );
            if ($customerSession->isLoggedIn ()) {
                $customerId = $customerSession->getId ();
            }
            $sellerModel = $objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
            $sellerDetails = $sellerModel->load ( $customerId, 'customer_id' );
            $sellerDetails->setBannerName ( '' );
            $sellerDetails->save ();
        }
        $objectGroupManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $customerSession = $objectGroupManager->get ( 'Magento\Customer\Model\Session' );
        if ($customerSession->isLoggedIn ()) {
            $this->_view->loadLayout ();
            $this->_view->renderLayout ();
            $storeName = $this->getRequest ()->getPost ( 'store_name' );
            $contactNumber = $this->getRequest ()->getPost ( 'contact' );
            $state = $this->getRequest ()->getPost ( 'state' );
            $state = trim ( $state );
            $country = $this->getRequest ()->getPost ( 'country' );
            $facebookId = $this->getRequest ()->getPost ( 'facebook_id' );
            $twitterId = $this->getRequest ()->getPost ( 'twitter_id' );
            $googleId = $this->getRequest ()->getPost ( 'google_id' );
            $linkedId = $this->getRequest ()->getPost ( 'linked_id' );
            $desc = $this->getRequest ()->getPost ( 'company_description' );
            $paypalId = $this->getRequest ()->getPost ( 'paypal_id' );
            $address = $this->getRequest ()->getPost ( 'address' );
            $showProfile = $this->getRequest ()->getPost ( 'show_profile' );
            if ($showProfile == '') {
                $showProfile = 0;
            }
            $nationalShippingAmount = $this->getRequest ()->getPost ( 'national_shipping_amount' );
            $internationalShippingAmount = $this->getRequest ()->getPost ( 'international_shipping_amount' );
            $metaKeywords = $this->getRequest ()->getPost ( 'meta_keyword' );
            $bankPayment = $this->getRequest ()->getPost ( 'bank_payment' );
            $metaDescription = $this->getRequest ()->getPost ( 'meta_description' );
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
            $customerSession = $objectManager->get ( 'Magento\Customer\Model\Session' );
            if ($customerSession->isLoggedIn ()) {
                $customerId = $customerSession->getId ();
            }
            $sellerModel = $objectManager->get ( 'Apptha\Marketplace\Model\Seller' );
            $sellerDetails = $sellerModel->load ( $customerId, 'customer_id' );
            $sellerDetails->setStoreName ( $storeName );
            $sellerDetails->setAddress ( $address );
            $sellerDetails->setState ( $state );
            $sellerDetails->setCountry ( $country );
            $sellerDetails->setContact ( $contactNumber );
            $sellerDetails->setFacebookId ( $facebookId );
            $sellerDetails->setTwitterId ( $twitterId );
            $sellerDetails->setGoogleId ( $googleId );
            $sellerDetails->setShowProfile ( $showProfile );
            $sellerDetails->setLinkedId ( $linkedId );
            $sellerDetails->setDesc ( $desc );
            $sellerDetails->setMetaKeywords ( $metaKeywords );
            $sellerDetails->setMetaDescription ( $metaDescription );
            if (isset ( $_FILES ['store_logo'] ['name'] ) && $_FILES ['store_logo'] ['name'] != '') {
                $sellerDetails->setLogoName ( $logoName );
            }
            if (isset ( $_FILES ['store_banner'] ['name'] ) && $_FILES ['store_banner'] ['name'] != '') {
                $sellerDetails->setBannerName ( $bannerName );
            }
            $sellerDetails->setBankPayment ( $bankPayment );
            $sellerDetails->setPaypalId ( $paypalId );
            if (! empty ( $nationalShippingAmount )) {
                $sellerDetails->setNationalShippingAmount ( $nationalShippingAmount );
            }
            if (! empty ( $internationalShippingAmount )) {
                $sellerDetails->setInternationalShippingAmount ( $internationalShippingAmount );
            }
            $trimStr = trim ( preg_replace ( '/[^a-z0-9-]+/', '-', strtolower ( $storeName ) ), '-' );
            $mainUrlRewrite = $objectManager->get ( 'Magento\UrlRewrite\Model\UrlRewrite' )->load ( $trimStr, 'request_path' );
            $getUrlRewriteId = $mainUrlRewrite->getUrlRewriteId ();
            $id = null;
            $om = \Magento\Framework\App\ObjectManager::getInstance ();
            $manager = $om->get ( 'Magento\Store\Model\StoreManagerInterface' );
            $store = $manager->getStore ( $id );
            $storeId = $store->getId ();
            if ($getUrlRewriteId) {
                $requestPath = $trimStr . '-' . $customerId;
            } else {
                $requestPath = $trimStr;
                $objectManager->get ( 'Magento\UrlRewrite\Model\UrlRewrite' )->setIsSystem ( 0 )->setIdPath ( 'seller/' . $customerId )->setTargetPath ( 'marketplace/seller/displayseller/id/' . $customerId )->setRequestPath ( $requestPath )->setStoreId ( $storeId )->save ();
            }
            $sellerDetails->setStoreUrl ( $requestPath );
            $sellerDetails->save ();
            $this->messageManager->addSuccess ( __ ( 'Store details has been updated Successfully' ) );
            $this->_redirect ( '*/*/profile' );
        } else {
            $this->messageManager->addError ( __ ( 'You must have a seller account to access' ) );
            $this->_redirect ( 'marketplace/seller/login' );
        }
    }
    
    /**
     * To upload store logo
     *
     * @param string $fileId            
     * @param string $absolutePath            
     *
     * @return string
     */
    public function uploadStoreLogo($fileId, $absolutePath) {
        $uploader = $this->_objectManager->create ( 'Magento\MediaStorage\Model\File\Uploader', [ 
                'fileId' => 'store_logo' 
        ] );
        $uploader->setAllowedExtensions ( [ 
                'jpg',
                'jpeg',
                'gif',
                'png' 
        ] );
        /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
        $imageAdapter = $this->_objectManager->get ( 'Magento\Framework\Image\AdapterFactory' )->create ();
        $uploader->addValidateCallback ( 'catalog_product_image', $imageAdapter, 'validateUploadFile' );
        $uploader->setAllowRenameFiles ( true );
        $uploader->setFilesDispersion ( true );
        /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
        $mediaDirectory = $this->_objectManager->get ( 'Magento\Framework\Filesystem' )->getDirectoryRead ( DirectoryList::MEDIA );
        $result = $uploader->save ( $mediaDirectory->getAbsolutePath ( 'Marketplace/Sellerlogo' ) );
        unset ( $result ['tmp_name'] );
        unset ( $result ['path'] );
        $result ['url'] = $this->_objectManager->get ( 'Magento\Catalog\Model\Product\Media\Config' )->getTmpMediaUrl ( $result ['file'] );
        $logoName = $result ['file'];
        $image = $logoName;
        $objectModelManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $absPath = $objectModelManager->get ( 'Magento\Store\Model\StoreManagerInterface' )->getStore ()->getBaseUrl ( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . 'Marketplace/Sellerlogo' . $logoName;
        $fileFactory = $objectModelManager->get ( 'Magento\Framework\Filesystem' );
        /**
         * To resize image
         */
        $imageResized = $fileFactory->getDirectoryRead ( DirectoryList::MEDIA )->getAbsolutePath ( 'Marketplace/Seller/Resized' ) . $image;
        $imageFactory = $objectModelManager->get ( 'Magento\Framework\Image\AdapterFactory' );
        $imageResize = $imageFactory->create ();
        $imageResize->open ( $absPath );
        $imageResize->constrainOnly ( TRUE );
        $imageResize->keepTransparency ( false );
        $imageResize->keepFrame ( FALSE );
        $imageResize->keepAspectRatio ( false );
        $imageResize->resize ( 150, 110 );
        $dest = $imageResized;
        $imageResize->save ( $dest );
        /**
         * Return store logo for save on seller table
         */
        return $logoName;
    }
}
