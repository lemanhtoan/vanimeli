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

class Avenla_KlarnaCheckout_Model_Order_Abstract extends Mage_Core_Model_Abstract
{
	public $order;
	protected $helper;
	protected $config;
	protected $connector;
	protected $cart;
	protected $quote;
	protected $discounted = 0;
	protected $dummyAmount = 4490;
	protected $dummy = false;
	protected $disableCustomerData = false;

	public function __construct()
	{
		$this->helper = Mage::helper("klarnaCheckout");
		$this->config = $this->getPaymentModel()->getConfig();
		$this->getConnector();
	}

	/**
	 *  Get related Klarna order object
	 *
	 *  @param Mage_Sales_Model_Quote $quote
	 *  @param string $checkoutId
	 *  @return mixed
	 */
	public function getOrder($quote = null, $checkoutId = null)
	{
		if(!$checkoutId && Mage::getSingleton('core/session')->getKCOid())
			$checkoudId = Mage::getSingleton('core/session')->getKCOid();

		if(!$this->order)
			$this->initOrder($checkoutId);

		if(!$quote){
			$this->order->fetch();
			return $this->order;
		}

		$this->quote = $quote;
		$this->addProductsToCart();
		$this->getShippingCosts();
		$this->processDiscount();

		$checkoutId ? $this->updateOrder() : $this->createOrder();

		Mage::getSingleton('core/session')->setKCOid($this->getKlarnaOrderId());

		return $this->order;
	}

	/**
	 *  Use configuration for given store and reload connector
	 *
	 *  @param int store
	 */
	public function useConfigForStore($store)
	{
		$this->config->setStore($store);
		$this->getConnector();
	}

	/**
	 * Get purchase country
	 *
	 * @return  string
	 */
	protected function getPurchaseCountry()
	{
		if($this->quote && $this->quote->getShippingAddress()->getCountry())
			return $this->quote->getShippingAddress()->getCountry();

		return $this->config->getDefaultCountry();
	}

	/**
	 * Get customer info for Klarna Checkout
	 *
	 * @return	array
	 */
	protected function getAddressInfo()
	{
		$info = array();

		if($this->quote && !$this->disableCustomerData){
			$sa = $this->quote->getShippingAddress();
			$sa->getPostcode() != null ? $info['postal_code'] = $sa->getPostcode() : '';
			$this->quote->getCustomerEmail() != null ? $info['email'] = $this->quote->getCustomerEmail() : '';
		}

		return $info;
	}

	/**
	 * Create new Klarna Checkout order
	 */
	protected function createOrder()
	{
		$request = new Varien_Object();
		$info = $this->getAddressInfo();

		if(!empty($info))
			$request->setShippingAddress($info);

		$request->setPurchaseCountry($this->getPurchaseCountry());
		$request->setPurchaseCurrency(Mage::app()->getStore()->getBaseCurrencyCode());
		$request->setLocale($this->helper->getLocale($this->getPurchaseCountry()));

		$options = new Varien_Object();
		$options->setAllowSeparateShippingAddress($this->config->allowSeparateShippingAddress());

		if($this->config->useCustomColors()){
			$options->setColorButton('#'.$this->config->getButtonColor());
			$options->setColorButtonText('#'.$this->config->getButtonTextColor());
			$options->setColorCheckbox('#'.$this->config->getCheckboxColor());
			$options->setColorCheckboxCheckmark('#'.$this->config->getCheckboxCheckmarkColor());
			$options->setColorHeader('#'.$this->config->getHeaderColor());
			$options->setColorLink('#'.$this->config->getLinkColor());
		}

		if($extraOptions = $this->getExtraOptions())
			$options->addData($extraOptions);

		if($customerOptions = $this->getCustomerOptions())
			$request->setCustomer($customerOptions);

		if($this->config->useAdditionalCheckbox()) {
			$options->setAdditionalCheckbox(
				array(
					'text' => $this->config->getAdditionalCheckboxText(),
					'checked' => $this->config->getAdditionalCheckboxChecked(),
					'required' => $this->config->getAdditionalCheckboxRequired()
				)
			);
		}

		$request->setGui(array('options' => array('disable_autofocus')));
		$request->addData(array('options' => $options->getData()));
		$request->addData($this->getOrderData());

		try{
			$this->helper->log($request->getData());
			$this->order->create($request->getData());

			if(!$this->dummy)
				$this->order->fetch();
		}
		catch(Klarna_Checkout_ApiErrorException $e){
			$this->helper->logException($e);
			if(!$this->disableCustomerData){
				$this->disableCustomerData = true;
				$this->createOrder();
				return;
			}
			$this->helper->logException($e);
			$this->order = null;
		}
		catch (Exception $e){
			$this->helper->logException($e);
			$this->order = null;
		}
	}

