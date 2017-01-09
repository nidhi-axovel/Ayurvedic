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
namespace Apptha\Marketplace\Controller\Configurable;
/**
 * This class used to get configurable attribute options
 */
class Options extends \Magento\Framework\App\Action\Action {
    
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $managerInterface;
    public function __construct(\Magento\Framework\Message\ManagerInterface $managerInterface, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\App\Action\Context $context) {
        $this->managerInterface = $managerInterface;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct ( $context );
    }
    
    /**
     * To create configurable select attribute options block
     *
     * @return void
     */
    public function execute() {
        /**
         * Get Attribute ids
         */
        $attributeCodes = $this->getRequest ()->getParam ( 'attributes' );
        $productId = $this->getRequest ()->getParam ( 'product_id' );
        $attributeIds = $this->getRequest ()->getParam ( 'attribute_ids' );
        
        /**
         * Create instance for object manager
         */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance ();
        $configurableData = array ();
        
        if (! empty ( $productId )) {
            /**
             * Get Product data by product id
             */
            $product = $objectManager->get ( 'Magento\Catalog\Model\Product' )->load ( $productId );
            
            /**
             * Get configurable
             */
            $configurableData = $product->getTypeInstance ( true )->getConfigurableAttributesAsArray ( $product );
        }
        
        foreach ( $attributeCodes as $attributeCode => $label ) {
            
            $attributeId = '';
            if (isset ( $attributeIds [$attributeCode] )) {
                $attributeId = $attributeIds [$attributeCode];
            }
            
            /**
             * Getting selected product options values
             */
            $valueIndex = array ();
            if (! empty ( $attributeId ) && ! empty ( $configurableData [$attributeId] ['values'] )) {
                foreach ( $configurableData [$attributeId] ['values'] as $value ) {
                    $valueIndex [] = $value ['value_index'];
                }
            }
            
            /**
             * Getting product option values
             */
            $options = $objectManager->create ( 'Magento\Catalog\Model\Product\Attribute\Repository' )->get ( $attributeCode )->getOptions ();
            echo '<label class="label"><span>' . $label . '</span></label>';
            echo '<ul class="attribute-options-ul">';
            foreach ( $options as $option ) {
                $optionValue = $option->getValue ();
                
                /**
                 * Checking for product have option or not
                 */
                $checked = '';
                if (in_array ( $optionValue, $valueIndex )) {
                    $checked = 'checked';
                }
                
                if (! empty ( $optionValue )) {
                    echo '<li><input ' . $checked . ' id="option_' . $option->getValue () . '" name="options[' . $option->getValue () . ']" value="' . $attributeCode . '"
  title="' . $attributeCode . '"
  class="attribute-options-checkbox validate-one-required-by-name" type="checkbox">';
                    echo '<label for="option_' . $option->getValue () . '">' . $option->getLabel () . '</label></li>';
                }
            }
            echo '</ul>';
        }
    }
}
