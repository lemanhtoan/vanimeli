<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Model_System_Config_Source_Revslider
{
	public function __construct() {
		global $wpdb;
		$wpdb = Mage::helper('nwdrevslider/query');
        spl_autoload_register( array(Mage::helper('nwdrevslider'), 'loadRevClasses'), true, true );
	}
	
	public function toOptionArray()
	{
		new RevSliderAdmin();
		$slider = new RevSlider();
		$arrSliders = $slider->getArrSliders();
		$options = array();
		foreach ( $arrSliders as $item ) {
			$options[] = array('value' => $item->getAlias(), 'label' => $item->getAlias());
		}
		return $options;
	}
}
