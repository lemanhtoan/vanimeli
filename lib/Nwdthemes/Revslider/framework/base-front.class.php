<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */

if( !defined( 'Nwdthemes_Revslider_Helper_Framework::ABSPATH') ) exit();

class RevSliderBaseFront extends RevSliderBase {		
	
	const ACTION_ENQUEUE_SCRIPTS = "wp_enqueue_scripts";
	
	/**
	 * 
	 * main constructor		 
	 */
	public function __construct($t){
		
		parent::__construct($t);
		
		Mage::helper('nwdrevslider/framework')->add_action('wp_enqueue_scripts', array('RevSliderFront', 'onAddScripts'));
	}	
	
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteBaseFrontClassRev extends RevSliderBaseFront {}
?>