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

class Avenla_KlarnaCheckout_Model_Api extends Mage_Core_Model_Abstract
{
	protected  	$klarna;
	private 	$config;
	private 	$helper;

	public function __construct($payment = null)
	{
		$this->klarna = new Klarna();
		$this->helper = Mage::helper('klarnaCheckout');

		if($payment){
			$this->config = $payment->getConfig();
		}
		else{
			$this->config = Mage::getModel('klarnaCheckout/config');
		}

		$defaultCountry = $this->config->getDefaultCountry();
		$locale = $this->klarna->getLocale($defaultCountry);

		try{
			$this->klarna->config(
				$this->config->getKlarnaEid(),
				$this->config->getKlarnaSharedSecret(),
				$locale['country'],
				$locale['language'],
				$locale['currency'],
				$this->config->isLive() ?  Klarna::LIVE : Klarna::BETA,
				'json',
				Mage::getBaseDir('lib').'/KlarnaCheckout/pclasses/'.$this->config->getKlarnaEid().'_pclass_'.$locale['country'].'.json',
				true,
				true
			);
		}
		catch (Exception $e) {
			$this->helper->logException($e);
		}
	}

	/**
	 * Do partial activation
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   array $qtys
	 * @return  bool
	 */
	public function activatePartialReservation($mo, $qtys)
	{
		if(!$this->config->activatePartial())
			return false;

		foreach($qtys as $key => $qty){
			$sku = Mage::getModel('sales/order_item')->load($key)->getSku();
			$sku = iconv('UTF-8', 'ISO-8859-1', $sku);
			$this->klarna->addArtNo($qty, $sku);
		}

		$klarnainvoices = $this->helper->getKlarnaInvoices($mo);
		if(empty($klarnainvoices) && $mo->getShippingAmount() > 0)
			$this->klarna->addArtNo(1, 'shipping_fee');

		try{
			$rno = $this->helper->getReservationNumber($mo);
			$result = $this->klarna->activate($rno);

			$mo = $this->createMageInvoice($mo, $result, $qtys);
			$mo = $this->checkExpiration($mo);
			$mo->save();

			return true;
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			$mo->addStatusHistoryComment(
				$this->helper->__('Failed to activate reservation %s', $rno) ."(" . $e->getMessage() . ")"
			);
			$mo->save();

			return false;
		}
	}

	/**
	 * Do full activation
	 *
	 * @param   Magento_Sales_Order $mo
	 * @return  bool
	 */
	public function activateFullReservation($mo)
	{
		if($rno = $this->helper->getReservationNumber($mo)){
			try{
				$result = $this->klarna->activate($rno);
				$mo = $this->createMageInvoice($mo, $result);
				$mo = $this->checkExpiration($mo);
				$mo->save();

				return true;
			}
			catch(Exception $e) {
				$this->helper->logException($e);
				$mo->addStatusHistoryComment(
					$this->helper->__('Failed to activate reservation %s', $rno) ."(" . $e->getMessage() . ")"
				);
				$mo->save();

				return false;
			}
		}
		return false;
	}

