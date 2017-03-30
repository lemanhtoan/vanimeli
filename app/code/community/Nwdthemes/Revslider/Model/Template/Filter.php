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

class Nwdthemes_Revslider_Model_Template_Filter extends Mage_Widget_Model_Template_Filter {

	/**
	 * Directive for revslider shortcode
	 */

    public function revsliderDirective($construction) {
		if ( Mage::helper('nwdall')->getCfg('general/enabled', 'nwdrevslider_config') ) {
			$params = $this->_getIncludeParameters($construction[2]);
			$alias = isset($params['alias']) ? $params['alias'] : '';
			$sliderBlock = Mage::app()->getLayout()->createBlock('nwdrevslider/revslider', 'revslider.' . $alias);
			$sliderBlock->setData('alias', $alias);
			$output = $sliderBlock->toHtml();
		} else {
			$output = '';
		}
		return $output;
    }

}
