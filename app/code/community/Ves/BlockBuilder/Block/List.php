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
class Ves_BlockBuilder_Block_List extends Mage_Core_Block_Template 
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
	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{	
		$this->_show = $this->getConfig("show");
 		
		if(!$this->_show) return;
		/*End init meida files*/
		parent::__construct($attributes);

        $code = null;
        $block_id = 0;
        if (isset($attributes['code'])) {
            $code = $attributes['code'];
            $this->setConfig("code", $code );
        }

        if (isset($attributes['block_id'])) {
            $block_id = (int)$attributes['block_id'];
            $this->setConfig("block_id", $block_id );
        }
        if (isset($attributes['show_title'])) {
            $this->setConfig("show_title", (int)$attributes['show_title'] );
        }

        if (isset($attributes['block_type'])) {
            $this->setConfig("block_type", (int)$attributes['block_type'] );
        }

        $block_type = $this->getConfig("block_type", "block");
        $cache_tag = Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG;
        $my_template = "";

        if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }elseif($block_type == "page") {
        	$my_template = "ves/blockbuilder/default_page.phtml";
        	$cache_tag = Ves_BlockBuilder_Model_Block::CACHE_PAGE_TAG;
        } else {
        	$my_template = "ves/blockbuilder/default.phtml";
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
        $cache_key = Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG;

        if("page" == $this->getConfig("block_type", "block") ) {
        	$cache_key = Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG;
        }
        $magento_version = Mage::getVersion();
        $magento_version = str_replace(".","", $magento_version);
        
        if((int)$magento_version >= 1900) {
	        $this->addCacheTag(array(
		        Mage_Core_Model_Store::CACHE_TAG,
		        Mage_Cms_Model_Block::CACHE_TAG,
		        $cache_key
		    ));
	    }
        /*End Cache Block*/
	}
    
    public function convertAttributesToConfig($attributes = array()) {
    	if($attributes) {
    		foreach($attributes as $key=>$val) {
				$this->setConfig($key, $val);
			}
    	}
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
    	$cache_key = 'VES_BLOCKBUILDER_WIDGET_BUILDER';
    	if("page" == $this->getConfig("block_type", "block") ) {
    		$cache_key = 'VES_BLOCKBUILDER_WIDGET_PAGE';
    	}
        return array(
           $cache_key,
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

	public function _toHtml(){

		$code = null;
        $block_id = $this->getConfig("block_id");
		$block_id = $block_id?$block_id:0;
        $code = $this->getConfig('code');
        $this->_banner = null;
        if($block_id) {
			$this->_banner  = Mage::getModel('ves_blockbuilder/block')->load( $block_id );
		}

        if(!$this->_banner && $code) {
        	$this->_banner = Mage::getModel('ves_blockbuilder/block')->getBlockByAlias($code);
        }

        if($this->_banner && !Mage::getModel('ves_blockbuilder/block')->checkBlockProfileAvailable($this->_banner)) {
			$this->_banner = null;
		}

        if($this->_banner) {
    		$params = $this->_banner->getParams();
        	$params = Zend_Json::decode($params);
        	$this->assign("layouts", $params);
        	$this->assign("is_container", $this->_banner->getContainer());
        	$this->assign("class", $this->_banner->getPrefixClass());
        	$this->assign("show_title", $this->getConfig("show_title"));
        	$this->assign("heading", $this->_banner->getTitle());
    	}
    	return parent::_toHtml();
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


	public function renderWidgetShortcode( $shortcode = "") {
		if($shortcode) {
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			return $processor->filter($shortcode);
		}
		return;
	}

	public function getLayoutPath($filepath = "") {
		$current_theme_path = Mage::getSingleton('core/design_package')->getBaseDir(array('_area' => 'frontend', '_type'=>'template'));
		$current_theme_path .= "/ves/blockbuilder/";

		$load_file_path = $current_theme_path.$filepath;

		if(file_exists($load_file_path)) {
			return $load_file_path;
		}
		return false;
	}

	public function getImageUrl($image = "") {
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
       
        if (file_exists($_imageUrl)){
            return Mage::getBaseUrl("media").$image;
        }
        return false;
	}

	public function getRowStyle($row = array()) {
		$custom_css = array();
		
		if(isset($row['bgcolor']) && $row['bgcolor']) {
			$custom_css[] = 'background-color:'.$row['bgcolor'];
		}
		if(isset($row['bgimage']) && $row['bgimage']) {
			$row['bgimage'] = $this->getImageUrl( $row['bgimage'] );
			$custom_css[] = ($row['bgimage'])?'background-image:url('.$row['bgimage'].')':'';
		}
		if(isset($row['bgrepeat']) && $row['bgrepeat']) {
			$custom_css[] = 'background-repeat:'.$row['bgrepeat'];
		}
		if(isset($row['bgposition']) && $row['bgposition']) {
			$custom_css[] = 'background-position:'.$row['bgposition'];
		}
		if(isset($row['bgattachment']) && $row['bgattachment']) {
			$custom_css[] = 'background-attachment:'.$row['bgattachment'];
		}
		if(isset($row['padding']) && $row['padding']) {
			$custom_css[] = 'padding:'.$row['padding'];
		}
		if(isset($row['margin']) && $row['margin']) {
			$custom_css[] = 'margin:'.$row['margin'];
		}

		return implode(";", $custom_css);
	}

	public function getRowInnerStyle($row = array()) {
		$custom_css = array();
		
		if(isset($row['inbgcolor']) && $row['inbgcolor']) {
			$custom_css[] = 'background-color:'.$row['inbgcolor'];
		}
		if(isset($row['inbgimage']) && $row['inbgimage']) {
			$row['inbgimage'] = $this->getImageUrl( $row['inbgimage'] );
			$custom_css[] = ($row['inbgimage'])?'background-image:url('.$row['inbgimage'].')':'';
		}
		if(isset($row['inbgrepeat']) && $row['inbgrepeat']) {
			$custom_css[] = 'background-repeat:'.$row['inbgrepeat'];
		}
		if(isset($row['inbgposition']) && $row['inbgposition']) {
			$custom_css[] = 'background-position:'.$row['inbgposition'];
		}
		if(isset($row['inbgattachment']) && $row['inbgattachment']) {
			$custom_css[] = 'background-attachment:'.$row['inbgattachment'];
		}

		return implode(";", $custom_css);
	}

	public function getColStyle($col = array()) {
		$custom_col_css = array();

		if(isset($col['bgcolor']) && $col['bgcolor']) {
			$custom_col_css[] = 'background-color:'.$col['bgcolor'];
		}
		if(isset($col['bgimage']) && $col['bgimage']) {
			$col['bgimage'] = $this->getImageUrl( $col['bgimage'] );
			$custom_col_css[]= $col['bgimage']?'background-image:url('.$col['bgimage'].')':'';
		}
		if(isset($col['bgrepeat']) && $col['bgrepeat']) {
			$custom_col_css[] = 'background-repeat:'.$col['bgrepeat'];
		}
		if(isset($col['bgposition']) && $col['bgposition']) {
			$custom_col_css[] = 'background-position:'.$col['bgposition'];
		}
		if(isset($col['bgattachment']) && $col['bgattachment']) {
			$custom_col_css[] = 'background-attachment:'.$col['bgattachment'];
		}
		if(isset($col['padding']) && $col['padding']) {
			$custom_col_css[] = 'padding:'.$col['padding'];
		}
		if(isset($col['margin']) && $col['margin']) {
			$custom_col_css[] = 'margin:'.$col['margin'];
		}

		return implode(";", $custom_col_css);
	}

	public function getWidgetStyle($col = array()) {
		$custom_widget_css = array();

		if(isset($col['bgcolor']) && $col['bgcolor']) {
			$custom_widget_css[] = 'background-color:'.$col['bgcolor'];
		}
		if(isset($col['bgimage']) && $col['bgimage']) {
			$col['bgimage'] = $this->getImageUrl( $col['bgimage'] );
			$custom_widget_css[]= $col['bgimage']?'background-image:url('.$col['bgimage'].')':'';
		}
		if(isset($col['bgrepeat']) && $col['bgrepeat']) {
			$custom_widget_css[] = 'background-repeat:'.$col['bgrepeat'];
		}
		if(isset($col['bgposition']) && $col['bgposition']) {
			$custom_widget_css[] = 'background-position:'.$col['bgposition'];
		}
		if(isset($col['bgattachment']) && $col['bgattachment']) {
			$custom_widget_css[] = 'background-attachment:'.$col['bgattachment'];
		}
		if(isset($col['padding']) && $col['padding']) {
			$custom_widget_css[] = 'padding:'.$col['padding'];
		}
		if(isset($col['margin']) && $col['margin']) {
			$custom_widget_css[] = 'margin:'.$col['margin'];
		}
		
		return implode(";", $custom_widget_css);
	}

	public function getMobileDetect() {
		return Mage::helper("ves_blockbuilder/mobiledetect");
	}
	public function detectDeviceToShow($widget = array()){
        $display = true;
        $show_on_desktop = isset($widget['desktop'])?$widget['desktop']: true;
        $show_on_tablet = isset($widget['tablet'])?$widget['tablet']: true;
        $show_on_mobile = isset($widget['mobile'])?$widget['mobile']: true;

        if($this->getMobileDetect()->isMobile() && !$this->getMobileDetect()->isTablet()) { //If current are mobile devices
            if(!$show_on_mobile) {
                $display = false;
            }
        } elseif($this->getMobileDetect()->isTablet()) { //If current are mobile devices
            if(!$show_on_tablet) {
                $display = false;
            }
        } elseif(!$show_on_desktop) {
            $display = false;
        }

        return $display;
    }

    public function getAjaxGenerateUrl() {
    	if(Mage::app()->getStore()->isCurrentlySecure()){
    		return $this->getUrl('blockbuilder/ajaxwidget/gen', array('_secure' => true));
    	} else {
    		return $this->getUrl('blockbuilder/ajaxwidget/gen');
    	}
    	
    }
}
