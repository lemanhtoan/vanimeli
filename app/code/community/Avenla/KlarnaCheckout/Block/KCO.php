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

class Avenla_KlarnaCheckout_Block_KCO extends Mage_Core_Block_Template
{
	/**
	 *	Get URL for KCO loadframe action
	 *
	 *	@return string
	 */
	public function getLoadUrl()
	{
		return $this->getUrl(
			"klarnaCheckout/KCO/loadKcoFrame/",
			array("_forced_secure" => Mage::app()->getStore()->isCurrentlySecure())
		);
	}

	/**
	 *	Check if quote has email
	 *
	 *	@return bool
	 */
	public function checkQuoteEmail()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		if($quote->getCustomerEmail())
			return false;

		return true;
	}

	/**
	 *	Get URL for addEmail action
	 *
	 *	@return string
	 */
	public function getAddEmailUrl()
	{
		return $this->getUrl(
			"klarnaCheckout/KCO/addEmail/",
			array("_forced_secure" => Mage::app()->getStore()->isCurrentlySecure())
		);
	}
}