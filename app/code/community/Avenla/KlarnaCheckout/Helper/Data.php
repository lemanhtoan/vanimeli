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
class Avenla_KlarnaCheckout_Helper_Data extends Mage_Core_Helper_Data
{
	/**
	 * Write in KCO log if debugging is enabled
	 *
	 * @param mixed  $message
	 * @param int $level
	 */
	public function log($message, $level = 7)
	{
		if(!Mage::getSingleton('klarnaCheckout/payment_KCO')->getConfig()->debuglog())
			return;

		$file = 'KCO.log';
		Mage::log($message, $level, $file);
	}

	/**
	 *  Write exception to log
	 *
	 *  @param Exception $e
	 */
	public function logException(Exception $e)
	{
		$this->log("\n" . $e->__toString(), Zend_Log::ERR);
		if($e instanceof Klarna_Checkout_ApiErrorException){
			$this->log($e->getMessage());
			$this->log($e->getPayload());
		}

		Mage::logException($e);
	}

	/**
	 *  Get KCO payment model
	 *
	 *  @return mixed
	 */
	public function getKco()
	{
		$version = Mage::getSingleton('klarnaCheckout/config')->getApiVersion();
		if($version == Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV2){
			return Mage::getModel('klarnaCheckout/payment_KCO');
		}
		elseif($version == Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV3_UK || $version == Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV3_US){
			return Mage::getModel('klarnaCheckout/payment_KCOv3');
		}

		return false;
	}

	/**
	 * Get confirmation url
	 *
	 * @return  string
	 */
	public function getConfirmationUri()
	{
		return rtrim(Mage::getUrl('klarnaCheckout/KCO/confirmation?' . Avenla_KlarnaCheckout_Model_Payment_Abstract::REQUEST_KLARNA_ORDER . '={checkout.order.id}'), "/");
	}

	/**
	 * Get push url
	 *
	 * @return  string
	 */
	public function getPushUri()
	{
		$storeId = Mage::app()->getStore()->getStoreId();
		return rtrim(Mage::getUrl('klarnaCheckout/KCO/push?storeid='.$storeId.'&' . Avenla_KlarnaCheckout_Model_Payment_Abstract::REQUEST_KLARNA_ORDER . '={checkout.order.id}'), "/");
	}

	/**
	 * Get validation url
	 *
	 * @return  string|false
	 */
	public function getValidationUri()
	{
		$uri = rtrim(Mage::getUrl('klarnaCheckout/KCO/validation?sid='.Mage::getSingleton('core/session')
			->getSessionId(), array('_forced_secure' => true)), "/");

		if(parse_url($uri, PHP_URL_SCHEME) == "https")
			return $uri;

		return false;
	}

	/**
	 * Get url of checkout page
	 *
	 * @return  string
	 */
	public function getCheckoutUri()
	{
		return rtrim(Mage::helper('checkout/url')->getCheckoutUrl(), "/");
	}

	/**
	 * Get url of cart page
	 *
	 * @return 	string
	 */
	public function getCartUri()
	{
		return Mage::getUrl('checkout/cart');
	}

	/**
	 * Get Klarna logo url
	 *
	 * @param 	int $width|88
	 * @param 	string $background|blue-black
	 * @return 	string
	 */
	public function getLogoSrc($width = 88, $background = "blue-black")
	{
		$country = $this->getLocale(Mage::getStoreConfig('general/country/default', Mage::app()->getStore()));
		$country = str_replace('-', '_', $country);
		$eid = Mage::getSingleton('klarnaCheckout/payment_KCO')->getConfig()->getKlarnaEid();

		return 'https://cdn.klarna.com/1.0/shared/image/generic/logo/'.$country.'/basic/'.$background.'.png?width='.$width.'&eid='. Mage::getModel('klarnaCheckout/config')->getKlarnaEid();
	}

	/**
	 * Send test query to Klarna to verify given merchant credentials
	 *
	 * @return  bool
	 */
	public function getConnectionStatus($kco, $quote = null)
	{
		try{
			$ko = $kco->getOrderModel()->dummyOrder($quote);

			if($ko == null)
				return false;

			$ko->fetch();
			return true;
		}
		catch (Exception $e) {
			$this->logException($e);
			return false;
		}
	}

