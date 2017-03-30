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

class Nwdthemes_Revslider_Block_Revslider extends Mage_Core_Block_Template {

	protected $_slider;
	protected $_content;

	protected function _construct() {
		parent::_construct();
		global $wpdb;
		global $revSliderVersion;
        spl_autoload_register( array(Mage::helper('nwdrevslider'), 'loadRevClasses'), true, true );
		$wpdb = Mage::helper('nwdrevslider/query');
		$revSliderVersion = RevSliderGlobals::SLIDER_REVISION;
		new RevSliderFront();
        Mage::helper('nwdrevslider/plugin')->loadPlugins();
		$this->setTemplate('nwdthemes/revslider/revslider.phtml');
	}

	protected function _renderSlider() {
		if ( is_null($this->_slider) ) {
			ob_start();
			$this->_slider = RevSliderOutput::putSlider($this->getData('alias'));
			$this->_content = ob_get_contents();
			ob_clean();
			ob_end_clean();
		}
	}

    /**
     *  Include scritps and styles
     */

    protected function addHeadIncludes() {
        $this->_renderSlider();
        Mage::helper('nwdrevslider/framework')->do_action('wp_enqueue_scripts');
        $content = '';
        foreach (Mage::helper('nwdrevslider/framework')->getFromRegister('styles') as $_handle => $_style) {
            if (strpos($_style, Mage::getDesign()->getSkinUrl('nwdthemes/revslider')) === false) {
                $content .= '<link rel="stylesheet" type="text/css" href="' . $_style . '" media="all" />' . "\n";
            }
        }
        foreach (Mage::helper('nwdrevslider/framework')->getFromRegister('scripts') as $_handle => $_script) {
            if (strpos($_script, Mage::getDesign()->getSkinUrl('nwdthemes/revslider')) === false) {
                $content .= '<script type="text/javascript" src="' . $_script . '"></script>' . "\n";
            }
        }
        return $content;
    }

	public function getCacheKeyInfo() {
		$this->_renderSlider();
		$key = parent::getCacheKeyInfo();
		$key[] = $this->getData('alias');
		$key[] = $this->_slider->getParam("disable_on_mobile", "off");
		$key[] = isset($_SERVER['HTTPS']);
		return $key;
	}

	public function renderSlider() {
		if ( Mage::helper('nwdall')->getCfg('general/enabled', 'nwdrevslider_config') ) {
			
			$this->_renderSlider();
	
			$custom_css = RevSliderOperations::getStaticCss();
			$custom_css = '<style type="text/css">' . RevSliderCssParser::compress_css($custom_css) . '</style>';

			$this->_content = $this->addHeadIncludes() . self::load_icon_fonts() . $custom_css . $this->_content;
			
			if(!empty($this->_slider)){
				// Do not output Slider if we are on mobile
				$disable_on_mobile = $this->_slider->getParam("disable_on_mobile","off");
				if($disable_on_mobile == 'on'){
					$mobile = (strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || Mage::helper('nwdrevslider/framework')->wp_is_mobile()) ? true : false;
					if($mobile) return false;
				}
	
				$show_alternate = $this->_slider->getParam("show_alternative_type","off");
	
				if($show_alternate == 'mobile' || $show_alternate == 'mobile-ie8'){
					if(strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || Mage::helper('nwdrevslider/framework')->wp_is_mobile()){
						$show_alternate_image = $this->_slider->getParam("show_alternate_image","");
						$this->_content = '<img class="tp-slider-alternative-image" src="'.$show_alternate_image.'" data-no-retina>';
					}
				}
			}
		}	

		return $this->_content;
	}

	/**
	 *	Add icon fonts
	 */

	public static function load_icon_fonts(){
		global $fa_icon_var,$pe_7s_var;
		$content = '';
		if($fa_icon_var) $content .= "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-fa-icon-css'  href='" . Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL . "public/assets/fonts/font-awesome/css/font-awesome.css' type='text/css' media='all' />";
		if($pe_7s_var) $content .= "<link rel='stylesheet' property='stylesheet' id='rs-icon-set-pe-7s-css'  href='" . Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL . "public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css' type='text/css' media='all' />";
		return $content;
	}	
	
}
