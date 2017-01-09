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
 * @copyright   Copyright (c) 2016 Apptha. (http://www.apptha.com)
 * @license     http://www.apptha.com/LICENSE.txt
 *
 */
namespace Apptha\Sociallogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Apptha\Marketplace\Helper\Data;

/**
 * This class contains seller approval/disapproval functions
 */
class Captcha implements ObserverInterface {
    /**
     *
     * @var $marketplaceData
     */
    protected $marketplaceData;
    protected $_helper;
    
    /**
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;
    
    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    
    /**
     *
     * @var CaptchaStringResolver
     */
    protected $captchaStringResolver;
    protected $redirect;
    protected $resultJsonFactory;
    /**
     * Customer data
     *
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    
    /**
     * Constructor
     * 
     * @param Data $marketplaceData            
     */
    public function __construct(Data $marketplaceData, \Magento\Captcha\Helper\Data $helper, \Magento\Framework\App\ActionFlag $actionFlag, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\Session\SessionManagerInterface $session, CaptchaStringResolver $captchaStringResolver, \Magento\Customer\Model\Url $customerUrl, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\App\Response\RedirectInterface $redirect) {
        $this->marketplaceData = $marketplaceData;
        $this->_helper = $helper;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->_session = $session;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->_customerUrl = $customerUrl;
        $this->redirect = $redirect;
        $this->resultJsonFactory = $resultJsonFactory;
    }
    /**
     * Execute the result
     * 
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $formId = 'social_login';
        $captcha = $this->_helper->getCaptcha ( $formId );
        if ($captcha->isRequired ()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction ();
            
            if (! $captcha->isCorrect ( $this->captchaStringResolver->resolve ( $controller->getRequest (), $formId ) )) {
                $message = __ ( 'Incorrect CAPTCHA.' );
                $this->_actionFlag->set ( '', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true );
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
                echo $message;
                $objectManager->get ( '\Magento\Customer\Model\Session' )->setUsername ( $message );
                return $objectManager->get ( '\Magento\Framework\Controller\Result\JsonFactory' )->create ()->setData ( $message );
            }
        }
    }
}