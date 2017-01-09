<?php

namespace Infortis\Base\Model\System\Config\Source\Product\Tabs;

class Mode
{
    public function toOptionArray()
    {
    	//Important: note the order of values - "Tabs" moved to first position
		return [
			['value' => 3,		'label' => __('Tabs')],
			['value' => 1,		'label' => __('Tabs/Accordion')],
			['value' => 2,		'label' => __('Accordion')],
        ];
    }
}