	/**
	 * Create invoice for Magento order
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   array $result
	 * @param   array $qtys|null
	 * @return  Magento_Sales_Order
	 */
	private function createMageInvoice($mo, $result, $qtys = null)
	{
		$invoice = Mage::getModel('sales/service_order', $mo)->prepareInvoice($qtys);

		if (!$invoice->getTotalQty())
			Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products'));

		if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION) != null)
			Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION);

		Mage::register(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION, $result[1]);
		$amount = $invoice->getBaseGrandTotal();
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		$invoice->register();

		$mo->getPayment()->setTransactionId($result[1]);

		Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();

		$invoice->save();

		$klarnainvoices = $this->helper->getKlarnaInvoices($mo);
		$klarnainvoices[$invoice->getId()] = array(
			'invoice'   => $result[1],
			'risk'      => $result[0]
		);


		$mo->addStatusHistoryComment($this->helper->__('Created Klarna invoice %s', $result[1]));
		$mo = $this->helper->saveKlarnaInvoices($mo, $klarnainvoices);

		return $mo;
	}

	/**
	 * Check reservation expiration
	 *
	 * @param   Magento_Sales_Order $mo
	 * @return  Magento_Sales_Order
	 */
	private function checkExpiration($mo)
	{
		if(!$this->helper->isKcoOrder($mo))
			return $mo;

		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);

		$kco = $mo->getPayment()->getMethodInstance()->getOrderModel();
		$kco->useConfigForStore($mo->getStore()->getId());
		$info = $kco->getOrderInformation($klarnaOrderId);
		$expr = $info->getExpiration();

		if(new Zend_Date($expr) < new Zend_Date()){
			$formattedExpiration = Mage::helper('core')->formatDate($expr,'medium', false);

			$mo->getPayment()->setAdditionalInformation(
				Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_MESSAGE,
				'Reservation was activated after expiration (expired '.$formattedExpiration.')'
			);
		}
		return $mo;
	}

	/**
	 * Activate reservation from Magento invoice
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   Magento_Sales_Order_Invoice $invoice
	 * @return  bool
	 */
	public function activateFromInvoice($mo, $invoice)
	{
		if($rno = $this->helper->getReservationNumber($mo)){
			if (abs($mo->getTotalDue() - $invoice->getGrandTotal()) > .0001){
				foreach($invoice->getAllItems() as $item){
					if(!$item->getOrderItem()->isDummy())
						$this->klarna->addArtNo($item->getQty(), $item->getSku());
				}
				if($invoice->getShippingAmount() > 0)
					$this->klarna->addArtNo(1, 'shipping_fee');
			}

			try{
				$result = $this->klarna->activate($rno);

				if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_INVOICE) != null)
					Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_INVOICE);

				Mage::register(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_INVOICE, $result[1]);

				$klarnainvoices = $this->helper->getKlarnaInvoices($mo);
				$klarnainvoices[$result[1]] = array(
					'invoice'   => $result[1],
					'risk'      => $result[0]
				);

				if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION) != null)
					Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION);

				Mage::register(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION, $result[1]);

				$mo = $this->helper->saveKlarnaInvoices($mo, $klarnainvoices);
				$mo = $this->checkExpiration($mo);

				$mo->save();

				return true;
			}
			catch(Exception $e) {
				$this->helper->logException($e);
				$mo->addStatusHistoryComment(
					$this->helper->__('Failed to activate reservation %s', $rno) ."(" . $e->getMessage() . ")"
				);
				$mo->save();

				return false;
			}
		}
	}

	/**
	 * Credit Klarna invoice
	 *
	 * @param   string $invoiceNo
	 * @return  bool
	 */
	public function creditInvoice($invoiceNo)
	{
		try {
			$result = $this->klarna->creditInvoice($invoiceNo);
			return $this->emailInvoice($result);
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			return false;
		}
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
	public function creditPart($invoiceNo, $products, $adjustment = null, $adjustmentTaxRate = null)
	{
		if($adjustment){
			$this->klarna->addArticle(
				1,
				'Adjustment',
				$this->helper->__('Adjustment fee'),
				$adjustment,
				$adjustmentTaxRate,
				0,
				KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING
			);
		}

		foreach($products as $key => $p){
			$this->klarna->addArtNo($p, $key);
		}

		try {
			$result = $this->klarna->creditPart($invoiceNo);
			return $this->emailInvoice($result);
		}
		catch(Exception $e) {
			$this->logException($e);
			return false;
		}
	}

	/**
	 * Return amount from Klarna invoice
	 *
	 * @param   string $invoiceNo
	 * @param   float $amount
	 * @param  	float $vat|0
	 * @return  bool
	 */
	public function returnAmount($invoiceNo, $amount, $vat = 0)
	{
		try {
			$result = $this->klarna->returnAmount(
				$invoiceNo,
				$amount,
				$vat,
				KlarnaFlags::INC_VAT
			);
			return $this->emailInvoice($result);
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			return false;
		}
	}

	/**
	 * Cancel Klarna reservation
	 *
	 * @param   string $rno
	 * @param   Mage_Sales_Model_Order $mo|null
	 * @return  bool
	 */
	public function cancelReservation($rno, $mo = null)
	{
		try {
			$result = $this->klarna->cancelReservation($rno);

			if($mo){
				$mo->addStatusHistoryComment(
					$this->helper->__('Klarna reservation <b>%s</b> was canceled.', $rno)
				);
			}
			return true;
		}
		catch(Exception $e){
			$this->helper->logException($e);
			if($mo){
				$mo->addStatusHistoryComment(
					$this->helper->__('Failed to cancel Klarna reservation <b>%s</b>.(%s - %s)',
						$rno,
						$e->getMessage(),
						$e->getCode()
					)
				);
			}

			return false;
		}
	}

	/**
	 * Send invoice e-mail
	 *
	 * @param   string $invoiceNo
	 * @return  bool
	 */
	public function emailInvoice($invoiceNo)
	{
		try {
			$result = $this->klarna->emailInvoice($invoiceNo);
			return true;
		} catch(Exception $e) {
			$this->helper->logException($e);
			return false;
		}
	}

	/**
	 * Get cheapest monthly price
	 *
	 * @param   float $price
	 * @return  string | bool
	 */
	public function getMonthlyPrice($price)
	{
		if($pclass = $this->klarna->getCheapestPClass($price, KlarnaFlags::PRODUCT_PAGE)){
			$value = KlarnaCalc::calc_monthly_cost(
				$price,
				$pclass,
				KlarnaFlags::PRODUCT_PAGE
			);

			$country = $pclass->getCountry();
			$currency = KlarnaCurrency::getCode(KlarnaCountry::getCurrency($pclass->getCountry()));

			try{
				$currency = Mage::app()->getLocale()->currency(strtoupper($currency))->getSymbol();
			}
			catch(Exception $e){
				$this->helper->logException($e);
			}

			return $value . $currency;
		}

		return false;
	}

	/**
	 * Update Klarna PClasses
	 *
	 * @return string
	 */
	public function updatePClasses()
	{
		try {
			$this->klarna->fetchPClasses();
			return $this->helper->__('PClasses updated successfully');
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			return $e->getMessage();
		}
	}
}