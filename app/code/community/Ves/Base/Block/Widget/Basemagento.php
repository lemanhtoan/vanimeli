<?php
class Ves_Base_Block_Widget_Basemagento extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		/*Cache Block*/
		$block_name = $this->getBlock("block_name", "");
    	$cache_lifetime = 86400;

        $this->addData(array('cache_lifetime' => $cache_lifetime));
        $cache_key = "ves_base_widget_basemagento_".$block_name;

        $this->addCacheTag(array(
	        Mage_Core_Model_Store::CACHE_TAG,
	        Mage_Cms_Model_Block::CACHE_TAG,
	        $cache_key
	    ));

        /*End Cache Block*/
	}

	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
    	$block_name = $this->getBlock("block_name", "");
    	$cache_key = 'VES_BASE_WIDGET_BASEMAGENTO_BLOCK_'.ucwords($block_name);
    	
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


	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$core_block = $this->getMagentoBlock();
		$custom_template = $this->getConfig("custom_template", "");
		if($core_block) {
			if($custom_template) {
				$core_block->setTemplate($custom_template);
			}
			return $core_block->toHtml();
		}
		return;
	}

	protected function _prepareLayout() {
        $block_name = $this->getConfig("block_name", "");
        $custom_block_name = $this->getConfig("custom_block_name", "");
        if($custom_block_name) {
        	$block_name = $custom_block_name;
        }
		$core_block = null;
		if($block_name) {
			$core_block = $this->getLayout()->getBlock($block_name);
		}
		$this->setMagentoBlock($core_block);

        return parent::_prepareLayout();
    }
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = ""){
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {

	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    }
	    return $default;
	}
}