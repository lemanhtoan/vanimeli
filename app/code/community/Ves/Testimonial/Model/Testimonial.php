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

class Ves_Testimonial_Model_Testimonial extends Mage_Core_Model_Abstract
{
    protected function _construct() {	
        $this->_init('ves_testimonial/testimonial');
    }
  
	public function getFileUrl(){
		return Mage::getBaseUrl( Mage_Core_Model_Store::URL_TYPE_MEDIA)."/".$this->getFile();
	}
}