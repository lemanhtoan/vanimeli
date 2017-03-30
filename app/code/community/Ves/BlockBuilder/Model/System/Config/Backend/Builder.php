<?php
class Ves_BlockBuilder_Model_System_Config_Backend_Builder extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
	    if($this->getPath() === 'ves_blockbuilder/ves_blockbuilder/show' || $this->getPath() === 'ves_blockbuilder/ves_blockbuilder/cache_lifetime') {
	        // Code that flushes cache goes here
	        Mage::app()->cleanCache( array(
		        Mage_Core_Model_Store::CACHE_TAG,
		        Mage_Cms_Model_Block::CACHE_TAG,
		        Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG
		    ) );
		    Mage::app()->cleanCache( array(
		        Mage_Core_Model_Store::CACHE_TAG,
		        Mage_Cms_Model_Block::CACHE_TAG,
		        Ves_BlockBuilder_Model_Block::CACHE_PAGE_TAG
		    ) );
	    }
	}
}