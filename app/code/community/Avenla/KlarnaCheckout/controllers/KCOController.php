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
class Avenla_KlarnaCheckout_KCOController extends Mage_Core_Controller_Front_Action
{
	/**
	 *  Load Klarna Checkout iframe
	 */
	public function loadKcoFrameAction()
	{
		$result = new Varien_Object();
		$kco = Mage::helper('klarnaCheckout')->getKco();
		$quote = Mage::getSingleton('checkout/session')->getQuote();

		if (!$quote->getCustomerIsGuest() && !Mage::getSingleton('customer/session')->isLoggedIn() && Mage::helper('checkout')->isAllowedGuestCheckout($quote))
			$quote->setCustomerIsGuest(true)->save();

		if($validationMessage = $quote->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_VALIDATION_MSG)){
			$quote->getPayment()->setAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_VALIDATION_MSG, null);
			$result->setValidationMsg($validationMessage);
		}

		if(!$kco->isAvailable($quote)){
			$result->setMsg(Mage::getSingleton('core/session')->getKCOMessage());

			if(Mage::getSingleton('core/session')->getKCORequireLogin()){
				$this->loadLayout();
				Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('checkout/cart'));
				$result->setKlarnaframe($this->getLayout()->createBlock('customer/form_login')->setTemplate('customer/form/mini.login.phtml')->toHtml());
				$result->setKcologin(true);
			}
		}
		else{
			$kcoOrder = $kco->getOrderModel();
			if($kcoOrder->getOrder($quote)){
				if(!Mage::getModel('klarnaCheckout/validator')->validateQuote($quote))
					$result->setMsg(Mage::getSingleton('core/session')->getKCOMessage());

				$result->setKlarnaframe($kcoOrder->getHtmlSnippet());
			}
            else{
				$result->setMsg("Klarna Checkout is not available");
			}
		}

		Mage::getSingleton('core/session')->unsKCOMessage();
		Mage::getSingleton('core/session')->unsKCORequireLogin();

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result->getData()));
	}

	/**
	 * Add email to quote
	 */
	public function addEmailAction()
	{
		$result = array();
		$kcoEmail = $this->getRequest()->getParam('klarna_email');

		if (!Zend_Validate::is($kcoEmail, 'EmailAddress') || !Zend_Validate::is($kcoEmail, 'NotEmpty'))
			return false;

		try {
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			$quote->setCustomerEmail($kcoEmail)->save();
		}
		catch (Exception $e) {
			Mage::helper('klarnaCheckout')->logException($e);
		}

		$result['msg'] = ' ';
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	/**
	 *  Confirmation action for Klarna Checkout
	 */
	public function confirmationAction()
	{
		$redirect = false;
		$checkoutId = Mage::app()->getRequest()->getParam(Avenla_KlarnaCheckout_Model_Payment_Abstract::REQUEST_KLARNA_ORDER);
		$kco = Mage::helper('klarnaCheckout')->getKco()->getOrderModel();
		$ko = $kco->getOrder(null, $checkoutId);

		try{
			$ko->fetch();
			if (($ko['status'] == "checkout_complete" || $ko['status'] == "created") && Mage::getSingleton('core/session')->getKCOid()){
				$this->emptyCart();
				$this->loadLayout();

				$confirmation = $this->getLayout()->getBlock('klarnaCheckout.confirmation');
				$confirmation->setCheckoutID($checkoutId);
				$confirmation->setKlarnaSnippet($kco->getHtmlSnippet());

				if(Mage::getModel('klarnaCheckout/config')->getGoogleAnalyticsNo() !== false)
					$confirmation->setAnalyticsData($kco->getAnalyticsData());

				$this->renderLayout();
				Mage::getSingleton('core/session')->unsKCOid();
			}
			else{
				$redirect = true;
			}
		}
		catch(Exception $e) {
			Mage::helper('klarnaCheckout')->logException($e);
			$redirect = true;
		}

		if($redirect){
			header('Location: ' . Mage::helper('checkout/url')->getCartUrl());
			exit();
		}
	}

	/**
	 *  Validation action for Klarna Checkout
	 */
	public function validationAction()
	{
		$validator = Mage::getModel('klarnaCheckout/validator');
		$ko = $validator->parseValidationPost();
		$quoteId = false;
		if(isset($ko->merchant_reference->orderid1)){
			$quoteId = $ko->merchant_reference->orderid1;
		}
		elseif(isset($ko->merchant_reference1)){
			$quoteId = $ko->merchant_reference1;
		}

		if($quoteId){
			$quote = Mage::getModel("sales/quote")->load($quoteId);

			if(!$validator->validateQuote($quote, $ko)){
				$this->getResponse()
					->setHttpResponseCode(303)
					->setHeader('Location', Mage::getUrl('checkout/cart'));
			}
		}
	}

	/**
	 *  Save gift message form
	 *
	 */
	public function saveGiftMessageAction()
	{
		Mage::dispatchEvent(
			'kco_save_giftmessage',
			array(
				'request' => $this->getRequest(),
				'quote'   => Mage::getSingleton('checkout/session')->getQuote()
			)
		);

		$result = array();
		$result['msg'] = $this->__('Gift message saved successfully');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}

	/**
	 *  Push action for Klarna Checkout
	 */
	public function pushAction()
	{
		$checkoutId = $this->getRequest()->getParam(Avenla_KlarnaCheckout_Model_Payment_Abstract::REQUEST_KLARNA_ORDER);
		$storeid = $this->getRequest()->getParam('storeid');
		Mage::app()->setCurrentStore($storeid);

		$kco = Mage::helper('klarnaCheckout')->getKco()->getOrderModel();
		$ko = $kco->getOrder(null, $checkoutId);

		$quoteId = $kco->getMerchantReference();

		if (!$kco->isOrderComplete()){
			Mage::helper('klarnaCheckout')->log("Klarna order " . $checkoutId . " not complete in push. Status: " . $ko['status']);
			return false;
		}

		if(!$quoteId){
			$kco->cancelReservation();
			Mage::helper('klarnaCheckout')->log("No quote found for Klarna order " . $checkoutId);
			return false;
		}

		$quote = Mage::getModel("sales/quote")->load($quoteId);

		if(count($quote->getAllItems()) < 1){
			Mage::helper('klarnaCheckout')->log("Quote has no items, reservation canceled.");
			$kco->cancelReservation();
			return false;
		}
		try{
			$mo = $kco->createMagentoOrder();
			$kco->confirmOrder($mo, $checkoutId);
		}
		catch(Exception $e){
			Mage::helper('klarnaCheckout')->logException($e);
		}
	}

	/**
	 * Clear the checkout session after successful checkout
	 */
	private function emptyCart()
	{
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$quote->setIsActive(false)->save();
		Mage::getSingleton('checkout/session')->clear();
	}

	/**
	 * Save newsletter subscribtion status
	 */
	public function newsletterAction()
	{
		$result = array();
		$status = false;
		$customerSession = Mage::getSingleton('customer/session');

		if($this->getRequest()->getParam('status') == true)
			$status = true;

		if ($status && Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1
			&& !$customerSession->isLoggedIn()) {
			$result['letter_msg'] = $this->__(
				'Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.',
				Mage::helper('customer')->getRegisterUrl()
			);
			$status = false;
		}

		$result['msg'] = ' ';

		Mage::getModel('klarnaCheckout/newsletter')->addNewsletterStatus($status);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}