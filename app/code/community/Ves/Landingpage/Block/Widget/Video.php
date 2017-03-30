<?php
class Ves_Landingpage_Block_Widget_Video extends Ves_Landingpage_Block_List implements Mage_Widget_Block_Interface
{

	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
        parent::__construct($attributes);

        /*Cache Block*/
	      $enable_cache = $this->getConfig("enable_cache", 1 );
	      if(!$enable_cache) {
	        $cache_lifetime = null;
	      } else {
	        $cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
	        $cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
	      }

	      $this->addData(array('cache_lifetime' => $cache_lifetime));
	      $this->addCacheTag(array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_Landingpage_Model_Config::CACHE_WIDGET_TAG
	    ));
	}

	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'VES_LANDINGPAGE_WIDGET_VIDEO',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

	public function _toHtml() {
		$this->_show = $this->getConfig("show");
		
		if(!$this->_show) return;

        return parent::_toHtml();
	}
	
}