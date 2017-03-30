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

class Nwdthemes_Revslider_Helper_Data extends Mage_Core_Helper_Abstract {

    public static $_GET = array();
    public static $_REQUEST = array();

	/**
	 *	Constructor
	 */

	public function __construct() {
        self::$_REQUEST = Mage::app()->getRequest()->getParams();
	}

    /**
     *  Set page for get imitation
     *
     *  @param  string  Page
     */

    public static function setPage($page = '') {
        self::$_GET['page'] = $page;
    }
	
    /**
     * This function can autoloads classes
     *
     * @param string $class
     */

    public static function loadRevClasses($class) {
		switch ($class) {
			case 'UniteFunctionsRev' :	$class = 'RevSliderFunctions'; break;
			case 'RevSlider' : 			$class = 'RevSliderSlider'; break;
			case 'RevSlide' : 			$class = 'RevSliderSlide'; break;
		}
		switch ($class) {
			case 'Rev_addon_Admin' :	    $classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/addon-admin.class.php'; break;
			case 'RevSliderEventsManager' :	$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/em-integration.class.php'; break;
			case 'RevSliderCssParser' :		$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/cssparser.class.php'; break;
			case 'RevSliderWooCommerce' :	$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/woocommerce.class.php'; break;
			case 'RevSliderAdmin' : 		$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/admin/revslider-admin.class.php'; break;
			case 'RevSliderFront' :			$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/public/revslider-front.class.php'; break;
			case 'RevSliderFacebook' :
			case 'RevSliderTwitter' :
			case 'RevSliderTwitterApi' :
			case 'RevSliderInstagram' :
			case 'RevSliderFlickr' :
			case 'RevSliderYoutube' :
			case 'RevSliderVimeo' :			$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/external-sources.class.php'; break;
			default:
				if (preg_match( '#^RevSlider#', $class)) {
					$className = str_replace(array('RevSlider', 'WP'), array('', 'Wordpress'), $class);
					preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $className, $matches);
					$ret = $matches[0];
					foreach ($ret as &$match) {
						$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
					}
					$className = implode('-', $ret);
					$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/' . $className . '.class.php';
					if ( ! file_exists($classFile)) {
						$classFile = Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/' . $className . '.class.php';
					}
					if ( ! file_exists($classFile)) {
						unset($classFile);
					}
				}
			break;
		}
		if (isset($classFile)) {
			require_once($classFile);
		}
    }

	/**
	 * Get store options for multiselect
	 *
	 * @return array Array of store options
	 */

	public function getStoreOptions() {
		$storeValues = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
		$storeValues = $this->_makeFlatStoreOptions($storeValues);
		return $storeValues;
	}

	/**
	 * Make flat store options
	 *
	 * @param array $storeValues Store values tree array
	 * @retrun array Flat store values array
	 */

	private function _makeFlatStoreOptions($storeValues) {
		$arrStoreValues = array();
		foreach ($storeValues as $_storeValue) {
			if ( ! is_array($_storeValue['value']) ) {
				$arrStoreValues[] = $_storeValue;
			} else {
				$arrStoreValues[] = array(
					'label'	=> $_storeValue['label'],
					'value' => 'option_disabled'
				);
				$_arrSubStoreValues = $this->_makeFlatStoreOptions($_storeValue['value']);
				foreach ($_arrSubStoreValues as $_subStoreValue) {
					$arrStoreValues[] = $_subStoreValue;
				}
			}
		}
		return $arrStoreValues;
	}

}
