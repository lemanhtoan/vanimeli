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

class Avenla_KlarnaCheckout_Model_Order_Kcov3 extends Avenla_KlarnaCheckout_Model_Order_Abstract
{
	const ORDER_STATUS_AUTHORIZED   = 'AUTHORIZED';
	const ORDER_STATUS_PARTCAPTURED = 'PART_CAPTURED';
	const ORDER_STATUS_CAPTURED     = 'CAPTURED';
	const ORDER_STATUS_CANCELLED    = 'CANCELLED';
	const ORDER_STATUS_EXPIRED      = 'EXPIRED';
	const ORDER_STATUS_CLOSED       = 'CLOSED';

	private $orderManagement = null;

	/**
	 *  Get related payment model
	 *
	 *  @return Avenla_KlarnaCheckout_Model_Payment_KCOv3
	 */
	protected function getPaymentModel()
	{
		return Mage::getModel('klarnaCheckout/payment_KCOv3');
	}

	/**
	 *  Get Klarna connector
	 */
	protected function getConnector()
	{
		$connector = Klarna\Rest\Transport\Connector::create(
			$this->config->getKlarnaEid(),
			$this->config->getKlarnaSharedSecret(),
			$this->getServiceUrl()
		);

		$this->connector = $connector;
	}

	/**
	 *  Get Klarna service URL
	 *
	 *  @return string
	 */
	protected function getServiceUrl()
	{
		$url = null;

		if($this->config->getApiVersion() == Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV3_US){
			$url = $this->config->isLive()
				?  Klarna\Rest\Transport\ConnectorInterface::NA_BASE_URL
				:  Klarna\Rest\Transport\ConnectorInterface::NA_TEST_BASE_URL;
		}
		else{
			$url = $this->config->isLive()
				?  Klarna\Rest\Transport\ConnectorInterface::EU_BASE_URL
				:  Klarna\Rest\Transport\ConnectorInterface::EU_TEST_BASE_URL;
		}

		return $url;
	}

	/**
	 *  Initialize Klarna order object
	 *
	 *  @param string $checkoutId|null
	 *  @return Klarna\Rest\Checkout\Order
	 */
	protected function initOrder($checkoutId = null)
	{
		$this->order = new Klarna\Rest\Checkout\Order($this->connector, $checkoutId);
		return $this->order;
	}

	/**
	 *  Get Klarna order id
	 *
	 *  @return string
	 */
	protected function getKlarnaOrderId()
	{
		if($this->order['order_id'])
			return $this->order['order_id'];

		return null;
	}

	/**
	 * 	Get extra options
	 *
	 *	@return array|false
	 */
	protected function getExtraOptions()
	{
		return false;
	}

	/**
	 *  Get customer options
	 *
	 *  @return array
	 */
	protected function getCustomerOptions()
	{
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
		$data['merchant_reference1']              = $this->quote ? $this->quote->getId() : '12345';
		if(!$isUpdate){
			$data['merchant_urls']['terms']           = $this->config->getTermsUri();
			$data['merchant_urls']['checkout']        = $this->helper->getCheckoutUri();
			$data['merchant_urls']['confirmation']    = $this->helper->getConfirmationUri($this->type);
			$data['merchant_urls']['push']            = $this->helper->getPushUri($this->type);

			if($this->helper->getValidationUri($this->type))
				$data['merchant_urls']['validation'] = $this->helper->getValidationUri($this->type);
			}

		$data["order_amount"]                     = $this->dummy ? $this->dummyAmount : $this->helper->formatPriceForKlarna($this->quote->getBaseGrandTotal());
		$data["order_tax_amount"]                 = $this->dummy ? 0 : $this->helper->formatPriceForKlarna($this->quote->getShippingAddress()->getBaseTaxAmount());
		$data['order_lines'] = $this->cart;

		return $data;
	}

