<?php

namespace Infortis\Base\Model\System\Config\Source\Category\Grid;

class Size
{
	public function toOptionArray()
	{
		return [
			['value' => '',	'label' => __('Default')],
			['value' => 's',	'label' => __('Size S')],
			['value' => 'xs',	'label' => __('Size XS')],
		];
	}
}