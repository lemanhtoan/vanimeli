<?php
/*------------------------------------------------------------------------
 # VenusTheme Brand Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_Layerslider_Model_Banner extends Mage_Core_Model_Abstract
{
    protected function _construct() {	
        $this->_init('ves_layerslider/banner');
    }
	
	public function getLink(){
		return  Mage::getBaseUrl().Mage::getModel('core/url_rewrite')->loadByIdPath('veslayerslider/banner/'.$this->getId())->getRequestPath();
	}
	
	public function getImageUrl($type='l') {
		$tmp = explode("/", $this->getFile());
		$imageName = $type."-".$tmp[count($tmp)-1];
		return Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA)."resized/".$imageName;
	}
	
	public function getIconUrl( ) {
		return Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA)."/".$this->getIcon();
	}
	
	public function getFileUrl(){
		return Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA)."/".$this->getFile();
	}

	public function getSliderByAlias($alias = "") {
		if($alias)
			return $this->getCollection()->addFieldToFilter('alias', $alias)->getFirstItem();
		else 
			return null;
    }
}