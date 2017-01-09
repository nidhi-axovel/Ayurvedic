<?php

namespace Infortis\Base\Model\System\Config\Source\Layout\Screen\Width;

class Widecustom
{
    public function toOptionArray()
    {
		return [
            ['value' => '768',      'label' => __('768 px')],
            ['value' => '992',      'label' => __('992 px')],
            ['value' => '1200',     'label' => __('1200 px')],
            ['value' => '1440',     'label' => __('1440 px')],
            ['value' => '1680',     'label' => __('1680 px')],
            ['value' => '1920',     'label' => __('1920 px')],
            ['value' => 'full',     'label' => __('Full width')],
            ['value' => 'custom',   'label' => __('Custom width...')],
        ];
    }
}
