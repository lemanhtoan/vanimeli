<?php
class Ves_BlockBuilder_Block_Widget_Product extends Ves_BlockBuilder_Block_List implements Mage_Widget_Block_Interface
{

	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{

		$this->_show = $this->getConfig("show");
		if(!$this->_show) return;
		/*End init meida files*/

        parent::__construct($attributes);

        $my_template = "";
        if($this->hasData("template") && $this->getData("template")) {
        	$my_template = $this->getData("template");
        } elseif(isset($attributes['template']) && $attributes['template']) {
            $my_template = $attributes['template'];
        } else {
 			$my_template = "ves/productbuilder/row.phtml";
 		}
        $this->setTemplate($my_template);

        /*Cache Block*/
        $enable_cache = $this->getConfig("enable_cache", 0 );
    	if(!$enable_cache) {
    		$cache_lifetime = null;
    	} else {
    		$cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
    		$cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
    	}

        $this->addData(array('cache_lifetime' => $cache_lifetime));
        $magento_version = Mage::getVersion();
        $magento_version = str_replace(".","", $magento_version);
        
        if((int)$magento_version >= 1900) {
            $this->addCacheTag(array(
    	        Mage_Core_Model_Store::CACHE_TAG,
    	        Mage_Cms_Model_Block::CACHE_TAG,
    	        Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG
    	    ));
        }
        /*End Cache Block*/
		
	}

	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'VES_BLOCKBUILDER_WIDGET_PRODUCT',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }


	protected function _prepareLayout() {

		$this->_show = $this->getConfig("show");
		if(!$this->_show) return;

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

        $settings = array();
        if($this->_banner) {
    		$params = $this->_banner->getParams();
        	$params = Zend_Json::decode($params);
            $block_widgets = $this->_banner->getWidgets();

            $settings = $this->_banner->getSettings();
            $settings = unserialize($settings);
            $this->setSettings($settings);

            $this->assign("block_id", $this->_banner->getAlias());
            $this->assign("settings", $settings);
            $this->assign("block_widgets", $block_widgets);
        	$this->assign("layouts", $params);
        	$this->assign("is_container", $this->_banner->getContainer());
        	$this->assign("class", $this->_banner->getPrefixClass());
        	$this->assign("show_title", $this->getData("show_title"));
        	$this->assign("heading", $this->_banner->getTitle());

            if(1 == $this->_banner->getContainer()) {
                $this->setTemplate("ves/productbuilder/row_container.phtml");
            }
        }
        
        
	}
	
    public function _toHtml() {
        $settings = $this->getSettings();

        if($settings && isset($settings['enable_wrapper']) && $settings['enable_wrapper'] == 1) {
            $wrapper_class = (isset($settings['select_wrapper_class'])?$settings['select_wrapper_class']." ":'');
            $wrapper_class .= (isset($settings['wrapper_class'])?$settings['wrapper_class']:'');
            if(isset($this->_banner) && $this->_banner) {
                $wrapper_class .= " ".$this->_banner->getAlias();
            }
            return '<div class="'.$wrapper_class.'">'.parent::_toHtml().'</div>';
        } else {
            return parent::_toHtml();
        }
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
		$current_theme_path .= "/ves/productbuilder/";

		$load_file_path = $current_theme_path.$filepath;
		
		if(file_exists($load_file_path)) {
			return $load_file_path;
		}
		return false;
	}
}