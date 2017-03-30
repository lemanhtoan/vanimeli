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

class Nwdthemes_Revslider_Block_Adminhtml_Block_Template extends Mage_Adminhtml_Block_Template {

	private $_revSliderAdmin;

	/**
	 * Constructor
	 */

	public function __construct() {
		parent::__construct();
		$this->_revSliderAdmin = Mage::getSingleton('RevSliderAdmin');
	}

	/**
	 * Get settings values
	 *
	 * @param $key
	 */

	public function getSettings($key) {
		return $this->_revSliderAdmin->getSettings($key);
	}

	/**
	 * Get settings file path
	 *
	 * @param $name
	 */

	public function getSettingsFilePath($name) {
		return $this->_revSliderAdmin->getSettingsFilePath($name);
	}

	/**
	 * Get view url
	 *
	 * @param string $view view name
	 * @param string $params params for view
	 * @return string view url
	 */

	static public function getViewUrl($view, $params = '') {
		return RevSliderAdmin::getViewUrl($view, $params);
	}

	/**
	 * Store settings
	 *
	 * @param string $name
	 * @param array $settings
	 */

	public function storeSettings($name, $settings) {
		$this->_revSliderAdmin->storeSettings($name, $settings);
	}

}
