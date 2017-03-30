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

class Avenla_KlarnaCheckout_Model_Payment_KCO extends Avenla_KlarnaCheckout_Model_Payment_Abstract
{
	protected $_code                    = 'klarnaCheckout_payment';
	protected $_formBlockType           = 'klarnaCheckout/KCO_form';
	protected $_infoBlockType           = 'klarnaCheckout/KCO_info';
	protected $_canUseCheckout          = false;

	/**
	 * Get Config model
	 *
	 * @return  Avenla_KlarnaCheckout_Model_Config
	 */
	public function getConfig()
	{
		$config = Mage::getSingleton('klarnaCheckout/config');
		$config->setStore($this->getStore());
		return $config;
	}

	/**
	 * Check if Klarna Checkout is available
	 *
	 * @param   Mage_Sales_Model_Quote|null $quote
	 * @return  bool
	 */
	public function isAvailable($quote = null)
	{
		if($quote == null)
			$quote = Mage::getSingleton('checkout/session')->getQuote();

		$result = (parent::isAvailable($quote) && $this->getConfig()->getLicenseAgreement() && count($quote->getAllVisibleItems()) >= 1);
		if(!$result){
			Mage::getSingleton('core/session')->setKCOMessage("Klarna Checkout is not available");
			return false;
		}

		if(in_array($quote->getShippingAddress()->getShippingMethod(), $this->getConfig()->getDisallowedShippingMethods())){
			Mage::getSingleton('core/session')->setKCOMessage("Klarna Checkout is not available with selected shipping method");
			return false;
		}

		if(!Mage::getSingleton('customer/session')->isLoggedIn() && !Mage::helper('checkout')->isAllowedGuestCheckout($quote)){
			Mage::getSingleton('core/session')->setKCOMessage("Please login to use Klarna Checkout");
			Mage::getSingleton('core/session')->setKCORequireLogin(true);
			return false;
		}

		if (!$quote->validateMinimumAmount()){
			$minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
			->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

			$msg = Mage::getStoreConfig('sales/minimum_order/description')
				? Mage::getStoreConfig('sales/minimum_order/description')
				: Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

			Mage::getSingleton('core/session')->setKCOMessage($msg);
			return false;
		}

		if(!$this->helper->getConnectionStatus($this, $quote)){
			Mage::getSingleton('core/session')->setKCOMessage("Klarna Checkout is not available");
			return false;
		}

		return true;
	}

	/**
	 *  Get order model
	 *
	 *  @return Avenla_KlarnaCheckout_Model_Order_Kcov2
	 */
	public function getOrderModel()
	{
		$ko = Mage::getModel('klarnaCheckout/order_Kcov2');
		$ko->useConfigForStore($this->getStore());
		return $ko;
	}

	/**
	 *  Cancel reservation
	 *
	 *  @param Magento_Sales_Model_Order $mo
	 *  @return bool
	 */
	public function cancelReservation($mo)
	{
		$rno = $this->helper->getReservationNumber($mo);
		return Mage::getModel('klarnaCheckout/api', $this)
			->cancelReservation($rno, $mo);
	}

