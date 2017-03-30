<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Block_Adminhtml_Navigation_Editor extends Mage_Adminhtml_Block_Template {

	/**
	 * Constructor
	 */

	public function __construct() {

		parent::__construct();

		$nav = new RevSliderNavigation();

		$navigation = intval($this->getRequest()->getParam('navigation', 0));

		$navigs = $nav->get_all_navigations();

		$rsopr = new RevSliderOperations();


		$font_families = $rsopr->getArrFontFamilys();
		
		$this->assign(get_defined_vars());
	}
}
