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

require_once 'Mage/Checkout/controllers/CartController.php';
class Avenla_KlarnaCheckout_CartController extends Mage_Checkout_CartController
{
	/**
     * Initialize shipping information
     */
	public function estimateAjaxPostAction()
	{
		$country 	= (string) $this->getRequest()->getParam('country_id');
		$postcode 	= (string) $this->getRequest()->getParam('estimate_postcode');
		$city 		= (string) $this->getRequest()->getParam('estimate_city');
		$regionId 	= (string) $this->getRequest()->getParam('region_id');
		$region 	= (string) $this->getRequest()->getParam('region');

		$this->_getQuote()->getShippingAddress()
			->setCountryId($country)
			->setCity($city)
			->setPostcode($postcode)
			->setRegionId($regionId)
			->setRegion($region)
			->setCollectShippingRates(true);

		$this->_getQuote()->save();
		$this->_getCart()->save();

		$this->getResponse()->setBody($this->getEncodedResponse());
	}

	/**
	 * Estimate update action
	 */
	public function estimateUpdateAjaxPostAction()
	{
		$code = (string) $this->getRequest()->getParam('estimate_method');

		if (!empty($code))
			$this->_getQuote()->getShippingAddress()->setShippingMethod($code)->save();

		$cart = $this->_getCart();
		$cart->save();

		$this->getResponse()->setBody($this->getEncodedResponse());
	}

	/**
	 * Get response array
	 *
	 * @return array
	 */
	private function getEncodedResponse()
	{
		$resp = array(
			'shipping'	=> $this->getShippingHtml(),
			'totals'	=> $this->getTotalsHtml(),
			'msg'		=> ""
		);

		return Mage::helper('core')->jsonEncode($resp);
	}

	/**
	 * 	Get shipping html
	 *
	 *	@return string
	 */
	private function getShippingHtml()
	{
		$layout = $this->getLayout();
		$layout->getMessagesBlock()->setMessages(Mage::getSingleton('checkout/session')
			->getMessages(true),Mage::getSingleton('catalog/session')->getMessages(true));
		$block = $this->getLayout()->createBlock('checkout/cart_shipping')->setTemplate('KCO/cart/shipping.phtml');

		return $block->toHtml();
	}

	/**
	 * 	Get review html
	 *
	 *	@return string
	 */
	private function getTotalsHtml()
	{
		$layout = $this->getLayout();
		$layout->getMessagesBlock()->setMessages(Mage::getSingleton('checkout/session')
			->getMessages(true),Mage::getSingleton('catalog/session')->getMessages(true));
		$block = $this->getLayout()->createBlock('checkout/cart_totals')->setTemplate('checkout/cart/totals.phtml');

		return $block->toHtml();
	}
}