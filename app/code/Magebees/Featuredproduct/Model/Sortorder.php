<?php 
/***************************************************************************
 Extension Name	: Featured Products 
 Extension URL	: https://www.magebees.com/featured-products-extension-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\Featuredproduct\Model;

class Sortorder implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' =>'asc', 'label' => __('Ascending')],['value' =>'desc', 'label' => __('Descending')], ['value' =>'rand', 'label' => __('Random')]];
    }

    public function toArray()
    {
        return [0 => __('Random'), 1 => __('Descending'),2=>__('Ascending')];
    }
}
?>
