<?php

namespace Infortis\Base\Block\Adminhtml\Button\Import;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\LayoutFactory;

class Cms extends Field
{
    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;

    public function __construct(
        Context $context, 
        LayoutFactory $viewLayoutFactory,    
        array $data = []
    ) {
        $this->_viewLayoutFactory = $viewLayoutFactory;

        parent::__construct($context, $data);
    }

    /**
     * Import static blocks
     *
     * @param AbstractElement $element
     * @return String
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementOriginalData = $element->getOriginalData();     
        $name = false;

        if (isset($elementOriginalData['process']))
        {
            $name = $elementOriginalData['process'];
        }
        else
        {
            //grab name from ID element instead 
            $parts = explode('_', $elementOriginalData['id']);
            $name  = array_pop($parts);        
        }
        
        if (!$name)
        {
            return '<div>Action was not specified, and could not transform ID into name.</div>';
        }
        
        $buttonSuffix = '';
        if (isset($elementOriginalData['label']))
            $buttonSuffix = ' ' . $elementOriginalData['label'];

        $url = $this->getUrl('adminhtml/cmsimport/' . $name . '/package/Infortis_Base');
        
        $html = $this->_viewLayoutFactory->create()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setType('button')
            ->setClass('import-cms')
            ->setLabel('Import' . $buttonSuffix)
            ->setOnClick("setLocation('$url')")
            ->toHtml();
            
        return $html;
    }
}
