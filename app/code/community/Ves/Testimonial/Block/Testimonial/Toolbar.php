<?php
 /*------------------------------------------------------------------------
  # Ves Testimonial Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_Testimonial_Toolbar extends Mage_Core_Block_Template {
	protected function _prepareLayout() {			 
		
	}
	public function getTotal() {
		return Mage::registry('paginateTotals');
    }
    
    public function getPages() {
		return ceil(($this->getTotal())/(int)$this->getLimitPerPage() );
    }
	
	public function getLimitPerPage(){
		return Mage::registry('paginateLimitPerPages');
	}

	public function getCurrentLink() {
		$module = $this->getRequest()->getModuleName();
		$controller = $this->getRequest()->getControllerName();
		$module = strtolower($module);
		if($module == "ves-testimonial" || $module == "testimonial"){
			$route = trim( Mage::getStoreConfig('ves_testimonial/general_setting/route') );
			return  Mage::getBaseUrl().$module;
		}
 		return;
 	}
}

?>