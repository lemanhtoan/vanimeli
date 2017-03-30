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

class Nwdthemes_Revslider_Helper_Curl extends Mage_Core_Helper_Abstract {

    /**
     *  Check if Curl available
     *
     *  @return boolean
     */

    public function test() {
        $test = function_exists('curl_version');
		return $test;
    }

    /**
     *  Do request
     *
     *  @param  string  url
     *  @return array
     */

    public function request($url) {
        $result = Mage::helper('nwdrevslider/framework')->wp_remote_post($url);
        return $result;
    }

}