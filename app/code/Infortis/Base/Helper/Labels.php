<?php

namespace Infortis\Base\Helper;

use Infortis\Base\Helper\Data as HelperData;
use Infortis\Base\Helper\GetNowBasedOnLocale;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Date;

class Labels extends AbstractHelper
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var GetNowBasedOnLocale
     */
    protected $_getNowBasedOnLocale;
    
    public function __construct(
        Context $context,
        HelperData $helperData,
        GetNowBasedOnLocale $getNowBasedOnLocale
    ) {
        $this->helper = $helperData;
        $this->_getNowBasedOnLocale = $getNowBasedOnLocale;

        parent::__construct($context);
    }

    /**
     * Get product labels (HTML)
     *
     * @return string
     */
    public function getLabels($product)
    {
        //TODO: test:
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/infortis.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        // $logger->info('->> name=' . $product->getName() );
        // $logger->info('news_from_date=' . $product->getData('news_from_date') );
        // $logger->info('news_to_date=' . $product->getData('news_to_date') );
        // $logger->info('new=' . $product->getData('new') );
        // $logger->info('special_from_date=' . $product->getData('special_from_date') );
        // $logger->info('special_to_date=' . $product->getData('special_to_date') );
        // $logger->info('sale=' . $product->getData('sale') );

        // $specialPrice = number_format($product->getFinalPrice(), 2);
        // $regularPrice = number_format($product->getPrice(), 2);
        
        // if ($specialPrice != $regularPrice)
        // {
        //     $logger->info('IS SPECIAL=' . $specialPrice );
        // }
        // $logger->info('sale, _nowIsBetween=' . $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date')) );
        // $logger->info("\n");

        $html = '';

        $isNew = false;
        if ($this->helper->getCfg('product_labels/new'))
        {   
            $isNew = $this->isNew($product);
        }
        
        $isSale = false;
        if ($this->helper->getCfg('product_labels/sale'))
        {
            $isSale = $this->isOnSale($product);
        }
        
        if ($isNew == true)
        {
            $html .= '<span class="sticker-wrapper top-left"><span class="sticker new">' . __('New') . '</span></span>';
        }
        
        if ($isSale == true)
        {
            $html .= '<span class="sticker-wrapper top-right"><span class="sticker sale">' . __('Sale') . '</span></span>';
        }
        
        return $html;
    }
    
    /**
     * Check if "new" label is enabled and if product is marked as "new"
     *
     * @return  bool
     */
    public function isNew($product)
    {
        //Check if product is marked as "new" OR if date range ("Set Product as New from/to Date") is set
        if ($product->getData('new') || $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date')))
        {
            return true;
        }
        return false;

        //Alternative way to check if product is new:
        //Check if product is marked as "new" AND if date range ("Set Product as New from/to Date") is set
        // if ($product->getData('new'))
        // {
        //     return $this->_nowIsBetween($product->getData('news_from_date'), $product->getData('news_to_date'));
        // }
        // return false;
    }

    /**
     * Check if product is on sale
     *
     * @return  bool
     */
    public function isOnSale($product)
    {
        //Check if product is marked as "sale" OR if date range ("Special Price From/To Date") is set
        if ($product->getData('sale') || $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date')))
        {
            return true;
        }
        return false;

        //Alternative way to check if product is on sale:
        //Check if product is marked as "sale" AND if date range ("Special Price From/To Date") is set
        // if ($product->getData('sale'))
        // {
        //     return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
        // }
        // return false;
    }

    // //Old version of this method:
    // public function isOnSale($product)
    // {
    //     $specialPrice = number_format($product->getFinalPrice(), 2);
    //     $regularPrice = number_format($product->getPrice(), 2);
        
    //     if ($specialPrice != $regularPrice)
    //         return $this->_nowIsBetween($product->getData('special_from_date'), $product->getData('special_to_date'));
    //     else
    //         return false;
    // }
    
    protected function _nowIsBetween($fromDate, $toDate)
    {
        if ($fromDate)
        {
            $fromDate = strtotime($fromDate);
            $toDate = strtotime($toDate);
            $now = strtotime($this->_getNowBasedOnLocale->getNow());
            if ($toDate)
            {
                if ($fromDate <= $now && $now <= $toDate)
                    return true;
            }
            else
            {
                if ($fromDate <= $now)
                    return true;
            }
        }
        
        return false;
    }
}
