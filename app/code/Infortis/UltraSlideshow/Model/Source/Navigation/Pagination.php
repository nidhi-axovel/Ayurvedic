<?php

namespace Infortis\UltraSlideshow\Model\Source\Navigation;

class Pagination
{
    public function toOptionArray()
    {
        return [
			['value' => '',					'label' => __('Disabled')],
			['value' => 'slider-pagination1',	'label' => __('Style 1')],
			['value' => 'slider-pagination2',	'label' => __('Style 2')],
        ];
    }
}