	/**
	 *  Check if Klarna order was made in current store
	 *
	 *  @param  Klarna_Checkout_Order $ko
	 *  @return bool
	 */
	public function isOrderFromCurrentStore($ko)
	{
		$uri = $ko['merchant']['push_uri'];
		preg_match('/storeid=(.*?)&' . Avenla_KlarnaCheckout_Model_Payment_Abstract::REQUEST_KLARNA_ORDER . '/', $uri, $res);

		if($res[1] == Mage::app()->getStore()->getStoreId())
			return true;

		return false;
	}

	/**
	 * Get order shipping tax rate
	 *
	 * @param 	Mage_Sales_Model_Quote
	 * @return 	float $taxRate
	 */
	public function getShippingVatRate($quote)
	{
		$taxCalculationModel = Mage::getSingleton('tax/calculation');

		$request = $taxCalculationModel->getRateRequest(
			$quote->getShippingAddress(),
			$quote->getBillingAddress(),
			$quote->getCustomerTaxClassId(),
			$quote->getStore()
		);

		$shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $quote->getStore());
		$rate = $taxCalculationModel->getRate($request->setProductClassId($shippingTaxClass));

		return $rate;
	}

	/**
	 * Get locale code for purchase country
	 *
	 * @param 	string $country
	 * @return 	string
	 */
	public function getLocale($country)
	{
		switch($country){
			case 'SE':
				return 'sv-se';
			case 'NO':
				return 'nb-no';
			case 'DE':
				return 'de-de';
			case 'AT':
				return 'de-at';
			case 'GB':
				return 'en-gb';
			case 'US':
				return 'en-us';
			case 'FI':
			default:
				return 'fi-fi';
		}
	}

	/**
	 * Format Klarna price for Magento
	 *
	 * @param 	int $price
	 * @param 	int store|null
	 * @return  mixed
	 */
	public function formatKlarnaPriceForMagento($price, $store = null)
	{
		$price = $price / 100;
		return Mage::helper('core')->currencyByStore($price, $store, true, false);
	}

	/**
	 * Format Magento price for Klarna
	 *
	 * @param float $price
	 * @return int $price
	 */
	public function formatPriceForKlarna($price)
	{
		return round($price, 2) * 100;
	}

	/**
	 * Check if order is made with KCO
	 *
	 * @param 	Mage_Sales_Model_Order $mo
	 * @return 	bool
	 */
	public function isKcoOrder($order)
	{
		$kcoCodes = array(
			Mage::getModel('klarnaCheckout/payment_KCO')->getCode(),
			Mage::getModel('klarnaCheckout/payment_KCOv3')->getCode()
		);

		$paymentCode = $order->getPayment()->getMethodInstance()->getCode();
		if(in_array($paymentCode, $kcoCodes))
			return true;

		return false;
	}

	/**
	 * Check for key in registry
	 *
	 * @return bool
	 */
	public function isKcoSave()
	{
		if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_KCO_SAVE))
			return true;

		return false;
	}

	/**
	 * Add key to registry
	 */
	public function prepareKcoSave()
	{
		Mage::register(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_KCO_SAVE, true);
	}

	/**
	 *  Remove key from registry
	 */
	public function finishKcoSave()
	{
		Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_KCO_SAVE);
	}

	/**
	 * Get Klarna reservation number from order
	 *
	 * @param	Mage_Sales_Model_Order $mo
	 * @return 	string|false
	 */
	public function getReservationNumber($mo)
	{
		if($rno = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_RESERVATION))
			return $rno;

		return false;
	}

	/**
	 * Get Klarna invoice numbers from order
	 *
	 * @param	Mage_Sales_Model_Order $mo
	 * @return	array
	 */
	public function getKlarnaInvoices($mo)
	{
		if($result = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_INVOICE))
			return $result;

		return array();
	}

	/**
	 * Save Klarna invoice numbers to order
	 *
	 * @param	Mage_Sales_Model_Order $mo
	 * @param	array $klarnainvoices
	 * @return	Mage_Sales_Model_Order
	 */
	public function saveKlarnaInvoices($mo, $klarnainvoices)
	{
		$mo->getPayment()->setAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_INVOICE, $klarnainvoices);
		return $mo;
	}
}