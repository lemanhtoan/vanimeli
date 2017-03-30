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

require_once(Mage::getBaseDir('lib') . '/KlarnaCheckout/autoload.php');

class Avenla_KlarnaCheckout_Model_Order_Kcov2 extends Avenla_KlarnaCheckout_Model_Order_Abstract
{
	/**
	 *  Get related payment model
	 *
	 *  @return Avenla_KlarnaCheckout_Model_Payment_KCOv2
	 */
	protected function getPaymentModel()
	{
		return Mage::getModel('klarnaCheckout/payment_KCO');
	}

	/**
	 *  Get Klarna connector
	 */
	protected function getConnector()
	{
		$this->connector = Klarna_Checkout_Connector::create(
			$this->config->getKlarnaSharedSecret(),
			$this->getServiceUrl()
		);
	}

	/**
	 *  Get Klarna service URL
	 *
	 *  @return string
	 */
	protected function getServiceUrl()
	{
		$url = $this->config->isLive()
			?  Klarna_Checkout_Connector::BASE_URL
			:  Klarna_Checkout_Connector::BASE_TEST_URL;

		return $url;
	}

	/**
	 *  Initialize Klarna order object
	 *
	 *  @param string $checkoutId|null
	 *  @return Klarna_Checkout_Order
	 */
	protected function initOrder($checkoutId = null)
	{
		$this->order = new Klarna_Checkout_Order($this->connector, $checkoutId);
		return $this->order;
	}

	/**
	 *  Get Klarna order id
	 *
	 *  @return string
	 */
	protected function getKlarnaOrderId()
	{
		if(isset($this->order['id']))
			return $this->order['id'];

		return null;
	}

	/**
	 * 	Get extra options
	 *
	 *	@return array|false
	 */
	protected function getExtraOptions()
	{
		$options = array();

		if($this->getAllowedCustomerTypes())
			$options['allowed_customer_types'] = $this->getAllowedCustomerTypes();

		if(!empty($options))
			return $options;

		return false;
	}

	/**
	 * 	Get allowed customer types
	 *
	 * 	@return array
	 */
	private function getAllowedCustomerTypes()
	{
		$types = array();

		$types[] = Avenla_KlarnaCheckout_Model_Config::CUSTOMER_TYPE_PERSON;
		if($this->config->allowB2BFlow())
			$types[] = Avenla_KlarnaCheckout_Model_Config::CUSTOMER_TYPE_ORGANIZATION;

		if(!empty($types))
			return $types;

		return false;
	}

	/**
	 *  Get customer options
	 *
	 *  @return array
	 */
	protected function getCustomerOptions()
	{
		$data = array();

		if($this->quote && !$this->disableCustomerData){
			if($this->quote->getCustomerTaxvat()){
				$data['organization_registration_id'] = $this->quote->getCustomerTaxvat();
				$data['type'] = Avenla_KlarnaCheckout_Model_Config::CUSTOMER_TYPE_ORGANIZATION;
			}
		}

		if(!empty($data))
			return $data;

		return false;
	}

	/**
	 *  Get order data for create or update request
	 *
	 *  @return array
	 */
	protected function getOrderData($isUpdate = false)
	{
		$data = array();
		if(!$isUpdate){
			$data['merchant']['id']                   = $this->config->getKlarnaEid();
			$data['merchant']['terms_uri']            = $this->config->getTermsUri();
			$data['merchant']['checkout_uri']         = $this->helper->getCheckoutUri();
			$data['merchant']['confirmation_uri']     = $this->helper->getConfirmationUri();
			$data['merchant']['push_uri']             = $this->helper->getPushUri();

			if($this->config->getB2BTermsUrl())
				$data['merchant']['organization_terms_uri'] = $this->config->getB2BTermsUrl();

			if($this->helper->getValidationUri())
				$data['merchant']['validation_uri']   = $this->helper->getValidationUri();
		}

		$data['merchant_reference']['orderid1']   = $this->quote ? $this->quote->getId() : '12345';
		$data['cart']['items'] = $this->cart;

		return $data;
	}

