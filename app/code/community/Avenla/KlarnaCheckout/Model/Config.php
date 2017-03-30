<?php
/**
 * This file is released under a custom license by Avenla Oy.
 * All rights reserved
 *
 * License and more information can be found at http://productdownloads.avenla.com/magento-modules/klarna-checkout/
 * For questions and support - klarna-support@avenla.com
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 * @copyright  Copyright (c) Avenla Oy
 * @link       http://www.avenla.fi
 */

/**
 * Avenla KlarnaCheckout
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 */
class Avenla_KlarnaCheckout_Model_Config extends Varien_Object
{
	const KLARNA_DOC_URL	  			= 'http://developers.klarna.com/';
	const ONLINE_GUI_URL	  			= 'https://merchants.klarna.com';

	const SERVER_MODE_LIVE 				= 'LIVE';
	const SERVER_MODE_DEMO 				= 'DEMO';

	const ANALYTICS_UNIVERSAL 			= 'universal';
	const ANALYTICS_CLASSIC 			= 'analytics';
	const LICENSE_URL         			= 'http://productdownloads.avenla.com/magento-modules/klarna-checkout/license';
	const DOCUMENTATION_URL     		= 'http://productdownloads.avenla.com/magento-modules/klarna-checkout#documentation';

	const API_TYPE_KCOV2        		= 2;
	const API_TYPE_KCOV3_UK     		= 3;
	const API_TYPE_KCOV3_US     		= 4;

	const KLARNA_WIDGET_SCRIPT  		= "https://cdn.klarna.com/1.0/code/client/all.js";
	const WIDGET_TYPE_KLARNA    		= 'klarna';
	const WIDGET_TYPE_PRODUCT   		= 'product';
	const WIDGET_TYPE_LIST      		= 'product_list';

	const CUSTOMER_TYPE_PERSON 			= 'person';
	const CUSTOMER_TYPE_ORGANIZATION	= 'organization';

	const B2B_DISABLED					= 0;
	const B2B_ENABLED 					= 1;
	const B2B_ENABLED_B2B_DEFAULT		= 2;

	private $store = null;

	public function setStore($storeId)
	{
		$this->store = $storeId;
	}

	public function getStore()
	{
		if($this->store != null)
			return $this->store;

		if(Mage::app()->getStore()->getId() == 0)
			return Mage::app()->getRequest()->getParam('store', 0);

		return $this->store;
	}

	/**
	 *  Return config var
	 *
	 *  @param    string $key
	 *  @param    string $default value for non-existing key
	 *  @return   mixed
	 */
	public function getConfigData($key, $default = false)
	{
		if (!$this->hasData($key) || $this->store != null){
			$value = Mage::getStoreConfig('payment/klarnaCheckout_payment/'.$key, $this->getStore());
			if (is_null($value) || false === $value) {
	    		$value = $default;
			}
			$this->setData($key, $value);
		}
		return $this->getData($key);
	}

	/**
	 * Get Klarna merchant eid
	 *
	 * @return  string
	 */
	public function getKlarnaEid()
	{
		return $this->getConfigData('merchantid');
	}

	/**
	 * Get Klarna merchant shared secret
	 *
	 * @return  string
	 */
	public function getKlarnaSharedSecret()
	{
		return Mage::helper('core')->decrypt($this->getConfigData('sharedsecret'));
	}

	/**
	 * Get terms url
	 *
	 * @return  string
	 */
	public function getTermsUri()
	{
		return Mage::getUrl($this->getConfigData('terms_url'));
	}

	/**
	 * Get B2B terms url
	 *
	 * @return  string
	 */
	public function getB2BTermsUrl()
	{
		if(!$this->allowB2BFlow())
			return false;

		return Mage::getUrl($this->getConfigData('terms_url_b2b'));
	}



	/**
	 * Get Klarna Checkout mode (LIVE OR BETA)
	 *
	 * @return  bool
	 */
	public function isLive()
	{
		if($this->getConfigData('server') == self::SERVER_MODE_LIVE)
			return true;

		return false;
	}

	/**
	 * Get module status
	 *
	 * @return  bool
	 */
	public function isActive()
	{
		return $this->getConfigData('active');
	}

	/**
	 * Get partial shipment activation mode
	 *
	 * @return  bool
	 */
	public function activatePartial()
	{
		return $this->getConfigData('activate_partial');
	}

	/**
	 * Get Google Analytics number or false if not found
	 *
	 * @return  mixed
	 */
	public function getGoogleAnalyticsNo()
	{
		$ga = $this->getConfigData('google_analytics');
		if(strlen($ga) < 1)
			return false;

		return $this->getConfigData('google_analytics');
	}

	/**
	 * Get Google Analytics account type
	 *
	 * @return  string
	 */
	public function getGoogleAnalyticsType()
	{
		return $this->getConfigData('google_analytics_type');
	}

	/**
	 * Get method title
	 *
	 * @return  string
	 */
	public function getTitle()
	{
		if(strlen($this->getConfigData('title')) > 0)
			return $this->getConfigData('title');

		return "Klarna Checkout";
	}

