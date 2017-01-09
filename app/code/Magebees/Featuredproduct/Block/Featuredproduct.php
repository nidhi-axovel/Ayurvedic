<?php
/***************************************************************************
 Extension Name	: Featured Products 
 Extension URL	: https://www.magebees.com/featured-products-extension-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\Featuredproduct\Block;

class Featuredproduct extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_orderstatus;
	protected $_fpmanualCollection;
	protected $_datetime;
    protected $_collection;
    protected $_stock;
	protected $_config;
	protected $_product_visibility;
	protected $_sliderconfig;
	protected $_moduleManager;
	protected $urlHelper;
	protected $_imageHelper;
	protected $_storeManager;
	protected $_featuredproducts;
	protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';
 const PAGE_VAR_NAME = 'np';
	 public function __construct(
      	\Magento\Catalog\Block\Product\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\Magebees\Featuredproduct\Model\Resource\Customcollection\Collection $fpmanualCollection,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magento\Sales\Model\Order\Status\History $orderstatus,
		\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
		\Magento\CatalogInventory\Helper\Stock $stockHelper,
      		array $data = []
    )
	{
		 $this->_coreResource = $resource;
		 $this->urlHelper = $urlHelper;
		 $this->_fpmanualCollection = $fpmanualCollection;
		 $this->_datetime=$datetime;
		 $this->_orderstatus=$orderstatus;
		 $this->productCollection=$productCollection;
		 $this->stockHelper=$stockHelper;

		parent::__construct($context,$data);
		
		
	}
	
	public function getConfig()
	{
		return $this->_scopeConfig->getValue('featuredproduct/setting',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	} 
	public function getSliderconfig()
	{
	    return $this->_scopeConfig->getValue('featuredproduct/slidersetting',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	} 
	
	public function _toHtml(){	
	
	$this->_config=$this->getConfig();
	if($this->_config['enable']=="0")
	{
		return '';
	}
		
		if(!$this->getTemplate()){
			
		$this->setTemplate('featured_grid.phtml');
			
			
		}
        return parent::_toHtml();    
    }
	
	
	public function setWidgetOptions()
	{
		
		$this->setShowHeading((bool)$this->getWdShowHeading());
		$this->setHeading($this->getWdHeading());
		$this->setFeaturedproduct($this->getWdFeaturedproduct());
		$this->setCategories($this->getWdCategories());
		$this->setSortBy($this->getWdSortBy());
		$this->setSortOrder($this->getWdSortOrder());
		$this->setProductsPrice((bool)$this->getWdPrice());		
		$this->setDescription((bool)$this->getWdDescription());
		$this->setAddToCart((bool)$this->getWdCart());
		$this->setAddToWishlist((bool)$this->getWdWishlist());
		$this->setAddToCompare((bool)$this->getWdCompare());
		$this->setOutOfStock((bool)$this->getWdOutStock());
		
		//Template Settings
		$this->setNoOfProduct((int)$this->getWdNoOfProduct());
		$this->setProductsPerRow((int)$this->getWdProductsPerRow());
		$this->setShowSlider((bool)$this->getWdSlider()); 
		
		//slider Settings
		$this->setAutoscroll((bool)$this->getWdAutoscroll());
		$this->setPagination((bool)$this->getWdPagination());
		$this->setNavarrow((bool)$this->getWdNavarrow()); 
		
		
	}
	
	public function setConfigValues()
	{
		$this->_config=$this->getConfig();
		$this->_sliderConfig=$this->getSliderconfig();
		$this->setEnabled((bool)$this->_config['enable']);
		$this->setShowHeading((bool)$this->_config['show_heading']);
		$this->setHeading($this->_config['heading']);
		$this->setFeaturedproduct($this->_config['featuredproduct']);
		$this->setCategories($this->_config['categories']);
		$this->setSortBy($this->_config['sort_by']);
		$this->setSortOrder($this->_config['sort_order']);
		$this->setProductsPrice((bool)$this->_config['price']);		
		$this->setDescription((bool)$this->_config['description']);
		$this->setAddToCart((bool)$this->_config['cart']);
		$this->setAddToWishlist((bool)$this->_config['wishlist']);
		$this->setAddToCompare((bool)$this->_config['compare']);
		$this->setOutOfStock((bool)$this->_config['out_stock']);
		
		//Template Settings
		$this->setNoOfProduct((int)$this->_config['no_of_product']);
		$this->setProductsPerRow((int)$this->_config['products_per_row']);
		$this->setShowSlider((bool)$this->_config['slider']); 

		//slider Settings
		$this->setAutoscroll((bool)$this->_sliderConfig['autoscroll']);
		$this->setPagination((bool)$this->_sliderConfig['pagination']);
		$this->setNavarrow((bool)$this->_sliderConfig['navarrow']); 
		
	}
	
	protected function _beforeToHtml(){
		
		if($this->getType()=="Magebees\Featuredproduct\Block\Widget\Featuredwidget\Interceptor")
		{
			$this->setWidgetOptions();				
		}
		
		elseif($this->getType()=="Magebees\Featuredproduct\Block\Widget\Featuredwidget")
		{
			$this->setWidgetOptions();
		}
		else
		{
			$this->setConfigValues();
		}
		$this->setProductCollection($this->getFeaturedproductsCollection());
		
	}

  	/****Get Product collection for manually Featuredproduct product*****/

	public function getFeaturedproductsCollection()
	{	$storeId=$this->_storeManager->getStore()->getId();
		$product_ids=$this->getProductsIds();
		$finalcollection=$this->productCollection
			   ->addAttributeToSelect('name')
			   ->addFinalPrice()
			   ->addAttributeToSelect('*')
			   ->setStore($storeId)			 
			   ->addStoreFilter($storeId)
			   ->addFieldToFilter('entity_id',array('in' =>$product_ids))
			   ->addAttributeToFilter('visibility', 4);	 
			
        //Display out of stock products
		if(!$this->getOutOfStock()){			
			$this->stockHelper->addInStockFilterToCollection($finalcollection);
		}	
	
		
		//Display By Category
		
		if($this->getFeaturedproduct()==2){
			if($this->getCategories())
			{
			$categorytable = $this->_coreResource->getTableName('catalog_category_product');
			$finalcollection->getSelect()
						->joinLeft(array('ccp' => $categorytable),'e.entity_id = ccp.product_id','ccp.category_id')
						->group('e.entity_id')
						->where("ccp.category_id IN (".$this->getCategories().")");
					
			}
		}			
		
		//Set Sort Order		
		if($this->getSortOrder()=='rand'){
			
			$finalcollection->getSelect()->order('rand()');
		}else{
					$finalcollection->addAttributeToSort($this->getSortBy(),$this->getSortOrder());
			
		}	
		
		  return $finalcollection;
	}
	
	public function getProductsIds()
	{
		$storeId=$this->_storeManager->getStore()->getId();
		$customcollection=$this->_fpmanualCollection->getData();
	
		foreach($customcollection as $custom)
		{	
			if($custom['store_id']==$storeId)
			{
				$product_ids=$custom['entity_id'];
			}
		}
		
		if(empty($product_ids))
		{
			foreach($customcollection as $custom)
			{	
				$store_arr=array(0,$storeId);
				foreach($store_arr as $store)
				{
					if($custom['store_id']==$store)
					{
						$product_ids[]=$custom['entity_id'];
					}
				}
			}
			if(!empty($product_ids))
			{
			$new_entityId= implode(",",$product_ids);
			$new= explode(",",$new_entityId);
			$entity=array_unique($new);
			}
			else
			{ 
				return $product_ids=[0];
			}
			return $entity;
			
		}
		else
		{
			$entity= explode(",",$product_ids);
			return $entity;
		}
		
	}
    	
	public function getImageHelper()
	{
		return $this->_imageHelper;
	}

	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
	{
		$url = $this->getAddToCartUrl($product);
		return [
		    'action' => $url,
		    'data' => [
			'product' => $product->getEntityId(),
			\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED =>
			    $this->urlHelper->getEncodedUrl($url),
		    ]
		];
	}
}