	/**
	 *  Add quote item to cart array
	 *
	 *  @param Mage_Sales_Model_Quote_Item
	 */
	protected function addProduct($item)
	{
		$discountRate = 0;
		if($item->getBaseDiscountAmount()){
			$discountRate = $item->getBaseDiscountAmount() / ($item->getBaseRowTotalInclTax() / 100);
			$this->discounted += $item->getBaseDiscountAmount();
		}

		$this->cart[] = array(
			'type'          => 'physical',
			'reference'     => $item->getSku(),
			'name'          => $item->getName(),
			'uri'           => $item->getUrlPath(),
			'quantity'      => (int)$item->getTotalQty(),
			'unit_price'    => $this->helper->formatPriceForKlarna($item->getBasePriceInclTax()),
			'discount_rate' => $this->helper->formatPriceForKlarna($discountRate),
			'tax_rate'      => $this->helper->formatPriceForKlarna($item->getTaxPercent())
		);
	}

	/**
	 *  Process discount from quote to Klarna Checkout order
	 */
	protected function processDiscount()
	{
		$totals = $this->quote->getTotals();
		$baseDiscount = $this->quote->getShippingAddress()->getBaseDiscountAmount();

		if(abs($baseDiscount) - $this->discounted > 0.001){
			$discount = $totals['discount'];
			$diff = abs($baseDiscount) - $this->discounted;

			$this->cart[] = array(
				'type'          => 'discount',
				'reference'     => $discount->getcode(),
				'name'          => $discount->getTitle(),
				'quantity'      => 1,
				'unit_price'    => $this->helper->formatPriceForKlarna($diff),
				'tax_rate'      => 0
			);
		}
	}

	/**
	 *  Process shipping costs from quote to Klarna Checkout order
	 */
	protected function getShippingCosts()
	{
		if($this->quote->getShippingAddress()->getShippingMethod() != null){

			$taxRate = $this->helper->getShippingVatRate($this->quote);
			$shippingAddress = $this->quote->getShippingAddress();
			$discountRate = 0;

			if($shippingAddress->getBaseShippingDiscountAmount()){
				$discountRate = $shippingAddress->getBaseShippingInclTax() == 0
					?  100
					:  $shippingAddress->getBaseShippingDiscountAmount() / ($shippingAddress->getBaseShippingInclTax() / 100);
				$this->discounted += $shippingAddress->getBaseShippingDiscountAmount();
			}

			$shippingCosts = array(
				'type'          => 'shipping_fee',
				'reference'     => 'shipping_fee',
				'name'          => $shippingAddress->getShippingDescription(),
				'quantity'      => 1,
				'unit_price'    => $this->helper->formatPriceForKlarna($shippingAddress->getBaseShippingInclTax()),
				'discount_rate' => $this->helper->formatPriceForKlarna($discountRate),
				'tax_rate'      => $this->helper->formatPriceForKlarna($taxRate)
			);

			$this->cart[] = $shippingCosts;
		}
	}

	/**
	 *  Get Klarna HTML-snippet
	 *
	 *  @return string
	 */
	public function getHtmlSnippet()
	{
		return $this->order['gui']['snippet'];
	}

	/**
	 *  Get merchant reference
	 *
	 *  @param int $no|1
	 *  @return string
	 */
	public function getMerchantReference($no = 1)
	{
		if($this->order['merchant_reference']['orderid'.$no])
			return $this->order['merchant_reference']['orderid'.$no];

		return false;
	}

	/**
	 *  Cancel Klarna reservation
	 */
	public function cancelReservation()
	{
		Mage::getModel('klarnaCheckout/api')->cancelReservation($this->order['reservation']);
	}

	/**
	 *  Confirm Klarna order
	 *
	 *  @param Mage_Sales_Model_Order $mo
	 *  @param string $quoteId
	 */
	public function confirmOrder($mo, $checkoutId)
	{
		$quoteId = $this->order['merchant_reference']['orderid1'];
		$data['merchant_reference']['orderid1'] = $mo->getIncrementId();
		$data['merchant_reference']['orderid2'] = $quoteId;
		$data['status'] = 'created';
		$this->order->update($data);

		if($this->order['status'] != "created"){
			$this->cancelMagentoOrder($mo, $this->__('Order canceled: Failed to create order in Klarna.'));
		}
		else{
			parent::confirmOrder($mo, $quoteId);
		}
	}