	/**
	 *  Add quote item to cart array
	 *
	 *  @param Mage_Sales_Model_Quote_Item
	 */
	protected function addProduct($item)
	{
		if($item->getBaseDiscountAmount())
			$this->discounted += $item->getBaseDiscountAmount();

		$discountedTotal = $item->getBaseRowTotalInclTax() - $item->getBaseDiscountAmount();

		$this->cart[] = array(
			'type'                  => 'physical',
			'reference'             => $item->getSku(),
			'name'                  => $item->getName(),
			'quantity'              => (int)$item->getTotalQty(),
			'unit_price'            => $this->helper->formatPriceForKlarna($item->getBasePriceInclTax()),
			'tax_rate'              => $this->helper->formatPriceForKlarna($item->getTaxPercent()),
			'total_amount'          => $this->helper->formatPriceForKlarna($discountedTotal),
			'total_tax_amount'      => $this->helper->formatPriceForKlarna($item->getBaseTaxAmount()),
			'total_discount_amount' => $this->helper->formatPriceForKlarna($item->getBaseDiscountAmount())
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
				'type'              => 'discount',
				'reference'         => $discount->getcode(),
				'name'              => $discount->getTitle(),
				'quantity'          => 1,
				'unit_price'        => $this->helper->formatPriceForKlarna($diff),
				'tax_rate'          => 0,
				'total_amount'      => $this->helper->formatPriceForKlarna($diff),
				'total_tax_amount'  => 0
			);
		}
	}

	/**
	 *  Process shipping costs from quote to Klarna Checkout order
	 */
	protected function getShippingCosts()
	{
		if($this->quote->getShippingAddress()->getShippingMethod() != null){

			$taxRate = Mage::helper('klarnaCheckout')->getShippingVatRate($this->quote);
			$shippingAddress = $this->quote->getShippingAddress();

			if($shippingAddress->getBaseShippingDiscountAmount())
				$this->discounted += $shippingAddress->getBaseShippingDiscountAmount();

			$this->cart[] = array(
				'type'                  => 'shipping_fee',
				'reference'             => 'shipping_fee',
				'name'                  => $shippingAddress->getShippingDescription(),
				'quantity'              => 1,
				'unit_price'            => $this->helper->formatPriceForKlarna($shippingAddress->getBaseShippingInclTax()),
				'tax_rate'              => (int)($taxRate * 100),
				'total_tax_amount'      => $this->helper->formatPriceForKlarna($shippingAddress->getBaseShippingTaxAmount()),
				'total_discount_amount' => $this->helper->formatPriceForKlarna($shippingAddress->getBaseShippingDiscountAmount()),
				'total_amount'          => $this->helper->formatPriceForKlarna($shippingAddress->getBaseShippingInclTax())
			);
		}
	}

	/**
	 *  Get Klarna HTML-snippet
	 *
	 *  @return string
	 */
	public function getHtmlSnippet()
	{
		return $this->order['html_snippet'];
	}

	/**
	 *  Get merchant reference
	 *
	 *  @param int $no|1
	 *  @return string
	 */
	public function getMerchantReference($no = 1)
	{
		return $this->order['merchant_reference'.$no];
	}

	/**
	 *  Cancel Klarna reservation
	 *
	 */
	public function cancelReservation()
	{
		$this->order->cancel();
	}

	/**
	 *  Get Klarna order management object
	 *
	 *  @param string $orderId
	 *  @return \Klarna\Rest\OrderManagement\Order
	 */
	public function getManagement($orderId)
	{
		if(!$this->orderManagement){
			$om = new \Klarna\Rest\OrderManagement\Order(
				$this->connector,
				$orderId
			);

			$om->fetch();
			$this->orderManagement = $om;
		}

		return $this->orderManagement;
	}

	/**
	 *  Confirm Klarna order
	 *
	 *  @param Magento_Sales_Model_Order $mo
	 *  @param string $checkoutId
	 */
	public function confirmOrder($mo, $checkoutId)
	{
		$quoteId = $this->order['merchant_reference1'];
		$om = $this->getManagement($checkoutId);
		$om->updateMerchantReferences([
			"merchant_reference1" => $mo->getIncrementId(),
			"merchant_reference2" => $quoteId
		]);

		$om->acknowledge();
		parent::confirmOrder($mo, $checkoutId);
	}

	/**
	 * Get Klarna order information
	 *
	 * @param array $paymentInfo|null
	 * @return Varien_Object
	 */
    public function getOrderInformation($checkoutId, $paymentInfo = null)
    {
		$info = new Varien_Object();

		$klarnainfo = $this->getManagement($checkoutId);
		$info->setLogoSrc($this->helper->getLogoSrc());
		$info->setExpiration($klarnainfo['expires_at']);
		$info->setKlarnaReference($klarnainfo['klarna_reference']);
		$info->setCaptures($klarnainfo['captures']);
		$info->setRefunds($klarnainfo['refunds']);
		$info->setRemainingAmount($klarnainfo['remaining_authorized_amount']);

		switch ($klarnainfo['status']) {
			case self::ORDER_STATUS_EXPIRED:
				$info->setStatusMessage("Order authorization time has expired");
				break;
			case self::ORDER_STATUS_CLOSED:
				$info->setStatusMessage("Order has been closed");
				break;
			case self::ORDER_STATUS_CANCELLED:
				$info->setStatusMessage("Order has been canceled");
				break;
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

		return $data;
	}

	/**
	 * Create dummy order with test product
	 *
	 * @return  Klarna\Rest\Checkout\Order
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
					'type'                  => 'physical',
					'reference'             => '123456789',
					'name'                  => 'Test product',
					'quantity'              => 1,
					'unit_price'            => $this->dummyAmount,
					'tax_rate'              => 0,
					'total_amount'          => $this->dummyAmount,
					'total_tax_amount'      => 0,
					'total_discount_amount' => 0
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

		if(count($ko['order_lines']) < 1)
			return false;

		$orderId = false;
		if(isset($ko['merchant_reference2']) && strlen($ko['merchant_reference2']) > 0){
			$mo = Mage::getModel('sales/order')->load($ko['merchant_reference1'], 'increment_id');
			if($mo->getId())
				$orderId = $ko['merchant_reference1'];
		}

		if(!$orderId){
			$quote = Mage::getModel('sales/quote')->load($ko['merchant_reference1']);
			$quote->reserveOrderId();
			$quote->save();
			$orderId = $quote->getReservedOrderId();
		}

		$result = new Varien_Object();
		$result->setOrderId($orderId);
		$result->setTotalInclTax($ko['order_amount'] / 100);
		$result->setTotalTaxAmount($ko['order_tax_amount'] / 100);
		$result->setBillingCity($ko['billing_address']['city']);
		$result->setBillingCountry($ko['billing_address']['country']);
		$result->setCurrency(strtoupper($ko['purchase_currency']));
		$result->setShippingFee(0);
		$items = array();
		foreach($ko['order_lines'] as $p){
			if($p['type'] == 'shipping_fee'){
				$result->setShippingFee($p['total_amount'] / 100);
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