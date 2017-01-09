<?php

namespace Infortis\Infortis\Block\Adminhtml\System\Config\Form\Field\Color;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;

class Minicolors extends Field
{
    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

    public function __construct(Context $context, 
        array $data = [], 
        Registry $frameworkRegistry = null)
    {
        $this->_frameworkRegistry = $frameworkRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Add color picker
     *
     * @param AbstractElement $element
     * @return String
     */
    protected function _getElementHtml(AbstractElement $element)
    {
		$html = $element->getElementHtml(); //Default HTML

        if($this->_frameworkRegistry->registry('colorPickerFirstUse') == false)
		{
			$html .= '
			<!-- <script type="text/javascript" src="'. $this->getJsUrl('infortis/jquery/jquery-for-admin.min.js') .'"></script> -->
			<script type="text/javascript" src="'. $this->getJsUrl('infortis/jquery/plugins/minicolors/jquery.minicolors.min.js') .'"></script>
			<script type="text/javascript">jQuery.noConflict();</script>
            <link type="text/css" rel="stylesheet" href="'. $this->getJsUrl('infortis/jquery/plugins/minicolors/jquery.minicolors.css') .'" />
            ';
			
			$this->_frameworkRegistry->register('colorPickerFirstUse', 1);
        }
		
		$html .= '
			<script type="text/javascript">
				jQuery(function($){
					$("#'. $element->getHtmlId() .'").miniColors();
				});
			</script>
        ';
		
        return $html;
    }
}
