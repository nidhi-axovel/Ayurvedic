<?php

namespace Infortis\UltraSlideshow\Model\Source\Navigation\Pagination;

class Position
{
    public function toOptionArray()
    {
        return [
			['value' => 'pagination-pos-bottom-centered',		'label' => __('Bottom, centered')],
			['value' => 'pagination-pos-bottom-right',			'label' => __('Bottom, right')],
			['value' => 'pagination-pos-bottom-left',			'label' => __('Bottom, left')],
			['value' => 'pagination-pos-over-bottom-centered',	'label' => __('Bottom, centered, over the slides')],
			['value' => 'pagination-pos-over-bottom-right',	'label' => __('Bottom, right, over the slides')],
			['value' => 'pagination-pos-over-bottom-left',		'label' => __('Bottom, left, over the slides')],
        ];
    }
}
