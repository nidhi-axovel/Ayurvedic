<?php

namespace Infortis\Base\Model\System\Config\Backend\Design\Color;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Validatetransparent extends Value
{
    /**
     * @var ManagerInterface
     */
    protected $_messageManagerInterface;

    public function __construct(Context $context, 
        Registry $registry, 
        ScopeConfigInterface $config, 
        TypeListInterface $cacheTypeList, 
        AbstractResource $resource = null, 
        AbstractDb $resourceCollection = null, 
        array $data = [], 
        ManagerInterface $messageManagerInterface = null)
    {
        $this->_messageManagerInterface = $messageManagerInterface;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

	public function save()
	{
		//Get the value from config
		$v = $this->getValue();
		if ($v == 'rgba(0, 0, 0, 0)')
		{
			$this->setValue('transparent');
			//$this->_messageManagerInterface->addNotice("value == rgba(0, 0, 0, 0)");
		}
		return parent::save();
	}
}
