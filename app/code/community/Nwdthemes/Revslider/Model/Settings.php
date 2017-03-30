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

class Nwdthemes_Revslider_Model_Settings extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('nwdrevslider/settings');
    }

	/**
	 * Gets settings value by name
	 *
	 * @param string $handle
	 * @param string $default
	 * @return variable
	 */

	public function getSettingsValue($handle, $default = '') {
		$settings = $this->getCollection()->getFirstItem();
		$arrSettings = unserialize($settings->getData('general'));
		return isset($arrSettings[$handle]) ? $arrSettings[$handle] : $default;
	}

}
