<?php
class Ves_Megamenu_Model_System_Config_Backend_Megamenu extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
	    // Code that flushes cache goes here
        Mage::app()->cleanCache( array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_Megamenu_Model_Megamenu::CACHE_BLOCK_TAG
	    ) );

	    Mage::app()->cleanCache( array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_Megamenu_Model_Megamenu::CACHE_WIDGET_TAG
	    ) );
	}
}