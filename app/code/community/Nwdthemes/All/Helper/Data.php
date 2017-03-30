<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_extensionsList = array(
        'Nwdthemes_Awblog' => 'Awblog',
        'Nwdthemes_Faqpage' => 'Faqpage',
        'Nwdthemes_Nwdajax' => 'Ajax Cart',
        'Nwdthemes_Powerbanners' => 'Powerbanners',
        'Nwdthemes_Productslider' => 'Product slider',
        'Nwdthemes_Revslider' => 'Slider Revolution',
        'Nwdthemes_Sociallogin' => 'Social login',
        'Nwdthemes_Testimonials' => 'Testimonials',
        'Nwdthemes_Ultratabs' => 'Ultratabs',
        'Nwdthemes_Wunderadmin' => 'Wunderadmin',
        'Nwdthemes_Wundermenu' => 'Wundermenu',
        'Nwdthemes_Wundertweets' => 'Wundertweets',
    );

    protected $_themesList = array(
        'Nwdthemes_Cartown' => 'Cartown',
    );

    /**
     * @return array
     */
    public function getExtensionsList()
    {
        return $this->_extensionsList;
    }

    /**
     * @return array
     */
    public function getThemesList()
    {
        return $this->_themesList;
    }

    public function getInstalledModules()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);
        return array_filter($modules, 'self::filter');
    }

    /**
     * @param $ext
     *
     * @return array
     */
    public function getExtConfigSectionName($ext)
    {
        $config = new Varien_Simplexml_Config(Mage::getModuleDir('etc', $ext) . '/config.xml');
        $sections = $config->getNode('default')->children();
        $result = array();
        foreach ( $sections as $s ) {
            $result[] = $s->getName();
        }
        return $result;
    }

	/**
	 * load config data from file
	 *
	 * @param $file
	 * @param $store
	 * @return string
	 */
	public function loadConfigFromFile($file, $store)
	{
		$settings = json_decode(base64_decode(file_get_contents($file)), true);
		if (is_array($settings) && $settings) {
			$loaded = array();
			if ( !empty($settings['Extensions']) ) {
				$loadedExt = $this->loadConfig($settings['Extensions'], $store);
				if ( !empty($loadedExt) ) {
					$extList = $this->getExtensionsList();
					foreach ( $loadedExt as $ext ) {
						$loaded[] = $extList[$ext];
					}
				}
			}
			if ( !empty($settings['Themes']) ) {
				$loadedTms = $this->loadConfig($settings['Themes'], $store);
				if ( !empty($loadedTms) ) {
					$tmsList = $this->getThemesList();
					foreach ( $loadedTms as $ext ) {
						$loaded[] = $tmsList[$ext];
					}
				}
			}
			Mage::getConfig()->reinit();

			$msg = 'No data was loaded or file was empty';
			if (!empty($loaded)) {
				$msg = 'Configuration for '.implode(', ', $loaded).' loaded successfully';
			}
		} else {
			$this->_nwdHelper->__('Incorrect file format');
		}

		return $msg;
	}

	/**
	 * Load data into config
	 *
	 * @param $items
	 * @param $store
	 *
	 * @return array
	 */
	public function loadConfig($items, $store)
	{
		$modules = $this->getInstalledModules();
		$scope = ($store ? 'stores' : 'default');
		$configModel = Mage::getConfig();
		$result = array();

		foreach ($items as $ext => $config) {

			if ( !in_array($ext, $modules) ) continue;

			$result[] = $ext;
			$i = each($config);
			$items1 = $i['value'];
			$section1 = $i['key'];
			foreach ( $items1 as $section2 => $items2 ) {
				foreach ( $items2 as $section3 => $value ) {
					$configModel->saveConfig($section1 .'/'. $section2 .'/'. $section3, $value, $scope, $store);
				}
			}
		}
		return $result;
	}

	/**
	 * Retrieve config value for store by path
	 *
	 * @param string $path
	 * @param string $section
	 * @param int $store
	 * @return mixed
	 */
	public function getCfg($path, $section = 'nwdall', $store = NULL)
	{
		if ($store == NULL) {
			$store = Mage::app()->getStore()->getId();
		}
		if (empty($path)) {
			$path = $section;
		} else {
			$path = $section . '/' . $path;
		}
		return Mage::getStoreConfig($path, $store);
	}

    /**
     * Filter NWDthemes modules
     *
     * @param   string
     * @return  boolean
     */
    
    public static function filter($val)
    {
        return strpos($val,'Nwdthemes_') !== false && $val != 'Nwdthemes_All';
    }

}
