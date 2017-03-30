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
class Avenla_KlarnaCheckout_Block_KCO_Newsletter extends Mage_Core_Block_Template
{
	protected function _toHtml()
	{
		$this->setTemplate('KCO/newsletter.phtml');
		return parent::_toHtml();
	}

	/**
	 * Check for previous selection
	 *
	 * @return bool
	 */
	public function isChecked()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		if($quote->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_NEWSLETTER))
			return true;

		return false;
	}
}