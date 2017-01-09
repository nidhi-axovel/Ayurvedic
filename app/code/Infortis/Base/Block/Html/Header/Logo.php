<?php

namespace Infortis\Base\Block\Html\Header;

use Magento\Theme\Block\Html\Header\Logo as MagentoHeaderLogo;
use Magento\Framework\View\Element\Template\Context;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\ScopeInterface;

class Logo extends MagentoHeaderLogo
{
    const STICKY_LOGO_SUFFIX = '_sticky';

    /**
     * @param Context $context
     * @param Database $fileStorageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Database $fileStorageHelper,
        array $data = []
    ) {
        parent::__construct($context, $fileStorageHelper, $data);
    }

    /**
     * Get sticky logo image URL
     *
     * @return string|false
     */
    public function getStickyLogoSrc()
    {
        return $this->getAdditionalLogoSrc(self::STICKY_LOGO_SUFFIX);
    }

    /**
     * TODO: Optimize: probably no need to find out default logo path again. It's already done in parent class method _getLogoUrl(). Override that method.
     * Get logo image URL with suffix
     *
     * @param string
     * @return string|false
     */
    protected function getAdditionalLogoSrc($suffix)
    {
        //Get default logo
        $storeLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            ScopeInterface::SCOPE_STORE
        );
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $path = $folderName . '/' . $storeLogoPath;
        $newPath = '';

        if ($storeLogoPath !== null && $this->_isFile($path))
        {
            $defaultLogoSrc = $storeLogoPath;

            $newLogoSrc = $this->getFilePathWithSuffix($defaultLogoSrc, $suffix);
            $newPath = $folderName . '/' . $newLogoSrc;
            $newLogoUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $newPath;

            // Check if image file exists
            if ($this->_isFile($newPath))
            {
                return $newLogoUrl;
            }
            else
            {
                return false;
            }
        }
        elseif ($this->getLogoFile())
        {
            $defaultLogoSrc = $this->getLogoFile(); //Get param from XML
            $newLogoSrc = $this->getFilePathWithSuffix($defaultLogoSrc, $suffix);
            $newLogoUrl = $this->getViewFileUrl($newLogoSrc);
        }
        else
        {
            $defaultLogoSrc = 'images/logo.svg';
            $newLogoSrc = $this->getFilePathWithSuffix($defaultLogoSrc, $suffix);
            $newLogoUrl = $this->getViewFileUrl($newLogoSrc);
        }

        return $newLogoUrl;
    }

    /**
     * Get file path with additional suffix
     *
     * @param string
     * @param string
     * @return string
     */
    protected function getFilePathWithSuffix($originalFilePath, $suffix)
    {
        $info = pathinfo($originalFilePath);
        return $info['dirname'] . '/' . $info['filename'] . $suffix . '.' . $info['extension'];
    }
}
