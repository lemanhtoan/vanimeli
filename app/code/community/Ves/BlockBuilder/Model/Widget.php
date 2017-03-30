<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */
class Ves_BlockBuilder_Model_Widget extends Mage_Core_Model_Abstract{

	var $widgets = array();
	/**
	 *
	 */	
	public function loadWidgetButtons(){

	 	if(Mage::helper("ves_blockbuilder")->checkModuleInstalled("Ves_Base")) {
	 		$this->widgets = Mage::getModel('ves_base/widget')	-> 	loadWidgetsEngines()
	 														 	->	getButtons();
	 	}

	 	return $this->widgets;
	}
}