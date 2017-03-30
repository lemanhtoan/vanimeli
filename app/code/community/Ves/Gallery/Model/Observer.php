<?php
class Ves_Gallery_Model_Observer
{
   public function beforeRender( Varien_Event_Observer $observer ){
		$this->_loadMedia();	
   }
   function _loadMedia( $config = array()){
	   	/*
		if( Mage::getStoreConfig("ves_gallery/ves_gallery/show") ) {
			$mediaHelper =  Mage::helper('ves_gallery/media');
			$mediaHelper->addMediaFile("js","venustheme/ves_tempcp/jquery/jquery.colorbox.js");
			$mediaHelper->addMediaFile("js","venustheme/ves_tempcp/jquery/colorbox.css");
		}
		*/
	}
}
