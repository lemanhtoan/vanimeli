<?php
/*------------------------------------------------------------------------
 # Venus Block Builder - Header Assets Block 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_BlockBuilder_Block_Media extends Mage_Core_Block_Template 
{
    /**
     * @var string $_config
     * 
     * @access protected
     */
    protected $_config = '';

    protected $_page_settings = array();
    
    /**
     * Contructor
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        if($this->hasData("template") && $this->getData("template")) {
            $my_template = $this->getData("template");
        }else {
            $my_template = "ves/blockbuilder/page_head.phtml";
        }

        $this->setTemplate($my_template);
        
    }

    public function _toHtml(){
        $page_url_key = Mage::getSingleton('cms/page')->getIdentifier();
        $this->_page_settings = Mage::registry('ves_page_settings');
        $page_profile = Mage::registry("product_builder_profile");
       

        if($page_url_key && !$this->_page_settings ) {
            $page_builder = Mage::getModel("ves_blockbuilder/block")->getBlockByAlias($page_url_key, true);
            
            if($page_builder) {
                $this->_page_settings = $page_builder->getSettings();
                $this->_page_settings = unserialize($this->_page_settings);
                Mage::register('ves_page_settings', $this->_page_settings);
            }
        } elseif($page_profile) {
            if (Mage::registry('current_product') || Mage::registry('current_category')) {
                $this->_page_settings = $page_profile->getSettings();
                $this->_page_settings = unserialize($this->_page_settings);
                Mage::register('ves_page_settings', $this->_page_settings);
            }
        }

        if($this->_page_settings) {
            $custom_css = isset($this->_page_settings['custom_css'])?$this->_page_settings['custom_css']:"";
            $custom_js = isset($this->_page_settings['custom_js'])?$this->_page_settings['custom_js']:"";

            $this->assign("custom_css", $custom_css);
            $this->assign("custom_js", $custom_js);
            return parent::_toHtml();
        }
    }
    /**
     * get value of the extension's configuration
     *
     * @return string
     */
    function getConfig( $key, $default = "", $panel='ves_blockbuilder' ){

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
          }else{
            $return = Mage::getStoreConfig("ves_blockbuilder/$panel/$key");
          }
          if($return == "" && $default) {
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
}