	/**
	 * Update existing Klarna Checkout order
	 */
	protected function updateOrder()
	{
		try {
			$this->order->fetch();
			if(!$this->helper->isOrderFromCurrentStore($this->order) ||
				strtoupper($this->order['purchase_country']) != $this->getPurchaseCountry()){
				$this->createOrder();
				return;
			}

			$request = new Varien_Object();
			$info = $this->getAddressInfo();
			if(!empty($info))
				$request->setShippingAddress($info);

			$request->addData($this->getOrderData(true));
			$this->order->update($request->getData());
		}
		catch (Exception $e){
			$this->helper->logException($e);
			$this->order = null;
			Mage::getSingleton('core/session')->unsKCOid();
		}
	}

	/**
	 *  Process items from quote to KCO order cart
	 */
	protected function addProductsToCart()
	{
		$this->cart = array();
		$mCart = $this->quote->getAllVisibleItems();

		if(count($mCart) > 0){
			foreach ($mCart as $i){
				if($i->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE && $i->isChildrenCalculated()){
					foreach($i->getChildren() as $c){
						$this->addProduct($c);
					}
				}
				else{
					$this->addProduct($i);
				}
			}
		}
	}

	/**
	 *  Create Magento order from Klarna order
	 *
	 *  @return Mage_Sales_Model_Order
	 */
	public function createMagentoOrder()
	{
		$quoteId = $this->getMerchantReference();
		$quote = Mage::getModel("sales/quote")->load($quoteId);
		$ko = $this->order;

		if($quote->getCustomerIsGuest()){
			$quote->setCustomerEmail($ko['billing_address']['email']);
			$quote->setCustomerFirstname($ko['billing_address']['given_name']);
            $quote->setCustomerLastname($ko['billing_address']['family_name']);
            $quote->save();
		}

		if($ko['customer']['type'] == Avenla_KlarnaCheckout_Model_Config::CUSTOMER_TYPE_ORGANIZATION)
			$quote->setCustomerTaxvat($ko['customer']['organization_registration_id']);

		$quote->getBillingAddress()->addData($this->convertAddress($ko['billing_address']));
		$quote->getShippingAddress()->addData($this->convertAddress($ko['shipping_address']));
		$quote->getPayment()->setMethod($this->getPaymentModel()->getCode());

		if($additionalInformation = $this->getAdditionalOrderInformation()){
			foreach ($additionalInformation as $key => $value)
				$quote->getPayment()->setAdditionalInformation($key, $value);
		}

		$quote->collectTotals()->save();

		$service = Mage::getModel('sales/service_quote', $quote);
		$service->submitAll();
		$quote->setIsActive(false)->save();

		return $service->getOrder();
	}

	/**
	 * Convert Klarna address to Magento address
	 *
	 * @param  	array $address
	 * @param  	string $region
	 * @param  	string $region_code
	 * @return 	array
	 */
	private function convertAddress($address, $region = '', $region_code = '')
	{
		$country_id = strtoupper($address['country']);

		if($region_code == '')
			$region_code = 1;

		$street = isset($address['street_address'])
			? $address['street_address']
			: $address['street_name']  . " " . $address['street_number'];

		$phone = strlen($address['phone'] > 0) ? $address['phone'] : '1';

		$magentoAddress = array(
			'firstname'             => $address['given_name'],
			'lastname'              => $address['family_name'],
			'email'                 => $address['email'],
			'street'                => $street,
			'city'                  => $address['city'],
			'region_id'             => $region_code,
			'region'                => $region,
			'postcode'              => $address['postal_code'],
			'country_id'            => strtoupper($address['country']),
			'telephone'             => $phone
		);

		if(isset($address['organization_name']))
			$magentoAddress['company'] = $address['organization_name'];

		return $magentoAddress;
	}

	/**
	 * Cancel Magento order
	 *
	 * @param   Mage_Sales_Model_Order $mo
	 * @param   string $msg
	 */
	protected function cancelMagentoOrder($mo, $msg)
	{
		$mo->cancel();
		$mo->setStatus($msg);
		$mo->save();
	}

	/**
	 * Confirm order
	 *
	 * @param Mage_Sales_Model_Order $mo
	 */
	public function confirmOrder($mo, $checkoutId)
	{
		$mo->getSendConfirmation(null);
		$mo->sendNewOrderEmail();
		if($mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_NEWSLETTER))
			Mage::getModel('klarnaCheckout/newsletter')->signForNewsLetter($mo, Mage::app()->getStore()->getWebsiteId());
	}

	/**
	 *  Check if the Klarna order is complete
	 *
	 *  @return bool
	 */
	public function isOrderComplete()
	{
		if($this->order['status'] == "checkout_complete")
			return true;

		return false;
	}
}