	/**
	 *  Activate reservation from invoice
	 *
	 *  @param Magento_Sales_Model_Order $mo
	 *  @param Magento_Sales_Model_Order_Invoice $invoice
	 *  @return bool
	 */
	protected function activateFromInvoice($mo, $invoice)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->activateFromInvoice($mo, $invoice);
	}

	/**
	 *  Activate partial reservation
	 *
	 *  @param Magento_Sales_Model_Order $mo
	 *  @param array $qtys
	 *  @return bool
	 */
	protected function activatePartialReservation($mo, $qtys)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->activatePartialReservation($mo, $qtys);
	}

	/**
	 *  Activate full reservation
	 *
	 *  @param Magento_Sales_Model_Order $mo
	 *  @return bool
	 */
	public function activateFullReservation($mo)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->activateFullReservation($mo);
	}

	/**
	 *  Credit invoice
	 *
	 *  @param string $invoiceNo
	 *  @return bool
	 */
	protected function creditInvoice($invoiceNo)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->creditInvoice($invoiceNo);
	}

	/**
	 * Credit Klarna invoice partially
	 *
	 * @param   string  $invoiceNo
	 * @param   array   $products
	 * @param   float   $adjustment |null
	 * @param   float   $adjustmentTaxRate | null
	 * @return  bool
	 */
	protected function creditPart($invoiceNo, $products, $adjustment = null, $adjustmentTaxRate = null)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->creditPart($invoiceNo, $products, $adjustment, $adjustmentTaxRate);
	}

	/**
	 * Return amount from Klarna invoice
	 *
	 * @param   string $invoiceNo
	 * @param   float $amount
	 * @param   float $vat|0
	 * @return  bool
	 */
	protected function returnAmount($invoiceNo, $amount, $vat = 0)
	{
		return Mage::getModel('klarnaCheckout/api', $this)
			->returnAmount($invoiceNo, $amount, $vat);
	}

	/**
	 * Capture payment
	 *
	 * @param   Varien_Object $payment
	 * @param   float $amount
	 * @return  Avenla_KlarnaCheckout_Model_KCO
	 */
	public function capture(Varien_Object $payment, $amount)
	{
		$order = $payment->getOrder();
		$this->setStore($order->getStore()->getStoreId());

		if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION) == null){
			foreach ($order->getInvoiceCollection() as $invoice) {
				if($invoice->getId() == null)
		    		$inv = $invoice;
			}

			if(isset($inv))
				$this->activateFromInvoice($order, $inv);
		}

		if($id = Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION)){
			$payment->setTransactionId($id);
			$payment->setIsTransactionClosed(1);
			Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION);
		}

		return $this;
	}

	/**
	 * Register KCO save before redund
	 *
	 * @param   $invoice
	 * @param   Varien_Object $payment
	 * @return  Avenla_KlarnaCheckout_Model_KCO
	 */
	public function processBeforeRefund($invoice, $payment)
	{
		$this->helper->prepareKcoSave();
		return $this;
	}

	/**
	 * Unregister KCO save after redund
	 *
	 * @param   $creditmemo
	 * @param   Varien_Object $payment
	 * @return  Avenla_KlarnaCheckout_Model_KCO
	 */
	public function processCreditmemo($creditmemo, $payment)
	{
		$this->helper->finishKcoSave();
		return $this;
	}

	/**
	 * Refund specified amount from invoice
	 *
	 * @param   Varien_Object $payment
	 * @param   float $amount
	 * @return  Avenla_KlarnaCheckout_Model_KCO
	 */
	public function refund(Varien_Object $payment, $amount)
	{
		$order = $payment->getOrder();
		$this->setStore($order->getStore()->getStoreId());

		if(!$this->helper->isKcoOrder($order))
			return $this;

		$creditmemo = $payment->getCreditmemo();
		$invoice = $creditmemo->getInvoice();
		$klarnaInvoice = $invoice->getTransactionId();

		$products = array();
		$result = array();
		$totalRefund = false;

		if (abs($invoice->getGrandTotal() - $creditmemo->getGrandTotal()) < .0001)
			$totalRefund = true;

		foreach ($creditmemo->getAllItems() as $item){
			$invoiceItem = Mage::getResourceModel('sales/order_invoice_item_collection')
				->addAttributeToSelect('*')
				->setInvoiceFilter($invoice->getId())
				->addFieldToFilter('order_item_id', $item->getOrderItemId())
				->getFirstItem();

			$diff = $item->getQty() - $invoiceItem->getQty();

			if($diff > 0)
				$totalRefund = false;

			if($item->getQty() > 0 && !$item->getOrderItem()->isDummy())
				$products[$item->getSku()] = $item->getQty();
		}

		if($totalRefund){
			$result[] = $this->creditInvoice($klarnaInvoice)
				? "Refunded Klarna invoice " . $klarnaInvoice
				: "Failed to refund Klarna invoice " . $klarnaInvoice;
		}
		else{
			$fee = null;
			if($creditmemo->getAdjustment() < 0)
				$fee = abs($creditmemo->getAdjustment());

			if(!empty($products) || $creditmemo->getShippingAmount() > 0){
				if (abs($invoice->getShippingAmount() - $creditmemo->getShippingAmount()) < .0001)
					$products['shipping_fee'] = 1;

				if($fee != null){
					$response = $this->creditPart($klarnaInvoice, $products, $fee, $this->getConfig()->getReturnTaxRate());
				}
				else{
					$response = $this->creditPart($klarnaInvoice, $products);
				}

				if($response){
					$t = "Credited products: ";
					foreach($products as $key => $p){
						$t .= $key."(".$p.") ";
					}
					$result[] = $t;
				}
				else{
					$result[] = "Failed to do partial refund";
				}

				if($creditmemo->getShippingAmount() > 0 && !array_key_exists('shipping_fee', $products)){
					$result[] =  $this->returnAmount($klarnaInvoice, $creditmemo->getShippingAmount(), Mage::helper('klarnaCheckout')->getShippingVatRate())
						? "Refunded amount of " . $creditmemo->getShippingAmount() . " from shipment on Klarna invoice " . $klarnaInvoice
						: "Failed to refund amount of " . $creditmemo->getShippingAmount() . " from shipment on Klarna invoice " . $klarnaInvoice;
				}
			}
			if($creditmemo->getAdjustment() > 0){
				$result[] =  $this->returnAmount($klarnaInvoice, $creditmemo->getAdjustment(), $this->getConfig()->getReturnTaxRate())
					? "Refunded amount of " . $creditmemo->getAdjustment() . " on Klarna invoice " . $klarnaInvoice
					: "Failed to refund amount of " . $creditmemo->getAdjustment() . " on Klarna invoice " . $klarnaInvoice;
			}
		}

		if(!empty($result)) {
			foreach($result as $msg){
				$order->addStatusHistoryComment($msg);
			}
			$order->save();
		}

		return $this;
	}
}