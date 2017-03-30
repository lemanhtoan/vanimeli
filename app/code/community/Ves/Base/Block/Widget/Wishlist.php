<?php
class Ves_Base_Block_Widget_Wishlist extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		/*Cache Block*/
    	$cache_lifetime = 86400;

        $this->addData(array('cache_lifetime' => $cache_lifetime));
        $cache_key = "ves_base_widget_wishlist";

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
    	$cache_key = 'VES_BASE_WIDGET_WISHLIST';
    	
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
		
		$block_name = $this->getConfig("block_name", "widget_wishlist_sidebar");
		$template = $this->getConfig("block_template", "wishlist/sidebar.phtml");

		$html = Mage::app()->getLayout()->createBlock('wishlist/customer_sidebar', $block_name)
											->setTemplate($template)
											->toHtml();
		return $html;
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