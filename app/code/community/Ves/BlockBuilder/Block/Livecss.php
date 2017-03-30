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
class Ves_BlockBuilder_Block_Livecss extends Mage_Core_Block_Template 
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
		$this->_show = $this->getConfig("show", 0);
 		
		if(!$this->_show) return;
		
		/*End init meida files*/
		parent::__construct($attributes);

		if($this->hasData("template") && $this->getData("template")) {
            $my_template = $this->getData("template");
        }else {
            $my_template = "ves/blockbuilder/livecss_panel.phtml";
        }

        $this->setTemplate($my_template);

        /*Cache Block*/
        $enable_cache = Mage::getStoreConfig("ves_blockbuilder/ves_blockbuilder/enable_cache");

        if(!$enable_cache) {
            $cache_lifetime = null;
        } else {
            $cache_lifetime = Mage::getStoreConfig("ves_blockbuilder/ves_blockbuilder/cache_lifetime");
            $cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
        }

        $this->addData(array('cache_lifetime' => $cache_lifetime));

        $magento_version = Mage::getVersion();
        $magento_version = str_replace(".","", $magento_version);
        
        if((int)$magento_version >= 1900) {
            $this->addCacheTag(array(
                Mage_Core_Model_Store::CACHE_TAG,
                Mage_Cms_Model_Block::CACHE_TAG,
                Ves_BlockBuilder_Model_Block::CACHE_LIVECSS_TAG
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
           'VES_BLOCKBUILDER_LIVECSS_BLOCK',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

    public function _toHtml(){
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
	        $return = Mage::getStoreConfig("ves_livecss/$panel/$key");
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

    public function getThemePackage() {
    	$theme_name =  Mage::getDesign()->getTheme('frontend');
	    $package = Mage::getSingleton('core/design_package')->getPackageName();
	    return $package."/".$theme_name;
    }

    public function getThemePath() {
    	return Mage::getBaseDir('skin') . '/frontend/'.$this->getThemePackage();
    }

    public function getThemeBasePath() {
    	return Mage::getBaseDir('skin') . '/frontend/base/default';
    }

    public function getCustomizePath() {
    	return $this->getThemePath().'/css/customize/';
    }

    public function getThemeURL() {
		$base = Mage::getBaseUrl();
		$base = str_replace("/index.php","", $base);
		return $base. 'skin/frontend/'.$this->getThemePackage();
    }

    public function getThemeDefaultURL() {
		$base = Mage::getBaseUrl();
		$base = str_replace("/index.php","", $base);
		return $base. 'skin/frontend/base/default';
    }

    /**
	 * 
	 */
	public function getPattern(){
		$output = array();
		$backgroundImageURL = $this->getThemeURL().'/images/patterns/';
		$patterns_path = $this->getThemePath().'/images/patterns/';

		if(!is_dir( $patterns_path ) || !file_exists($patterns_path)) {
			$patterns_path = $this->getThemeBasePath().'/images/patterns/';
			$backgroundImageURL = $this->getThemeDefaultURL().'/images/patterns/';
		}

		if( is_dir( $patterns_path ) ) {
			$files = glob($patterns_path.'/*');
			foreach( $files as $dir ){
				if( preg_match("#.png|.jpg|.gif#", $dir)){
					$tmp = array();
					$tmp['file'] = str_replace("","",basename( $dir ) );
					$tmp['path'] = $backgroundImageURL.$tmp['file'];
					$output[] = $tmp;
				}
			}			
		}
		return $output;
	}

	/**
	 *
	 */
	public function renderEdtiorThemeForm( $theme_name = "" ){
		$collection = Mage::getModel("ves_blockbuilder/selector")->getCollection()
																 ->addFieldToFilter('status', 1);
		//$collection->getSelect()->order('position ASC');

		$output = array();
		if(0 < $collection->getSize()) {
			foreach($collection as $item) {
				$element_tab = $item->getElementTab();
				$element_group = $item->getElementGroup();

				if(!isset($output[$element_tab])) {
					$output[$element_tab] = array();
				}

				if(!isset($output[$element_tab][$element_group])) {
					$output[$element_tab][$element_group] = array();
					$output[$element_tab][$element_group]['header'] = $this->getGroupLabel( $element_group );
					$output[$element_tab][$element_group]['match'] = $element_group;
					$output[$element_tab][$element_group]['selector'] = array();
				}

				$selector_item = array("type" => $item->getElementType(),
										"label" => $item->getElementName(),
										"selector" => $item->getElementSelector(),
										"attrs" => $item->getElementAttrs()
										);

				$output[$element_tab][$element_group]['selector'][] = $selector_item;

			}
		}

		return $output;
	}
	public function getCustomizeFolderURL() {
        $theme_name =  Mage::getDesign()->getTheme('frontend');
        $package = Mage::getSingleton('core/design_package')->getPackageName();
        $group = $package."/".$theme_name;

        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) ."skin/frontend/".$group."/css/customize/";
    }

	public function getGroupLabel( $match = "") {
		$groups = Mage::helper("ves_blockbuilder")->getSelectorGroups();
		return isset($groups[$match])?$groups[$match]: $match;
	}

	
	
    public function getLiveEditLink() {
    	return $this->getUrl('blockbuilder/livecss/saveCustomize', array('_secure' => true));
    }



}
