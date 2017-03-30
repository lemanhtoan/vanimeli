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
class Avenla_KlarnaCheckout_Block_Catalog_Product_Price extends Mage_Bundle_Block_Catalog_Product_Price
{
	private $config;

	protected function _toHtml()
	{
		$html = parent::_toHtml();
		if($kco = Mage::helper('klarnaCheckout')->getKco()){
			$this->config = $kco->getConfig();

			if(!$this->config->getPpWidgetSelection() || $this->config->getPpWidgetSelection() == "no")
				return $html;

			if(($this->getRequest()->getControllerName() == 'product' && $this->getLayout()->getBlock('klarnaCheckout_price')) ||
				($this->getRequest()->getControllerName() == "category" && $this->config->getPpWidgetSelection() != Avenla_KlarnaCheckout_Model_Config::WIDGET_TYPE_LIST))
				return $html;

			$data = $this->getWidgetData($this->config->getPpWidgetSelection() != Avenla_KlarnaCheckout_Model_Config::WIDGET_TYPE_KLARNA);

			$html .= $this->getLayout()->createBlock('core/template', 'klarnaCheckout_price')
				->setWidgetData($data)
				->setTemplate('KCO/catalog/product/price.phtml')->toHtml();
        }

        return $html;
    }

	/**
	 *  Get widget data
	 *
	 *  @return Varien_Object|false
	 */
	public function getWidgetData($custom = false)
	{
		$price = $this->_getPrice();

		if($price < 0.1)
			return false;

		$data = new Varien_Object();

		if($custom){
			$data->setMonthlyPrice(Mage::getModel('klarnaCheckout/api')->getMonthlyPrice($price));
		}
		else{
			$data->setWidth(210);
			$data->setHeight(70);
			$data->setEid($this->config->getKlarnaEid());
			$data->setLocale(Mage::app()->getLocale()->getLocaleCode());
			$data->setPrice($price);
			$data->setLayout($this->config->getPpWidgetLayout());
		}

		return $data;
	}

	/**
	 * Get product price
	 *
	 * @return float
	 */
	private function _getPrice()
	{
		if($this->getDisplayMinimalPrice()){
			$price = $this->getProduct()->getMinimalPrice();
		}
		else{
			$price = $this->getProduct()->getFinalPrice();
		}

		$c = Mage::app()->getStore()->getCurrentCurrencyCode();
		$bc = Mage::app()->getStore()->getBaseCurrencyCode();
		$rate = 1;

		if ($bc != $c) {
			$currency = Mage::getModel('directory/currency');
			$currency->load($bc);
			$rate = $currency->getRate($c);
		}

		return $this->helper('tax')->getPrice(
			$this->getProduct(),
			$price,
			true
		) * $rate;
	}
}