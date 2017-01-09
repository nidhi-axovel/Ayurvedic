<?php

namespace Infortis\Infortis\Helper;

use Magento\Catalog\Helper\Image as HelperImage;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Image extends AbstractHelper
{
    /**
     * @var Image helper
     */
    protected $helperImage;

    public function __construct(
        Context $context, 
        HelperImage $helperImage
    ) {
        $this->helperImage = $helperImage;

        parent::__construct($context);
    }

    /**
     * Get URL of product image
     *
     * @param Product   $product    Product
     * @param string    $imgId      Image version
     * @param int       $w          Image width
     * @param int       $h          Image height
     * @return string
     */
    public function getImg($product, $imgId = 'product_base_image', $w = null, $h = null)
    {
        $this->helperImage
            ->init($product, $imgId);

        if ($w || $h)
        {
            if ($w && $h)
            {
                $this->helperImage
                    ->resize($w, $h);
            }
            else
            {
                $this->helperImage
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->resize($w, $h);
            }
        }

        return $this->helperImage->getUrl();
    }

    /**
     * Get URL of product image
     *
     * @param Product   $product    Product
     * @param string    $imgId      Image version
     * @param int       $w          Image width
     * @param int       $h          Image height
     * @param mixed     $file       Image file
     * @param array     $attributes Attributes
     * @return string
     */
    public function getImgExtended($product, $imgId = 'product_base_image', $w = null, $h = null, $file = null, $attributes = [])
    {
        $this->helperImage
            ->init($product, $imgId, $attributes);

        if ($file != null)
        {
            $this->helperImage
                ->setImageFile($file);
        }

        if ($w || $h)
        {
            if ($w && $h)
            {
                $this->helperImage
                    ->resize($w, $h);
            }
            else
            {
                $this->helperImage
                    ->constrainOnly(true)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->resize($w, $h);
            }
        }

        return $this->helperImage->getUrl();
    }
}
