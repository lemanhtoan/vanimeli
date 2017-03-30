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

class Avenla_KlarnaCheckout_Model_Validator extends Mage_Core_Model_Abstract
{

	/**
	 * Parse Klarna order for validation
	 *
	 * @return object
	 */
	public function parseValidationPost()
	{
		$rawrequestBody = file_get_contents('php://input');

		if (mb_detect_encoding($rawrequestBody, 'UTF-8', true)){
			$order = json_decode($rawrequestBody);
		}
		else {
			$rawrequestBody = iconv("ISO-8859-1", "UTF-8", $rawrequestBody);
			$order = json_decode($rawrequestBody);
		}

		return $order;
	}

	/**
	 * Validate quote
	 *
	 * @param Mage_Sales_Model_Quote $quote
	 * @param object $ko|null
	 * @return bool
	 */
	public function validateQuote($quote, $ko = null)
	{
		$result = true;
		if($ko){
			if(!isset($ko->shipping_address->phone) || !isset($ko->billing_address->phone)){
				$msg = Mage::helper('klarnaCheckout')->__('Please fill in your phone number.');
				$this->setErrorMessage($quote, $msg);
				$result = false;
			}
		}

		if(!$quote->isVirtual()){

			$address = $quote->getShippingAddress();
			$method = $address->getShippingMethod();
			$rate  = $address->getShippingRateByCode($method);

			if($address->getPostcode() == null){
				$msg = Mage::helper('klarnaCheckout')->__("Please fill in your post code");
				$result = false;
			}

			if($ko && $address->getPostcode() != $ko->shipping_address->postal_code){
				$msg = Mage::helper('klarnaCheckout')->__('Please use the same post code for your quote and Klarna.');
				return false;
			}

			if($address->getCountry() == null){
				$msg = Mage::helper('klarnaCheckout')->__("Please select country");
				$result = false;
			}

			if (!$method || !$rate){
				$msg = Mage::helper('klarnaCheckout')->__("Please select shipping method to use Klarna Checkout");
				$result = false;
			}
		}

		if(isset($msg))
			$this->setErrorMessage($quote, $msg, $ko != null);

		return $result;
	}

	/**
	 *  Set error message to quote or customer session
	 *
	 *  @param Mage_Sales_Model_Quote
	 *  @param string $message
	 * 	@param bool $toQuote|null
	 */
	private function setErrorMessage($quote, $message, $toQuote = false)
	{
		if($toQuote){
			$quote->getPayment()->setAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_VALIDATION_MSG, $message);
			$quote->getPayment()->save();
		}
		else{
			Mage::getSingleton('core/session')->setKCOMessage($message);
		}
	}
}