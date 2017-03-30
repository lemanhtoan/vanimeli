<?php
class Ves_Base_Block_Widget_Toplinks extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		/*Cache Block*/
    	$cache_lifetime = 86400;

        $this->addData(array('cache_lifetime' => $cache_lifetime));
        $cache_key = "ves_base_widget_toplinks";

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
    	$cache_key = 'VES_BASE_WIDGET_TOPLINKS';
    	
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
		
		$block_name = $this->getConfig("block_name", "widget_tags_popular");
		$enable_myaccount = $this->getConfig("enable_myaccount", 1);
		$enable_wishlist = $this->getConfig("enable_wishlist", 1);
		$enable_cart = $this->getConfig("enable_cart", 1);
		$enable_checkout = $this->getConfig("enable_checkout", 1);
		$enable_register = $this->getConfig("enable_register", 1);
		$enable_login = $this->getConfig("enable_login", 1);
		$enable_logout = $this->getConfig("enable_logout", 1);

		$links = $this->getConfig('links', '');
		$custom_links = array();
		if($links) {
			$custom_links = unserialize(base64_decode($links));
	        if (is_array($custom_links)) {
	            unset($custom_links['__empty']);
	        }
		}
		
		$toplink_block = Mage::app()->getLayout()->getBlock('top.links');

		$checkout_cart_link = $toplink_block->getChild('checkout_cart_link');

		if($this->hasData("template")) {
			$template = $this->getData('template');
		} else {
			$template = $this->getConfig("block_template", "");
		}

		if($template) {
			$toplink_block->setTemplate($template);
		}
		/*Remove base links*/
		if(!$enable_cart) {
			$toplink_block->removeLinkByUrl(Mage::getUrl('checkout/cart'));
		} elseif( $checkout_cart_link ) {
			$checkout_cart_link->addCartLink();
		}

		if(!$enable_checkout) {
			$toplink_block->removeLinkByUrl(Mage::getUrl('checkout', array('_secure' => true)));
		} elseif( $checkout_cart_link ) {
			$checkout_cart_link->addCheckoutLink();
		}

		if(!$enable_myaccount) {
			$toplink_block->removeLinkByUrl(Mage::helper("customer")->getAccountUrl());
		}

		if(!$enable_wishlist) {
			$toplink_block->removeLinkByUrl(Mage::getUrl('wishlist'));
		}

		if(!$enable_register) {
			$toplink_block->removeLinkByUrl(Mage::helper("customer")->getRegisterUrl());
		} else {
			$toplink_block->addLink(Mage::helper("customer")->__("Register"), Mage::helper("customer")->getRegisterUrl(), Mage::helper("customer")->__("Register"), false, array(), 100, '', '');
		}

		if(!$enable_login) {
			$toplink_block->removeLinkByUrl(Mage::helper("customer")->getLoginUrl());
		}

		if(!$enable_logout) {
			$toplink_block->removeLinkByUrl(Mage::helper("customer")->getLogoutUrl());
		}

		/*Add custom links*/
		//$toplink_block->addLink("Custom Link", "http://venustheme.com", "Custom link title", false, array(), 5, 'class="custom_link_1"', 'target="_BLANK"', "before text", "after text");
		
		return $toplink_block->toHtml();
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