	/**
	 * Get link text
	 *
	 * @return  string
	 */
	public function getLinkText()
	{
		if(strlen($this->getConfigData('linktext')) > 0)
			return $this->getConfigData('linktext');

		return "Go to Klarna Checkout";
	}
	/**
	 * Get tax rate for credit memo adjustment
	 *
	 * @return  float
	 */
	public function getReturnTaxRate()
	{
		$taxClass =  $this->getConfigData('return_tax');

		$taxClasses  = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
		if(isset($taxClasses["value_".$taxClass]))
			return $taxClasses["value_".$taxClass];

		return 0;
	}

	/**
	 * Get license agreement status
	 *
	 * @return bool
	 */
	public function getLicenseAgreement()
	{
		return $this->getConfigData('license');
	}

	/**
	 * Get allowed shipping methods
	 *
	 * @return array
	 */
	public function getDisallowedShippingMethods()
	{
		$methods = array();
		if($this->getConfigData('disabled_shipping_methods'))
			$methods = explode(",", $this->getConfigData('disabled_shipping_methods'));

		return $methods;
	}

	/**
	 * Check if Part payment is enabled
	 *
	 * @return bool
	 */
	public function getPpWidgetSelection()
	{
		return $this->getConfigData('pp_widget');
	}

	/**
	 * Get part payment widget layout
	 *
	 * @return string
	 */
	public function getPpWidgetLayout()
	{
		return $this->getConfigData('pp_layout');
	}

	/**
	 * Get config for default Checkout
	 *
	 * @return bool
	 */
	public function hideDefaultCheckout()
	{
		return $this->getConfigData('default_checkout');
	}

	/**
	 * Get layout selection for KCO cart
	 *
	 * @return string
	 */
	public function getKcoLayout()
	{
		return $this->getConfigData('kco_layout');
	}

	/**
	 * Show sign for newsletter checkbox
	 *
	 * @return bool
	 */
	public function showNewsletter()
	{
		return $this->getConfigData('show_newsletter');
	}

	/**
	 * Show gift message form on cart page
	 *
	 * @return bool
	 */
	public function showGiftMessage()
	{
		return $this->getConfigData('show_giftmessage');
	}

	/**
	 *  Debug logging
	 *
	 *  @return bool
	 */
	public function debuglog()
	{
		return $this->getConfigData('debug_log');
	}

	/**
	 *  Use custom colors in KCO
	 *
	 *  @return bool
	 */
	public function useCustomColors()
	{
		return $this->getConfigData('custom_colors');
	}

	/**
	 *  Get button color
	 *
	 *  @return string
	 */
	public function getButtonColor()
	{
		return $this->getConfigData('color_button');
	}

	/**
	 *  Get button text color
	 *
	 *  @return string
	 */
	public function getButtonTextColor()
	{
		return $this->getConfigData('color_button_text');
	}

	/**
	 *  Get checkbox color
	 *
	 *  @return string
	 */
	public function getCheckboxColor()
	{
		return $this->getConfigData('color_checkbox');
	}

	/**
	 *  Get checkbox checkmark color
	 *
	 *  @return string
	 */
	public function getCheckboxCheckmarkColor()
	{
		return $this->getConfigData('color_checkbox_checkmark');
	}

	/**
	 *  Get header color
	 *
	 *  @return string
	 */
	public function getHeaderColor()
	{
		return $this->getConfigData('color_header');
	}

	/**
	 *  Get link color
	 *
	 *  @return string
	 */
	public function getLinkColor()
	{
		return $this->getConfigData('color_link');
	}

	/**
	 *  Get API version
	 *
	 *  @return int
	 */
	public function getApiVersion()
	{
		return $this->getConfigData('api');
	}

	/**
	 *  Allow separate shipping address
	 *
	 *  @return bool
	 */
	public function allowSeparateShippingAddress()
	{
		return (bool)$this->getConfigData('allow_separate_shipping');
	}

	/**
	 * Get default country from store config
	 *
	 * @return  string
	 */
	public function getDefaultCountry()
	{
		return Mage::getStoreConfig('general/country/default', $this->getStore());
	}

	/**
	 *  Use additional checkbox in KCO
	 *
	 *  @return bool
	 */
	public function useAdditionalCheckbox()
	{
		return $this->getConfigData('additional_checkbox');
	}

	/**
	 *  Get additional checkbox link text
	 *
	 *  @return string
	 */
	public function getAdditionalCheckboxText()
	{
		return $this->getConfigData('additional_checkbox_text');
	}

	/**
	 *  Get additional checkbox default value
	 *
	 *  @return bool
	 */
	public function getAdditionalCheckboxChecked()
	{
		return (bool)$this->getConfigData('additional_checkbox_checked');
	}

	/**
	 *  Get additional checkbox required value
	 *
	 *  @return bool
	 */
	public function getAdditionalCheckboxRequired()
	{
		return (bool)$this->getConfigData('additional_checkbox_required');
	}

	/**
	 *  Allow B2B flow
	 *
	 *  @return bool
	 */
	public function allowB2BFlow()
	{
		return $this->getConfigData('b2b_flow');
	}

}