<?php
if (!class_exists("Ves_Megamenu_Block_Top")) {
    require_once Mage::getBaseDir('code') . DIRECTORY_SEPARATOR . "community".DIRECTORY_SEPARATOR."Ves".DIRECTORY_SEPARATOR."Megamenu".DIRECTORY_SEPARATOR."Block".DIRECTORY_SEPARATOR."Top.php";
}
class Ves_Megamenu_Block_Widget_Menu extends Ves_Megamenu_Block_Top implements Mage_Widget_Block_Interface
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
            Ves_Megamenu_Model_Megamenu::CACHE_WIDGET_TAG
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
           'VES_MEGAMENU_WIDGET_LIST',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }
	
}