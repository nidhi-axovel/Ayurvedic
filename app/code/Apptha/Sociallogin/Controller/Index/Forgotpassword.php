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

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This class contains forgot password actions
 */
class Forgotpassword extends \Magento\Customer\Controller\AbstractAccount {
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;
    
    /** @var Escaper */
    protected $escaper;
    
    /**
     *
     * @var Session
     */
    protected $session;
    
    /**
     *
     * @param Context $context            
     * @param Session $customerSession            
     * @param AccountManagementInterface $customerAccountManagement            
     * @param Escaper $escaper            
     */
    public function __construct(Context $context, Session $customerSession, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, AccountManagementInterface $customerAccountManagement, Escaper $escaper) {
        $this->session = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        parent::__construct ( $context );
    }
    
    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create ();
        $email = ( string ) $this->getRequest ()->getParam ( 'email' );
        if ($email) {
            if (! \Zend_Validate::is ( $email, 'EmailAddress' )) {
                $this->session->setForgottenEmail ( $email );
                return $this->resultJsonFactory->create ()->setData ( __ ( 'Please Enter Valid email address.' ) );
            }
            
            try {
                $this->customerAccountManagement->initiatePasswordReset ( $email, AccountManagement::EMAIL_RESET );
            } catch ( NoSuchEntityException $e ) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch ( \Exception $exception ) {
                $message = __ ( 'We\'re unable to send the password reset email.' );
                $this->messageManager->addExceptionMessage ( $exception, __ ( 'We\'re unable to send the password reset email.' ) );
                return $this->resultJsonFactory->create ()->setData ( $message );
            }
            
            return $this->resultJsonFactory->create ()->setData ( $this->getSuccessMessage ( $email ) );
        } else {
            
            return $this->resultJsonFactory->create ()->setData ( __ ( 'Please enter your email.' ) );
        }
    }
    
    /**
     * Retrieve success message
     *
     * @param string $email            
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email) {
        return __ ( 'If there is an account associated with %1 you will receive an email with a link to reset your password.', $this->escaper->escapeHtml ( $email ) );
    }
}
