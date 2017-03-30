<?php
/*------------------------------------------------------------------------
 # VenusTheme Layer slider Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Block_List extends Mage_Core_Block_Template 
{
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_listDesc = array();
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_show = 0;
	protected $_theme = "";

	protected $_banner = null;

	protected $slider_code = null;

	protected $_banner_id = 0;
	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{	
		$this->convertAttributesToConfig($attributes);

		$this->_show = $this->getConfig("show");
 		
		if(!$this->_show) return;
		/*End init meida files*/
		parent::__construct($attributes);

        $this->slider_code = $this->getConfig("slider_code");

        $this->_banner_id = $this->getConfig("bannerId", 0);
		
		if($this->hasData("template") && $this->getData('template')) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/layerslider/default.phtml";
 		}
		$this->setTemplate($my_template);
		
		/*Cache Block*/
	      $enable_cache = $this->getConfig("enable_cache", 1 );
	      if(!$enable_cache) {
	        $cache_lifetime = null;
	      } else {
	        $cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
	        $cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
	      }

	      $this->addData(array('cache_lifetime' => $cache_lifetime));
	      $this->addCacheTag(array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        Ves_Layerslider_Model_Config::CACHE_BLOCK_TAG
	    ));
	}

	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'VES_LAYERSLIDER_BLOCK_LIST',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

    public function convertAttributesToConfig($attributes = array()) {
      if($attributes) {
        foreach($attributes as $key=>$val) {
            $this->setConfig($key, $val);
        }
      }
    }

	public function getSliderBanner() {
		return $this->_banner;
	}
	/**
     * Rendering block content
     *
     * @return string
     */
	function _toHtml() 
	{

        $this->_banner = null;

        if($this->slider_code) {
        	$this->_banner = Mage::getModel('ves_layerslider/banner')->getSliderByAlias($this->slider_code);
        }

        if(!$this->_banner) {
			$banner_id = $this->getConfig("bannerId");
			$banner_id = $banner_id?$banner_id:0;
			$this->_banner  = Mage::getModel('ves_layerslider/banner')->load( $banner_id );

		}

		$this->_show = $this->getConfig("show");
 		$banner  = $this->getSliderBanner();
		if(!$this->_show || empty($banner)) return;

		$is_active =  $banner->getData("is_active");

		if($is_active) {
			$banners = array();
			$setting = array();
			$params = $banner->getData("params");
			if (base64_decode($params, true)) {
				$params = unserialize(base64_decode($params) );
			} else {
				$params = unserialize($params);
			}

			$options = $banner->getData("options");
			$setting = unserialize($options);
			$setting['width'] = isset($setting['width'])?$setting['width']:1070;
			$setting['height'] = isset($setting['height'])?$setting['height']:460;

			if($params) {
				foreach($params as $key => $slider) {
					if(strpos($key, "slide-container-") !== false && $slider) {
						if(isset($slider['type']) && $slider['type'] == 'image' && $slider['src']) {
							$slider['src'] = Mage::helper("ves_layerslider")->getImage( $slider['src'] );
						}

						$banners[] = $slider;
						
					}
				}
				
				$setting['general'] = isset($params['bg'])?$params['bg']:array();

				if(isset($setting['general']['src']) && $setting['general']['src']) {
					$setting['general']['src'] = Mage::helper("ves_layerslider")->getImage( $setting['general']['src'] );
				}
				$setting['width'] = isset($params['ss']['width'])?(int)$params['ss']['width']:$setting['width'];
				$setting['height'] = isset($params['ss']['height'])?(int)$params['ss']['height']:$setting['height'];
			}

			$this->assign("sliderParams", $setting);
			$this->assign("setting", $setting);
			$this->assign("params", $params);
			$this->assign("banners", $banners);
		}
		return parent::_toHtml();
    }

    /**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = "", $panel='general_setting'){
	    $return = "";
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {

	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    } else {

	      if(isset($this->_config[$key])){
	        $return = $this->_config[$key];

	        if($return == "true") {
		        $return = 1;
		    } elseif($return == "false") {
		        $return = 0;
		    }
	      }else{
	        $return = Mage::getStoreConfig("ves_layerslider/$panel/$key");
	      }
	      if($return == "" && !$default) {
	        $return = $default;
	      }

	    }

	    return $return;
	}
    /**
     * overrde the value of the extension's configuration
     *
     * @return string
     */
    function setConfig($key, $value) {
		if($value == "true") {
	        $value =  1;
	    } elseif($value == "false") {
	        $value = 0;
	    }
    	if($value != "") {
	      	$this->_config[$key] = $value;
	    }
    	return $this;
    }

    

	public function renderBannerElements( $banners = array(), $options = array()) {
		$html = Mage::helper("ves_layerslider/slider")->renderBannerElements( $banners, $options );
		return $html;
	}

	public function getSliderThumbnail( $banners = array(), $options = array()) {
		$html = Mage::helper("ves_layerslider/slider")->getSliderThumbnail( $banners, $options );
		return $html;
	}

	public function getSliderMainimage( $banners = array(), $options = array()) {
		$html = Mage::helper("ves_layerslider/slider")->getSliderMainimage( $banners, $options );
		return $html;
	}
}
