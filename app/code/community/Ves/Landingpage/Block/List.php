<?php

class Ves_Landingpage_Block_List extends Mage_Core_Block_Template {

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

    public function __construct($attributes = array()) {

        $this->convertAttributesToConfig($attributes);

        $this->_show = $this->getConfig("show");

        if(!$this->_show) return;

        parent::__construct();

        $config = $this->_config;
        if( !$this->_show || !$this->getConfig('show') ) return;

        if($this->hasData("template") && $this->getData('template')) {
            $my_template = $this->getData("template");
        }else{
            $my_template = "ves/landingpage/default.phtml";
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
            Ves_Landingpage_Model_Config::CACHE_BLOCK_TAG
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
           'VES_LANDINGPAGE_BLOCK_LIST',
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

    protected function _toHtml() {
        $this->_show = $this->getConfig("show");

        if(!$this->_show) return;

        $logo = $this->getConfig('logo');
        if($logo) {
            if(strpos($logo, "http://") === false && strpos($logo, "https://") === false) {
                $logo = Mage::getBaseUrl('media').'ves_landingpage/'.$logo;
            }
        } else {
            $logo = "";
        }
        $image = $this->getConfig('image');
        if($image) {
            if(strpos($image, "http://") === false && strpos($image, "https://") === false) {
                $image = Mage::getBaseUrl('media').'ves_landingpage/'.$image;
            }
        } else {
            $image = "";
        }


        $this->assign( 'sliders', $this->getSlider());
        $this->assign( "image", $image );
        $this->assign( "logo", $logo );
        $this->assign( "videoid", $this->getConfig('video_id') );
        $this->assign( "interval", $this->getConfig('interval'));
        $this->assign( "loop", $this->getConfig('loop'));
        $this->assign( "autoPlay", $this->getConfig('auto_play'));
        $this->assign( "showControls", $this->getConfig('show_controls'));
        $this->assign( "mute", $this->getConfig('mute'));
        $this->assign( "link_logo", $this->getConfig('link_logo'));
        $this->assign('config', $config);

        return parent::_toHtml();
    }

   /**
     * List Tabs
     *
     * @return Colection
     */
    function getSlider(){
        $collection = Mage::getModel('ves_landingpage/slider')->getCollection()
        ->addFieldToFilter('status', array('eq' => 1))
        ->load();
        $this->setCollection($collection);
        return $collection;
    }

    /**
     * get value of the extension's configuration
     *
     * @return string
     */
    public function getConfig( $key, $panel='ves_landingpage', $default = ""){
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
            $return = Mage::getStoreConfig("ves_landingpage/$panel/$key");
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
  
    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
    		$text = ($is_striped==true)?strip_tags($text):$text;
    		if(strlen($text) <= $length){
    			return $text;
    		}
    		$text = substr($text,0,$length);
    		$pos_space = strrpos($text,' ');
    		return substr($text,0,$pos_space).$replacer;
	}

}
