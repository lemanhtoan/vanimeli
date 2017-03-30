<?php 
 /*------------------------------------------------------------------------
  # Ves Blog Module 
  # ------------------------------------------------------------------------
  # author:    Venustheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_IndexController extends Mage_Core_Controller_Front_Action
{  
	
	public function indexAction(){
		 
		$this->loadLayout();
		$this->renderLayout();
	 	// Zend_Debug::dump($this->getLayout()->getUpdate()->getHandles());
	}
}
?>