<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block;

class Sidebar extends AbstractBlock
{	
	/**
	 * Stores all templates for each widget block
	 *
	 * @var array
	 */
	protected $_widgets = array();

	/**
	 * Add a widget type
	 *
	 * @param string $name
	 * @param string $block
	 * @return \FishPig\WordPress\Block\Sidebar
	 */
	public function addWidgetType($name, $class)
	{
		if (!isset($this->_widgets[$name])) {
			$this->_widgets[$name] = $class;
		}
	
		return $this;
	}
	
	/**
	 * Retrieve information about a widget type
	 *
	 * @param string $name
	 * @return false|array
	 */
	public function getWidgetType($name)
	{
		return isset($this->_widgets[$name]) ? $this->_widgets[$name] : false;
	}
	
	/**
	 * Load all enabled widgets
	 *
	 * @return \FishPig\WordPress\Block\Sidebar
	 */
	protected function _beforeToHtml()
	{
		if ($widgets = $this->getWidgetsArray()) {
			$this->_initAvailableWidgets();

			foreach($widgets as $widgetType) {
				$name = $this->_getWidgetName($widgetType);
				$widgetIndex = $this->_getWidgetIndex($widgetType);

				if ($class = $this->getWidgetType($name)) {
					if ($block = $this->getLayout()->createBlock($class)) {
						$block->setWidgetType($name);
						$block->setWidgetIndex($widgetIndex);
						
						$this->setChild('wordpress_widget_' . $widgetType, $block);
					}
				}
			}
		}
		
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar.phtml');
		}

		return parent::_beforeToHtml();
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetName($widget)
	{
		return rtrim(preg_replace("/[^a-z_-]/i", '', $widget), '-');
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetIndex($widget)
	{
		if (preg_match("/([0-9]{1,})/",$widget, $results)) {
			return $results[1];
		}
		
		return false;
	}
	
	public function getWidgetArea()
	{
		return 'sidebar-main';
	}
	
	/**
	 * Retrieve the sidebar widgets as an array
	 *
	 * @return false|array
	 */
	public function getWidgetsArray()
	{
		if ($this->getWidgetArea()) {
			$widgets = $this->_config->getOption('sidebars_widgets');

			if ($widgets) {
				$widgets = unserialize($widgets);
				
				$realWidgetArea = $this->getRealWidgetArea();

				if (isset($widgets[$realWidgetArea])) {
					return $widgets[$realWidgetArea];
				}
			}
		}

		return false;
	}
	
	/**
	 * Get the real widget area by using the Custom Sidebars plugin
	 *
	 * @return string
	 */
	public function getRealWidgetArea()
	{
		return 'sidebar-main';
		if (!Mage::helper('wordpress')->isPluginEnabled('custom-sidebars/customsidebars.php')) {
			return $this->getWidgetArea();
		}

		$settings = @unserialize(Mage::helper('wordpress')->getWpOption('cs_modifiable'));
		
		if (!$settings) {
			return $this->getWidgetArea();
		}

		$handles = $this->getLayout()->getUpdate()->getHandles();

		if (!isset($settings['modifiable']) || array_search($this->getWidgetArea(), $settings['modifiable']) === false) {
			return $this->getWidgetArea();
		}
		
		if ($post = Mage::registry('wordpress_post')) {
			# Check post specific
			if ($value = $post->getMetaValue('_cs_replacements')) {
				$value = @unserialize($value);
				
				if (isset($value[$this->getWidgetArea()])) {
					return $value[$this->getWidgetArea()];
				}
			}

			# Single post by type
			if ($widgetArea = $this->_getArrayValue($settings, 'post_type_single/' . $post->getPostType() . '/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
			
			# Single post by category
			if ($categoryIdResults = $post->getResource()->getParentTermsByPostId($post->getId(), $taxonomy = 'category')) {
				$categoryIdResults = array_pop($categoryIdResults);

				if (isset($categoryIdResults['category_ids'])) {
					foreach(explode(',', $categoryIdResults['category_ids']) as $categoryId) {
						if ($widgetArea = $this->_getArrayValue($settings, 'category_single/' . $categoryId . '/' . $this->getWidgetArea())) {
							return $widgetArea;
						}
					}
				}
			}
		}
		else if ($postType = Mage::registry('wordpress_post_type')) {
			if (isset($settings['post_type_archive']) && isset($settings['post_type_archive'][$postType->getPostType()]) && isset($settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()])) {
				return $settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()];
			}
		}
		else if ($term = Mage::registry('wordpress_term')) {
			if ($widgetArea = $this->_getArrayValue($settings, $term->getTaxonomy() . '_archive/' . $term->getId() . '/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
		}
		else if (in_array('wordpress_homepage', $handles)) {
			if ($widgetArea = $this->_getArrayValue($settings, 'blog/' . $this->getWidgetArea())) {
				return $widgetArea;
			}	
		}
		else if ($author = Mage::registry('wordpress_author')) {
			if ($widgetArea = $this->_getArrayValue($settings, 'authors/' . $author->getId() . '/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
		}
		else if (in_array('wordpress_search_index', $handles)) {
			if ($widgetArea = $this->_getArrayValue($settings, 'search/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
		}
		else if (in_array('wordpress_archive_view', $handles)) {
			if ($widgetArea = $this->_getArrayValue($settings, 'date/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
		}
		else if (in_array('wordpress_post_tag_view', $handles)) {
			if ($widgetArea = $this->_getArrayValue($settings, 'tags/' . $this->getWidgetArea())) {
				return $widgetArea;
			}
		}
		
		return $this->getWidgetArea();
	}
	
	/**
	 * Retrieve a deep value from a multideimensional array
	 *
	 * @param array $arr
	 * @param string $key
	 * @return string|null
	 */
	protected function _getArrayValue($arr, $key)
	{
		$keys = explode('/', trim($key, '/'));
		
		foreach($keys as $key) {
			if (!isset($arr[$key])) {
				return null;
			}
			
			$arr = $arr[$key];
		}
		
		return $arr;
	}
	
	/**
	 * Initialize the widgets from the config.xml
	 *
	 * @return $this
	 */
	protected function _initAvailableWidgets()
	{
		$availableWidgets = $this->_config->getWidgets();
		
		foreach($availableWidgets as $name => $class) {
			$this->addWidgetType($name, $class);
		}
		
		return $this;
	}
	
	/**
	 * Determine whether or not to display the sidebar
	 *
	 * @return int
	 */
	public function canDisplay()
	{
		return 1;
	}
	
	/**
	 * Set the widget area.
	 * This allows for support for Simple Page Sidebars
	 *
	 * @param string $widgetArea
	 * @return $this
	 */
	public function setWidgetArea($widgetArea)
	{
		if ($this->hasWidgetArea()) {
			return $this;
		}
		
		$this->setData('widget_area', $widgetArea);

		$widgetArea = null;
		
		# Deprecated. Use Custom Sidebars plugin instead
		if ($post = Mage::registry('wordpress_post')) {
			$widgetArea = $post->getMetaValue('_sidebar_name');
		}
		else if ($page = Mage::registry('wordpress_page')) {
			$widgetArea = $page->getMetaValue('_sidebar_name');
		}
		
		if (!$widgetArea) {
			return $this;
		}

		$widgetArea = 'page-sidebar-' . preg_replace('/([^a-z0-9_-]{1,})/', '', strtolower(trim($widgetArea)));
		
		return $this->setData('widget_area', $widgetArea);
	}
}
