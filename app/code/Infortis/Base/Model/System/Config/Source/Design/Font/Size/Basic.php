<?php

namespace Infortis\Base\Model\System\Config\Source\Design\Font\Size;

class Basic
{
	public function toOptionArray()
	{
		return [
			['value' => '12',		'label' => __('12 px')],
			['value' => '13',		'label' => __('13 px')],
			['value' => '14',		'label' => __('14 px')],
			['value' => '15',		'label' => __('15 px')],
			['value' => '16',		'label' => __('16 px')],
		];
	}
}