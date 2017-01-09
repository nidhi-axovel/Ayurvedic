<?php

namespace Infortis\Base\Model\System\Config\Source\Product\AssociatedProducts;

class Template
{
    public function toOptionArray()
    {
        return [

            // Magento's default template file:
            ['value' => 'product/list/items.phtml',
                'label' => __('List')],

            // Custom template files:
            ['value' => 'Infortis_Base::product/list/slider.phtml',
                'label' => __('Slider')],
            // ['value' => 'Infortis_Base::product/list/slider_multi.phtml',
            //  'label' => __('Thumbnails slider')],

        ];
    }
}
