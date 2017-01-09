<?php

namespace Infortis\Base\Model\Import;

use Infortis\Base\Helper\Data as HelperData;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Simplexml\Config;
use Psr\Log\LoggerInterface;
class Cms extends AbstractModel
{
    /**
     * @var ManagerInterface
     */
    protected $_messageManagerInterface;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var LoggerInterface
     */
    protected $_logLoggerInterface;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $moduleDirHelper;

    /**
     * Path to directory with import files
     *
     * @var string
     */
    protected $_importPath;
    
    protected $_pageFactory;
    protected $_blockFactory;
    
    /**
     * Create path
     */
    public function __construct(Context $context, 
        Registry $registry, 
        ManagerInterface $messageManagerInterface,         
        HelperData $helperData,               
        \Magento\Framework\Module\Dir $moduleDirHelper,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,        
        AbstractResource $resource=null, 
        AbstractDb $resourceCollection=null,  
        array $data = []
    ) {
        $this->_messageManagerInterface = $messageManagerInterface;
        $this->_helperData = $helperData;
        $this->_logLoggerInterface = $context->getLogger();
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
        $this->moduleDirHelper = $moduleDirHelper;
        
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
    /**
    * Using dynamic strings as model aliases doesn't work in Magento 2
    * Since Magento 2 needs/wants to generate all classes before deploying
    * to production.  This method allows us to pick which factory our code
    * will use based on the old model string. Since we inject both objects,
    * this should be safe
    */  
    protected function getFactoryFromModel($modelString)
    {
        switch($modelString)
        {
            case 'cms/block':
                return $this->_blockFactory;
            case 'cms/page':
                return $this->_pageFactory;             
            default:
                throw new \Exception("I don't have a $modelString factory");
        }
        
        throw new \Exception("I don't have a $modelString factory, and how did execution get here?");       
    }
    /**
     * Import CMS items
     *
     * @param string model string
     * @param string name of the main XML node (and name of the XML file)
     * @param bool overwrite existing items
     */
    public function importCmsItems($modelString, $itemContainerNodeString, $overwrite = false, $package = 'Infortis_Base')
    {
        $this->_importPath = $this->moduleDirHelper->getDir($package) . '/etc/importexport/cms/';

        try
        {
            $xmlPath = $this->_importPath . $itemContainerNodeString . '.xml';
            if (!is_readable($xmlPath))
            {
                throw new \Exception(
                    __("Can't read data file: %1", $xmlPath)
                    );
            }
            $xmlObj = new Config($xmlPath);
            
            $conflictingOldItems = [];
            $i = 0;
            foreach ($xmlObj->getNode($itemContainerNodeString)->children() as $b)
            {
                //Check if block already exists
                $oldBlocks = $this->getFactoryFromModel($modelString)->create()->getCollection()
                    ->addFieldToFilter('identifier', $b->identifier) //array('eq' => $b->identifier)
                    ->load();
                
                //If items can be overwritten
                if ($overwrite)
                {
                    if (count($oldBlocks) > 0)
                    {
                        $conflictingOldItems[] = $b->identifier;
                        foreach ($oldBlocks as $old)
                            $old->delete();
                    }
                }
                else
                {
                    if (count($oldBlocks) > 0)
                    {
                        $conflictingOldItems[] = $b->identifier;
                        continue;
                    }
                }
                
                // ObjectManager::getInstance()->create($modelString)
                $this->getFactoryFromModel($modelString)->create()
                    ->setTitle($b->title)
                    ->setContent($b->content)
                    ->setIdentifier($b->identifier)
                    ->setIsActive($b->is_active)
                    ->setStores([0])
                    ->save();
                $i++;
            }
            
            //Final info
            if ($i)
            {
                $this->_messageManagerInterface->addSuccess(
                    __('Number of imported items: %1', $i)
                );
            }
            else
            {
                $this->_messageManagerInterface->addNotice(
                    __('No items were imported')
                );
            }
            
            if ($overwrite)
            {
                if ($conflictingOldItems)
                    $this->_messageManagerInterface->addSuccess(
                        __('Items (%1) with the following identifiers were overwritten:<br />%1', count($conflictingOldItems), implode(', ', $conflictingOldItems))
                    );
            }
            else
            {
                if ($conflictingOldItems)
                    $this->_messageManagerInterface->addNotice(
                        __('Unable to import items (%1) with the following identifiers (they already exist in the database):<br />%2', count($conflictingOldItems), implode(', ', $conflictingOldItems))
                    );
            }
        }
        catch (\Exception $e)
        {
            $this->_messageManagerInterface->addError($e->getMessage());
            $this->_logLoggerInterface->error($e);
        }
    }
    
}
