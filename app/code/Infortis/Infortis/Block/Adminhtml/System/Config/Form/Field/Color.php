<?php

namespace Infortis\Infortis\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Color extends Field
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
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
        $html = $element->getElementHtml();     
        $html .= '
            <script type="text/javascript">
                require(["jquery", "mcolorpicker", "module"], function(jQuery, colorpicker, module) {
                    jQuery(function($){
                        $("#'. $element->getHtmlId() .'").attr("data-hex", true).width("81%").mColorPicker();
                    });                
                });         
            </script>
        ';
        
        return $html;
    }
}
