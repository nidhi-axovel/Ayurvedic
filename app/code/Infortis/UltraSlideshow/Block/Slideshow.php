<?php
namespace Infortis\UltraSlideshow\Block;

use Infortis\UltraSlideshow\Helper\Data as HelperData;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

class Slideshow extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;

    /**
     * @var DesignInterface
     */
    protected $_viewDesignInterface;

    /**
     * @var Session
     */
    protected $_modelSession;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;

    protected $_isPredefinedHomepageSlideshow = false;
    protected $_slides = [];
    protected $_banners = NULL;
    protected $_cacheKeyArray = NULL;
    protected $_coreHelper;

    public function __construct(
        Context $context, 
        HelperData $helperData,
        Session $modelSession, 
        LayoutFactory $viewLayoutFactory,
        \Magento\Cms\Model\BlockFactory $cmsBlockModelFactory,
        array $data = []
    ) {
        $this->_cmsBlockModelFactory = $cmsBlockModelFactory; //TODO: remove
        $this->_helperData = $helperData;
        $this->_modelStoreManagerInterface = $context->getStoreManager();
        $this->_viewDesignInterface = $context->getDesignPackage();
        $this->_modelSession = $modelSession;
        $this->_viewLayoutFactory = $viewLayoutFactory;

        parent::__construct($context, $data);
    }

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_coreHelper = $this->_helperData;

        $this->addData([
            'cache_lifetime'    => 99999999,
            'cache_tags'        => [Product::CACHE_TAG],
        ]);
    }

    public function getHelperData()
    {
        return $this->_helperData;
    }
    
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if (NULL === $this->_cacheKeyArray)
        {
            $this->_cacheKeyArray = [
                'INFORTIS_ULTRASLIDESHOW',
                $this->_storeManager->getStore()->getCode(),
                $this->_storeManager->getWebsite()->getCode(),
                $this->_viewDesignInterface->getDesignTheme()->getCode(),               
                $this->_modelSession->getCustomerGroupId(),
                'template' => $this->getTemplate(),
                'name' => $this->getNameInLayout(),
                (int) $this->_storeManager->getStore()->isCurrentlySecure(),
                implode(".", $this->getSlideIds()),
                $this->getBannersId(),
                $this->_isPredefinedHomepageSlideshow,
            ];
        }

        return $this->_cacheKeyArray;
    }

    /**
     * Create unique block id for frontend
     *
     * @return string
     */
    public function getFrontendHash()
    {
        return md5(implode("+", $this->getCacheKeyInfo()));
    }

    /**
     * Get array of slides (static blocks) identifiers. Blocks will be displayed as slides.
     *
     * @return array
     */
    public function getSlideIds()
    {
        $slides = [];
        if ($this->_slides)
        {
            return $this->_slides;
        }
        
        // No predefined slides. Get slides from parameter
        $slides = $this->getParamStaticBlockIds();
        if (empty($slides))
        {
            //If this is predefined slideshow, get slides from module config
            if ($this->_isPredefinedHomepageSlideshow)
            {
                $slides = $this->getConfigStaticBlockIds();
            }
        }

        // //Magento 2 insists we pass an ID, not an identifier
        // $blocks = $this->_cmsBlockModelFactory->create()->getCollection()
        // ->addFieldToFilter('identifier', ['in'=>$slides]);

        // if(count($blocks) > 0)
        // {
        //     $new_slides = [];
        //     foreach($blocks as $block)
        //     {
        //         $new_slides[] = $block->getId();
        //     }
        //     $slides = $new_slides;
        // }

        // Retrieved slides can be saved for further processing
        $this->_slides = $slides;
        return $this->_slides;
    }

    /**
     * Get array of static blocks identifiers from parameter
     *
     * @return array
     */
    public function getParamStaticBlockIds()
    {
        $slides = $this->getSlides(); //param: slides
        if ($slides === NULL) //Param not set
        {
            return [];
        }

        $blockIds = explode(",", str_replace(" ", "", $slides));
        return $blockIds;
    }

    /**
     * Get array of static blocks identifiers from module config
     *
     * @return array
     */
    public function getConfigStaticBlockIds()
    {
        $blockIds = [];
        $blockIdsString = $this->_coreHelper->getCfg('general/blocks');

        if (!empty($blockIdsString))
        {
            $blockIds = explode(",", str_replace(" ", "", $blockIdsString));
        }

        return $blockIds;
    }

    /**
     * Get an identifier of additional side banners (static block)
     *
     * @return string
     */
    public function getBannersId()
    {
        if ($this->_banners)
        {
            return $this->_banners;
        }

        // No predefined banners. Get banners from parameter.
        $bid = $this->getBanner(); //param: banner
        if ($bid === NULL) //Param not set
        {
            // If this is predefined slideshow, get banners from module config
            if ($this->_isPredefinedHomepageSlideshow)
            {
                //Get banners from module config
                $bid = $this->_coreHelper->getCfg('banners/banners');
            }
        }

        // Retrieved banners can be saved for further processing
        $bid = trim($bid);
        $this->_banners = $bid;
        return $this->_banners;
    }

    /**
     * Get HTML of the static block which contains additional banners for the slideshow
     *
     * @return string
     */
    public function getBannersHtml()
    {
        $bid = $this->getBannersId();
        if ($bid)
        {
            return $this->_viewLayoutFactory->create()->createBlock('Magento\Cms\Block\Block')->setBlockId($bid)->toHtml();
        }

        return '';
    }

    /**
     * Add slides ids
     *
     * @param string $ids
     * @return \Infortis\UltraSlideshow\Block\Slideshow
     */
    public function addSlides($ids)
    {
        $this->_slides = $ids;
        return $this;
    }

    /**
     * Add banner id
     *
     * @param string $ids
     * @return \Infortis\UltraSlideshow\Block\Slideshow
     */
    public function addBanner($ids)
    {
        $this->_banners = $ids;
        return $this;
    }

    /**
     * Set/Unset as predefined slideshow (e.g. for homepage)
     *
     * @param string $value
     * @return \Infortis\UltraSlideshow\Block\Slideshow
     */
    public function setPredefined($value)
    {
        $this->_isPredefinedHomepageSlideshow = $value;
        return $this;
    }

    /**
     * Check if slideshow is set as predefined
     *
     * @return bool
     */
    public function isPredefined()
    {
        return $this->_isPredefinedHomepageSlideshow;
    }

    /**
     * Get CSS style string with margins for slideshow wrapper
     *
     * @return string
     */
    public function getMarginStyles()
    {
        //Slideshow margin
        $slideshowMarginStyleProperties = '';

        $marginTop = intval($this->_coreHelper->getCfg('general/margin_top'));
        if ($marginTop !== 0)
        {
            $slideshowMarginStyleProperties .= "margin-top:{$marginTop}px;";
        }

        $marginBottom = intval($this->_coreHelper->getCfg('general/margin_bottom'));
        if ($marginBottom !== 0)
        {
            $slideshowMarginStyleProperties .= "margin-bottom:{$marginBottom}px;";
        }

        if ($slideshowMarginStyleProperties)
        {
            return 'style="' . $slideshowMarginStyleProperties . '"';
        }
    }

    /**
     * If slideshow position retrieved from config is different than expected position, set flag to not display the slideshow
     *
     * @param int $position
     * @param int $expectedPosition
     * @return \Infortis\UltraSlideshow\Block\Slideshow
     */
    /*
    public function displayOnExpectedPosition($position, $expectedPosition)
    {
        if ($position !== $expectedPosition)
        {
            $this->_canBeDisplayed = false;
        }
        return $this;
    }
    */

    /**
     * @deprecated
     * Get slideshow config
     *
     * @return string
     */
    public function getSlideshowCfg()
    {
        $h = $this->_coreHelper;
        
        $cfg = [];
        $cfg['fx']          = "'" . $h->getCfg('general/fx') . "'";
        
        if ($h->getCfg('general/easing'))
        {
            $cfg['easing']  = "'" . $h->getCfg('general/easing') . "'";
        }
        else
        {
            $cfg['easing']  = '';
        }
        
        $cfg['timeout']         = intval($h->getCfg('general/timeout'));
        $cfg['speed']           = intval($h->getCfg('general/speed'));
        $cfg['smooth_height']   = $h->getCfg('general/smooth_height');
        $cfg['pause']           = $h->getCfg('general/pause');
        $cfg['loop']            = $h->getCfg('general/loop');
        
        return $cfg;
    }
}