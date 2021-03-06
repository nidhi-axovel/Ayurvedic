<?php

namespace Infortis\Base\Model\System\Config\Backend\Header;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Centralcolunits extends Value
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
	
	public function _afterSave()
    {
		//Get the saved value
		$value = $this->getValue();
		
		//Get the value from config (previous value)
		$oldValue = $this->getOldValue();
		
		if ($value != $oldValue)
		{
			if (empty($value) || trim($value) === '')
			{
				$this->_messageManagerInterface->addNotice(
					__('Central Column in the header has been disabled and will not be displayed in the header. IMPORTANT: note that any blocks assigned to the Central Column will also not be displayed.')
				);
			}
			else
			{
				$this->_messageManagerInterface->addNotice(
					__('Width of the Central Column in the header has changed (previous value: %1). Note that sum of these columns has to be equal 12 grid units.', $oldValue)
				);
			}
		}
		
        return parent::_afterSave();
    }
}
