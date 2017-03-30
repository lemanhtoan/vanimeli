<?php
class Ves_ProductCarousel_Model_System_Config_Backend_ProductCarousel_Carousel extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
	    // Code that flushes cache goes here
        Mage::app()->cleanCache( array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_ProductCarousel_Model_Config::CACHE_BLOCK_TAG
	    ) );
	    Mage::app()->cleanCache( array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_ProductCarousel_Model_Config::CACHE_WIDGET_TAG
	    ) );

	    Mage::getConfig()->deleteConfig("ves_productcarousel/carousel_setting/limit_cols");
	    Mage::getConfig()->deleteConfig("ves_productcarousel/carousel_setting/max_items");
	    Mage::getConfig()->deleteConfig("ves_productcarousel/effect_setting/limit_cols");
	    Mage::getConfig()->deleteConfig("ves_productcarousel/effect_setting/max_items");
	    Mage::getConfig()->deleteConfig("ves_productcarousel/ves_productcarousel/show_navigator");
	}
}