	/**
	 * Get Klarna order information
	 *
	 * @param array $paymentInfo|null
	 * @return Varien_Object
	 */
	public function getOrderInformation($checkoutId, $paymentInfo = null)
	{
		$this->getOrder(null, $checkoutId);

		$info = new Varien_Object();
		$info->setLogoSrc($this->helper->getLogoSrc());
		$info->setGuiUrl(Avenla_KlarnaCheckout_Model_Config::ONLINE_GUI_URL);
		$info->setExpiration($this->order['expires_at']);

		if(isset($this->order['billing_address']['reference']))
			$info->setOrganizationReference($this->order['billing_address']['reference']);

		if($paymentInfo){
			$info->setReservation($paymentInfo->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_RESERVATION));
			if (count($paymentInfo->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_INVOICE)) > 0){
				$info->setOrderInvoices($paymentInfo->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_INVOICE));
				$info->setPdfUrl($this->getServiceUrl() . "/packslips/");
			}
			if (strlen($paymentInfo->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_MESSAGE)) > 0)
				$info->setMessage($paymentInfo->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_MESSAGE));
		}

		return $info;
	}

	/**
	 * Get additional information to be saved in Magento order
	 *
	 * @return array
	 */
	protected function getAdditionalOrderInformation()
	{
		$data = array();
		$data[Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID] = $this->getKlarnaOrderId();
		$data[Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_RESERVATION] = $this->order['reservation'];

		return $data;
	}

	/**
	 * Create dummy order with test product
	 *
	 * @return  Klarna_Checkout_Order
	 */
	public function dummyOrder($quote = null)
	{
		try{
			$this->dummy = true;
			if($quote)
				$this->quote = $quote;

			$this->initOrder();
			$this->cart = array(
				array(
					'reference'     => '123456789',
					'name'          => 'Test product',
					'quantity'      => 1,
					'unit_price'    => 4490,
					'tax_rate'      => 0
				));
			$this->createOrder();

			return $this->order;
		}
		catch(Exception $e){
			$this->helper->logException($e);
			return false;
		}
	}

	/**
	 *  Get order analytics data
	 *
	 *  @return Varien_Object
	 */
	public function getAnalyticsData()
	{
		$ko = $this->order;

		if(count($ko['cart']['items']) < 1)
			return false;

		$orderId = false;
		if(isset($ko['merchant_reference']['orderid2']) && strlen($ko['merchant_reference']['orderid2']) > 0){
			$mo = Mage::getModel('sales/order')->load($ko['merchant_reference']['orderid1'], 'increment_id');
			if($mo->getId())
				$orderId = $ko['merchant_reference']['orderid1'];
		}

		if(!$orderId){
			$quote = Mage::getModel('sales/quote')->load($ko['merchant_reference']['orderid1']);
			$quote->reserveOrderId();
			$quote->save();
			$orderId = $quote->getReservedOrderId();
		}

		$result = new Varien_Object();
		$result->setOrderId($orderId);
		$result->setTotalInclTax($ko['cart']['total_price_including_tax'] / 100);
		$result->setTotalTaxAmount($ko['cart']['total_tax_amount'] / 100);
		$result->setBillingCity($ko['billing_address']['city']);
		$result->setBillingCountry($ko['billing_address']['country']);
		$result->setCurrency(strtoupper($ko['purchase_currency']));
		$result->setShippingFee(0);
		$items = array();
		foreach($ko['cart']['items'] as $p){
			if($p['type'] == 'shipping_fee'){
				$result->setShippingFee($p['total_price_including_tax'] / 100);
			}
			else{
				$line = new Varien_Object();
				$line->setReference($p['reference']);
				$line->setName($p['name']);
				$line->setPrice($p['unit_price'] / 100);
				$line->setQty($p['quantity']);
				$items[] = $line;
			}
		}
		$result->setItems($items);

		return $result;
	}
}