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
class Ves_BlockBuilder_Block_Product_View extends Mage_Catalog_Block_Product_View 
{
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
    /**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
    //protected function _prepareLayout() {

    	parent::__construct();

    	$_product = $this->getProduct();

        if($_product && $_product->getId() && $this->getConfig("show") && !Mage::registry('product_builder_profile')) {
        	$_product_type_id = $_product->getTypeId();
        	$layout_profile_id = 0;
            $current_store_id = Mage::app()->getStore()->getId();
            $load_product_id = false;
            $layout_profile = false;

        	switch ($_product_type_id) {
        		case 'configurable':
        			if($layout_mode = $this->getConfig("auto_layout_configurable", 0, "layout")) {
        				if($layout_mode == "auto") {
        					$layout_profile_id = $this->getConfig("layout_configurable_product", 0, "layout");
        				} elseif($layout_mode == "manual") {
                            $load_product_id = true;
                            
                        }
        				
        			}
        			
        			break;
        		case 'grouped':
        			if($layout_mode = $this->getConfig("auto_layout_grouped", 0, "layout")) {
        				if($layout_mode == "auto") {
        					$layout_profile_id = $this->getConfig("layout_grouped_product", 0, "layout");
        				} elseif($layout_mode == "manual") {
                            $load_product_id = true;
                            
                        }
        				
        			}
        			
        			break;
        		case 'virtual':
        			if($layout_mode = $this->getConfig("auto_layout_virtual", 0, "layout")) {
        				if($layout_mode == "auto") {
        					$layout_profile_id = $this->getConfig("layout_virtual_product", 0, "layout");
        				} elseif($layout_mode == "manual") {
                            $load_product_id = true;
                            
                        }
        				
        			}
        			break;
        		case 'simple':
        		default:
        			if($layout_mode = $this->getConfig("auto_layout_simple", 0, "layout")) {
        				if($layout_mode == "auto") {
        					$layout_profile_id = $this->getConfig("layout_simple_product", 0, "layout");
        				} elseif($layout_mode == "manual") {
                            $load_product_id = true;
                            
                        }
        				
        			};
        			break;
        	}

            

        	if($layout_profile_id) {
        		$layout_profile = Mage::getModel('ves_blockbuilder/block')->load( $layout_profile_id );

            } elseif($load_product_id) {
                $layout_profile = Mage::getModel('ves_blockbuilder/block')->getProfileByProduct( $_product->getId(), $current_store_id );
                $layout_profile_id = !$layout_profile?0:$layout_profile->getId();
            }

            if($layout_profile) {
        		$profile_shortcode = Mage::helper("ves_blockbuilder")->getShortCode("ves_blockbuilder/widget_product", (int)$layout_profile_id);

        		if(0 < (int)$layout_profile->getId() && Mage::getModel('ves_blockbuilder/block')->checkBlockProfileAvailable($layout_profile)) {
	        		$this->setProfileShortcode($profile_shortcode);
	        		$this->setLayoutProfile($layout_profile);
	        		Mage::register('product_builder_profile', $layout_profile);
	        		Mage::register('product_builder_shortcode', $profile_shortcode);
        		}
        		
        	}
        }
    }
    
	protected function _toHtml() {
		$route = Mage::app()->getRequest()->getRouteName();
		if(($shortcode = $this->getProfileShortcode()) && !Mage::registry('product_info_block') && Mage::registry('current_product') && $route == "catalog") {

            $enable_tags_tab = $this->getConfig("enable_tags_tab", 1);
            if($enable_tags_tab){
                $product_tags = Mage::app()->getLayout()->createBlock('tag/product_list')->setTitle($this->__('Product Tags'))->setBlockAlias('relatedproducts_tab')->setTemplate('tag/list.phtml');
                $this->append($product_tags,'producttags');
                $this->addToChildGroup('detailed_info', $product_tags);
            }

    		Mage::register('product_info_block', $this );

    		$product_layout_builder_html = Mage::helper("ves_blockbuilder")->runShortcode( $shortcode );

    		$this->setProductbuilderProfile( $product_layout_builder_html );
    		$this->setTemplate("ves/productbuilder/view/view.phtml");
    	}
		return parent::_toHtml();
	}
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	function getConfig( $key, $default = "", $panel='general' ){

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
	        $return = Mage::getStoreConfig("ves_productbuilder/$panel/$key");
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


	public function getImageUrl($image = "") {
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
       
        if (file_exists($_imageUrl)){
            return Mage::getBaseUrl("media").$image;
        }
        return false;
	}
}
