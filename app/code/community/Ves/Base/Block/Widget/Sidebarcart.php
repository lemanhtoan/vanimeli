<?php
class Ves_Base_Block_Widget_Sidebarcart extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		/*Cache Block*/
    	$cache_lifetime = 86400;

        $this->addData(array('cache_lifetime' => $cache_lifetime));
        $cache_key = "ves_base_widget_sidebarcart";

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
    	$cache_key = 'VES_BASE_WIDGET_SIDEBARCART';
    	
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
		
		$block_name = $this->getConfig("block_name", "widget_cart_sidebar");

		$template = $this->getConfig("block_template", "ves/base/sidebar_cart.phtml");
		$sidebar_template = $this->getConfig("sidebar_template", "checkout/cart/minicart/items.phtml");
		$default_template = $this->getConfig("default_template", "checkout/cart/minicart/default.phtml");
		$simple_template = $this->getConfig("simple_template", "checkout/cart/minicart/default.phtml");
		$grouped_template = $this->getConfig("grouped_template", "checkout/cart/minicart/default.phtml");
		$configurable_template = $this->getConfig("configurable_template", "checkout/cart/minicart/default.phtml");


		$block_links1  = Mage::app()->getLayout()->createBlock('core/text_list','cart_sidebar.cart_promotion', array("module"=>"checkout", "translate"=>"label"));

		$block_links2  = Mage::app()->getLayout()->createBlock('core/text_list','cart_sidebar.extra_actions', array("module"=>"checkout", "translate"=>"label"));

		$cart_sidebar_block = Mage::app()->getLayout()->createBlock('checkout/cart_sidebar', "minicart_content")
											->setTemplate($sidebar_template)
											->addItemRender(
									             'default', 
									             'checkout/cart_item_renderer',
									             $default_template
									         )
											->addItemRender(
									             'simple',
									             'checkout/cart_item_renderer',
									             $simple_template
									         )
											->addItemRender(
									             'grouped',
									             'checkout/cart_item_renderer_grouped',
									             $grouped_template
									         )
											->addItemRender(
									             'configurable', 
									             'checkout/cart_item_renderer_configurable',
									             $configurable_template
									         );

		$cart_sidebar_block->setChild("cart_promotion", $block_links1);
		$cart_sidebar_block->setChild("extra_actions", $block_links2);

		$minicart_block = Mage::app()->getLayout()->createBlock('checkout/cart_minicart', $block_name)->setTemplate($template);
		$minicart_block->setChild("minicart_content", $cart_sidebar_block);

		return $minicart_block->toHtml();
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