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
class Avenla_KlarnaCheckout_Block_Adminhtml_System_Config_Fieldset_Info extends Mage_Adminhtml_Block_Abstract
	implements Varien_Data_Form_Element_Renderer_Interface
{
	protected 	$_template = 'KCO/system/config/fieldset/info.phtml';

	/**
	 * Render fieldset html
	 *
	 * @param   Varien_Data_Form_Element_Abstract Element
	 * @return  string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$this->assign('logoSrc', Mage::helper("klarnaCheckout")->getLogoSrc());
		$this->assign('apiLink', Avenla_KlarnaCheckout_Model_Config::KLARNA_DOC_URL);
		$this->assign('documentationUrl', Avenla_KlarnaCheckout_Model_Config::DOCUMENTATION_URL);

		return $this->toHtml();
	}

	/**
	 * Check store configuration
	 *
	 * @return  array
	 */
	public function getAlerts()
	{
		$alerts = array();
		$kco = Mage::helper('klarnaCheckout')->getKco();

		if(!Mage::helper("klarnaCheckout")->getConnectionStatus($kco))
			$alerts[] = "Connection to Klarna failed, please check your eid/shared secret and store settings.";

		if(Mage::getStoreConfig('tax/calculation/discount_tax') != 1)
			$alerts[] = "Discount is applied before taxes, this may cause different price on Klarna Checkout. Please check store tax configation.";

		if(Mage::getStoreConfig('tax/calculation/price_includes_tax') != 1)
			$alerts[] = "Catalog prices are set excluding tax, this may result in different prices in Checkout.";

		if(!Mage::getModel('klarnaCheckout/config')->getLicenseAgreement())
			$alerts[] = "By accepting the license agreement and filling in your contact information you can use Klarna Checkout module for free.";

		return $alerts;
	}
}