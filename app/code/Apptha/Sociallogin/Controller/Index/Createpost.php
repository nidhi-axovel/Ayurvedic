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
namespace Apptha\Sociallogin\Controller\Index;

use Magento\Framework\DataObject;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

/**
 * THis class contains customer signup functions
 */
class Createpost extends \Magento\Framework\App\Action\Action {
    /** @var AccountManagementInterface */
    protected $accountManagement;
    
    /** @var Address */
    protected $addressHelper;
    
    /** @var FormFactory */
    protected $formFactory;
    
    /** @var SubscriberFactory */
    protected $subscriberFactory;
    
    /** @var RegionInterfaceFactory */
    protected $regionDataFactory;
    
    /** @var AddressInterfaceFactory */
    protected $addressDataFactory;
    
    /** @var Registration */
    protected $registration;
    
    /** @var CustomerInterfaceFactory */
    protected $customerDataFactory;
    
    /** @var CustomerUrl */
    protected $customerUrl;
    
    /** @var Escaper */
    protected $escaper;
    
    /** @var CustomerExtractor */
    protected $customerExtractor;
    
    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;
    
    /** @var DataObjectHelper  */
    protected $dataObjectHelper;
    
    /**
     *
     * @var Session
     */
    protected $session;
    
    /**
     *
     * @var AccountRedirect
     */
    private $accountRedirect;
    protected $resultJsonFactory;
    /**
     *
     * @param Context $context            
     * @param Session $customerSession            
     * @param ScopeConfigInterface $scopeConfig            
     * @param StoreManagerInterface $storeManager            
     * @param AccountManagementInterface $accountManagement            
     * @param Address $addressHelper            
     * @param UrlFactory $urlFactory            
     * @param FormFactory $formFactory            
     * @param SubscriberFactory $subscriberFactory            
     * @param RegionInterfaceFactory $regionDataFactory            
     * @param AddressInterfaceFactory $addressDataFactory            
     * @param CustomerInterfaceFactory $customerDataFactory            
     * @param CustomerUrl $customerUrl            
     * @param Registration $registration            
     * @param Escaper $escaper            
     * @param CustomerExtractor $customerExtractor            
     * @param DataObjectHelper $dataObjectHelper            
     * @param AccountRedirect $accountRedirect
     *            @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Context $context, Session $customerSession, ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, AccountManagementInterface $accountManagement, Address $addressHelper, UrlFactory $urlFactory, FormFactory $formFactory, SubscriberFactory $subscriberFactory, RegionInterfaceFactory $regionDataFactory, AddressInterfaceFactory $addressDataFactory, CustomerInterfaceFactory $customerDataFactory, CustomerUrl $customerUrl, Registration $registration, Escaper $escaper, CustomerExtractor $customerExtractor, DataObjectHelper $dataObjectHelper, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, AccountRedirect $accountRedirect) {
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create ();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct ( $context );
    }
    
    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress() {
        if (! $this->getRequest ()->getPost ( 'create_address' )) {
            return null;
        }
        $addressForm = $this->formFactory->create ( 'customer_address', 'customer_register_address' );
        $allowedAttributes = $addressForm->getAllowedAttributes ();
        $addressData = [ ];
        $regionDataObject = $this->regionDataFactory->create ();
        foreach ( $allowedAttributes as $attribute ) {
            $attributeCode = $attribute->getAttributeCode ();
            $value = $this->getRequest ()->getParam ( $attributeCode );
            if ($value === null) {
                continue;
            }
            /**
             * Choose Attributes
             */
            switch ($attributeCode) {
                /**
                 * Case Region
                 */
                case 'region' :
                    $regionDataObject->setRegion ( $value );
                    break;
                /**
                 */
                case 'region_id' :
                    $regionDataObject->setRegionId ( $value );
                    break;
                default :
                    $addressData [$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create ();
        $this->dataObjectHelper->populateWithArray ( $addressDataObject, $addressData, '\Magento\Customer\Api\Data\AddressInterface' );
        $addressDataObject->setRegion ( $regionDataObject );
        $addressDataObject->setIsDefaultBilling ( $this->getRequest ()->getParam ( 'default_billing', false ) )->setIsDefaultShipping ( $this->getRequest ()->getParam ( 'default_shipping', false ) );
        return $addressDataObject;
    }
    
    /**
     * Create customer account action
     *
     * @return void @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create ();
        if ($this->session->isLoggedIn () || ! $this->registration->isAllowed ()) {
            $resultRedirect->setPath ( '*/*/' );
            return $resultRedirect;
        }
        if (! $this->getRequest ()->isPost ()) {
            $url = $this->urlModel->getUrl ( '*/*/create', [ 
                    '_secure' => true 
            ] );
            $resultRedirect->setUrl ( $this->_redirect->error ( $url ) );
            return $resultRedirect;
        }
        $this->session->regenerateId ();
        $result = new DataObject ();
        try {
            $isSeller = '';
            $address = $this->extractAddress ();
            $addresses = $address === null ? [ ] : [ 
                    $address 
            ];
            $customer = $this->customerExtractor->extract ( 'customer_account_create', $this->_request );
            $customer->setAddresses ( $addresses );
            $email = $this->getRequest ()->getParam ( 'email' );
            $password = $this->getRequest ()->getParam ( 'password' );
            $isSeller = $this->getRequest ()->getParam ( 'is-seller' );
            $confirmation = $this->getRequest ()->getParam ( 'password_confirmation' );
            
            
            $redirectUrl = $this->session->getBeforeAuthUrl ();
            $this->checkPasswordConfirmation ( $password, $confirmation );
            $customer = $this->accountManagement->createAccount ( $customer, $password, $redirectUrl );
            if ($this->getRequest ()->getParam ( 'is_subscribed', false )) {
                $this->subscriberFactory->create ()->subscribeCustomerById ( $customer->getId () );
            }
            $this->_eventManager->dispatch ( 'customer_register_success', [ 
                    'account_controller' => $this,
                    'customer' => $customer 
            ] );
            $confirmationStatus = $this->accountManagement->getConfirmationStatus ( $customer->getId () );
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $message = __ ( 'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.', $email );
                // @codingStandardsIgnoreEnd
            } else {
                $message = $this->urlModel->getUrl ( 'customer/account',['_secure' => true ]);
                $this->session->setCustomerDataAsLoggedIn ( $customer );
                if ($isSeller == 1) {
                    $this->_objectManager->create ( '\Apptha\Sociallogin\Controller\Index\Googlepost' )->saveSeller ( $isSeller );
                }
            }
            return $this->resultJsonFactory->create ()->setData($message);
        } catch ( StateException $e ) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __ ('There is already an account with this email address.');
            $result->setData ( 'message', $message );
            $result->setData ( 'email', $email );
            return $this->resultJsonFactory->create()->setData($result);
        } catch ( InputException $e ) {
            return $this->resultJsonFactory->create()->setData( $this->escaper->escapeHtml ( $e->getMessage () ) );
        } catch ( \Exception $e ) {
            return $this->resultJsonFactory->create()->setData( __ ( 'We can\'t save the customer.' ) );
        }
        $this->session->setCustomerFormData ( $this->getRequest()->getPostValue () );
        $defaultUrl = $this->urlModel->getUrl ( '*/*/create', [ 
                '_secure' => true 
        ] );
        $resultRedirect->setUrl ( $this->_redirect->error ( $defaultUrl ) );
        return $this->resultJsonFactory->create ()->setData ( $result );
    }
    
    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password            
     * @param string $confirmation            
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation) {
        if ($password != $confirmation) {
            throw new InputException ( __ ( 'Please make sure your passwords match.' ) );
        }
    }
    
    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage() {
        if ($this->addressHelper->isVatValidationEnabled ()) {
            if ($this->addressHelper->getTaxCalculationAddressType () == Address::TYPE_SHIPPING) {
                // @codingStandardsIgnoreStart
                $message = __ ( 'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your shipping address for proper VAT calculation.', $this->urlModel->getUrl ( 'customer/address/edit' ) );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = __ ( 'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your billing address for proper VAT calculation.', $this->urlModel->getUrl ( 'customer/address/edit' ) );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $message = __ ( 'Thank you for registering with %1.', $this->storeManager->getStore ()->getFrontendName () );
        }
        return $message;
    }
}
