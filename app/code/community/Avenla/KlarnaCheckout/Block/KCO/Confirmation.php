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
class Avenla_KlarnaCheckout_Block_KCO_Confirmation extends Mage_Core_Block_Template
{
	protected function _toHtml()
	{
    	return $this->getKcoFrame();
	}

	/**
	 *  Return Klarna Checkout confirmation page
	 *
	 *  @return	  string
	 */
	private function getKcoFrame()
	{
		$result = "";
		if($analyticsData = $this->getAnalyticsData())
			$result .= $this->getAnalyticsCode($analyticsData);

		$result .= $this->getKlarnaSnippet();
		$linkToStore = '<div class="buttons-set"><button type="button" class="button"
			title="'.  $this->__('Continue Shopping') .'" onclick="window.location=\''. $this->getUrl() .'\'">
			<span><span>'. $this->__('Continue Shopping') .'</span></span></button></div>';

		$result .= $linkToStore;

		return $result;
	}

	/**
	 *  Get Google Analytics Ecommerce tracking code
	 *
	 *  @param      Klarna_Checkout_Order $ko
	 *  @return     string
	 */
	private function getAnalyticsCode($data)
    {
		$type = Mage::getModel('klarnaCheckout/config')->getGoogleAnalyticsType();
		if($type == Avenla_KlarnaCheckout_Model_Config::ANALYTICS_UNIVERSAL)
			return $this->getUniversalAnalyticsCode($data);

        return $this->getClassicAnalyticsCode($data);
	}

	/**
	 *  Get classic Google Analytics Ecommerce tracking code
	 *
	 *	@param		Varien_Object $data
	 *  @return		string
	 */
	private function getClassicAnalyticsCode($data)
	{
		$gc = '<script type="text/javascript">';
		$gc .= "//<![CDATA[\n";
		$gc .= 'var _gaq = _gaq || [];';
		$gc .= '_gaq.push(["_setAccount", "' . Mage::getModel('klarnaCheckout/config')->getGoogleAnalyticsNo() . '"]);';

		$gc .= sprintf("_gaq.push(['_addTrans', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);",
			$data->getOrderId(),
			Mage::app()->getStore()->getName(),
			$data->getTotalInclTax(),
			$data->getTotalTaxAmount(),
			$data->getShippingFee(),
			$data->getBillingCity(),
			null,
			$data->getBillingCountry()
		);

		foreach ($data->getItems() as $p){
			$gc .= sprintf("_gaq.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
				$data->getOrderId(),
				$p->getReference(),
				$p->getName(),
				null,
				$p->getUnitPrice(),
				$p->getQty
			);
		}

		$gc .= '_gaq.push(["_set", "currencyCode", "'. $data->getCurrency() .'"]); ';
		$gc .= '_gaq.push(["_trackTrans"]);';
		$gc .= '(function() { ';
		$gc .= 'var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true; ';
		$gc .= 'ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";';
		$gc .= 'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);';
		$gc .= ' })();';
		$gc .= '//]]>' . "\n";
		$gc .= '</script>';

		return $gc;
	}

	/**
	 *  Get Universal Google Analytics Ecommerce tracking code
	 *
	 *  @param      Varien_Object $data
	 *  @return     string
	 */
	public function getUniversalAnalyticsCode($data)
	{
		$gc = '<script type="text/javascript">';
		$gc .= "//<![CDATA[\n";
		$gc .= "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){";
		$gc .= "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),";
		$gc .= "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)";
		$gc .= "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";
		$gc .= "ga('create', '" . Mage::getModel('klarnaCheckout/config')->getGoogleAnalyticsNo() . "', 'auto');";
		$gc .= "ga('require', 'ecommerce');";
		$gc .= sprintf("ga('ecommerce:addTransaction', {
				'id': '%s',
				'affiliation': '%s',
				'revenue': '%s',
				'tax': '%s',
				'shipping': '%s',
				'currency': '%s'
			});",
			$data->getOrderId(),
			Mage::app()->getStore()->getName(),
			$data->getTotalInclTax(),
			$data->getTotalTaxAmount(),
			$data->getShippingFee(),
			$data->getCurrency()
		);

		foreach ($data->getItems() as $p){
			$gc .= sprintf("ga('ecommerce:addItem', {
					'id': '%s',
					'sku': '%s',
					'name': '%s',
					'category': '%s',
					'price': '%s',
					'quantity': '%s'
				});",
				$data->getOrderId(),
				$p->getReference(),
				$p->getName(),
				null,
				$p->getPrice(),
				$p->getQty()
			);
		}
		$gc .= "ga('ecommerce:send');";

		$gc .= '//]]>' . "\n";
		$gc .= '</script>';

		return $gc;
	}
}