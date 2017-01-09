<?php

namespace Infortis\Base\Model\System\Config\Source\Category;

class Altimagecolumn
{
    public function toOptionArray()
    {
        return [
            ['value' => 'label',            'label' => __('Label')],
            // ['value' => 'position',         'label' => __('Sort Order')],
        ];
    }
}
