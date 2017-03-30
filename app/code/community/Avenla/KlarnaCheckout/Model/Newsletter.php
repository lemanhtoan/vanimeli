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

class Avenla_KlarnaCheckout_Model_Newsletter extends Mage_Core_Model_Abstract
{

	/**
	 * Save newsletter selection to additional data
	 *
	 * @param bool $status
	 */
	public function addNewsletterStatus($status)
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$quote->getPayment()->setAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_NEWSLETTER, $status);
		$quote->save();
	}

	/**
	 * Subscribe order e-mail for newsletter
	 *
	 * @param Mage_Sales_Model_Order $mo
	 * @param int $websiteId
	 */
	public function signForNewsLetter($mo, $websiteId)
	{
		$email = $mo->getCustomerEmail();
		$subscriberId = Mage::getModel('newsletter/subscriber')->loadByEmail($email)->getId();

		if($subscriberId)
			return false;

		try{
			if($mo->getCustomerId() != NULL){
				$customer = Mage::getModel('customer/customer')->load($mo->getCustomerId());
				Mage::getModel('newsletter/subscriber')->subscribeCustomer($customer);
			}
			else{
				Mage::getModel('newsletter/subscriber')->subscribe($email);
			}
		}
		catch (Exception $e) {
			Mage::helper('KlarnaCheckout')->logException($e);
		}
	}
}