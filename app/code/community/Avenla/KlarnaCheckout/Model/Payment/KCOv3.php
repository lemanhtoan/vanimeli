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

class Avenla_KlarnaCheckout_Model_Payment_KCOv3 extends Avenla_KlarnaCheckout_Model_Payment_KCO
{
	protected $_code = 'klarnaCheckout_payment_v3';

	/**
	 * Check if Klarna Checkout is available
	 *
	 * @param   Mage_Sales_Model_Quote|null $quote
	 * @return  bool
	 */
	public function isAvailable($quote = null)
	{
		$api = $this->getConfig()->getApiVersion();
		if($api == Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV2)
			return false;

		return parent::isAvailable($quote);
	}

	/**
	 *  Get order model
	 *
	 *  @return Avenla_KlarnaCheckout_Model_Order_Kcov3
	 */
	public function getOrderModel()
	{
		$ko = Mage::getModel('klarnaCheckout/order_Kcov3');
		$ko->useConfigForStore($this->getStore());
		return $ko;
	}

	/**
	 * Cancel Klarna reservation
	 *
	 * @param 	Mage_Sales_Model_Order $mo
	 * @return 	bool
	 */
	public function cancelReservation($mo)
	{
		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);
		$ko = $this->getOrderModel();
		$order = $ko->getManagement($klarnaOrderId);
		$rno = $order['klarna_reference'];
		$this->helper->prepareKcoSave();

		if(!$order['remaining_authorized_amount'])
			return false;

		try {
			if(!empty($order['captures'])){
				$response = $order->releaseRemainingAuthorization();
			}
			else{
				$response = $order->cancel();
			}

			$mo->addStatusHistoryComment(
				$this->helper->__('Klarna reservation <b>%s</b> was canceled.', $rno)
			);
			$mo->save();
			$this->helper->finishKcoSave();
			return true;
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			$mo->addStatusHistoryComment(
				$this->helper->__('Failed to cancel Klarna reservation <b>%s</b>.(%s - %s)',
					$rno,
					$e->getMessage(),
					$e->getCode()
				)
			);
			$mo->save();
			$this->helper->finishKcoSave();

			return false;
		}
    }

	/**
	 * Activate reservation from Magento invoice
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   Magento_Sales_Order_Invoice $invoice
	 * @return  bool
	 */
	protected function activateFromInvoice($mo, $invoice)
	{
		if(!$this->helper->isKcoOrder($mo))
			return $this;

		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);
		$ko = $this->getOrderModel();
		$order = $ko->getManagement($klarnaOrderId);
		$request = new Varien_Object();

		if(!$order['remaining_authorized_amount'])
			return false;

		if (abs($mo->getTotalDue() - $invoice->getGrandTotal()) > .0001){
			$capturedAmount = $this->helper->formatPriceForKlarna($invoice->getBaseGrandTotal());
			$request->setCapturedAmount($capturedAmount);
			$request->setDescription($this->helper->__("Partial capture from invoice %s", $invoice->getIncrementId()));
		}
		else{
			$request->setCapturedAmount($order['remaining_authorized_amount']);
			$request->setDescription($this->helper->__('Order complete after invoice'));
			$request->setOrderLines($order['order_lines']);
		}

		try{
			$response = $order->createCapture($request->getData());
			$location = $response->getLocation();
			$captureId = end(explode("/", $location));
			$capture = $order->fetchCapture($captureId);

			if(!$capture->getId())
				Mage::throwException("Capture not found");

			$amount = $capture["captured_amount"];
			$result = array('', $captureId);

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
		catch(Exception $e){
			$this->helper->logException($e);
			$mo->addStatusHistoryComment(
				$this->helper->__('Failed to activate reservation (' . $e->getMessage() . ')')
			);
			$mo->save();

			return false;
		}
	}

	/**
	 * Do partial activation
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   array $qtys
	 * @return  bool
	 */
	protected function activatePartialReservation($mo, $qtys)
	{
		if(!$this->getConfig()->activatePartial())
			return false;

		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);
		$ko = $this->getOrderModel();
		$order = $ko->getManagement($klarnaOrderId);

		if(!$order['remaining_authorized_amount'])
			return false;

		$invoice = Mage::getModel('sales/service_order', $mo)->prepareInvoice($qtys);
		if (!$invoice->getTotalQty())
			Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products'));

		$capturedAmount = $this->helper->formatPriceForKlarna($invoice->getBaseGrandTotal());

		$request = new Varien_Object();
		$request->setCapturedAmount($capturedAmount);
		$request->setDescription($this->helper->__("Partial capture"));

		try{
			$response = $order->createCapture($request->getData());
			$location = $response->getLocation();
			$captureId = end(explode("/", $location));
			$capture = $order->fetchCapture($captureId);

			if(!$capture->getId())
				Mage::throwException("Capture not found");

			$amount = $capture["captured_amount"];
			$result = array('', $captureId);

			if(Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION) != null)
				Mage::unregister(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION);

			Mage::register(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_TRANSACTION, $result[1]);

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
				'invoice'   => $result[1]
			);

			$mo->addStatusHistoryComment($this->helper->__('Created Klarna invoice %s', $result[1]));
			$mo = $this->helper->saveKlarnaInvoices($mo, $klarnainvoices);

			$mo->save();

			return true;
		}
		catch(Exception $e) {
			$this->helper->logException($e);
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
		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);
		$ko = $this->getOrderModel();
		$order = $ko->getManagement($klarnaOrderId);

		if(!$order['remaining_authorized_amount'])
			return false;

		$request = new Varien_Object();
		$request->setCapturedAmount($order['remaining_authorized_amount']);
		$request->setDescription($this->helper->__('Order complete'));
		$request->setOrderLines($order['order_lines']);

		try{
			$response = $order->createCapture($request->getData());
			$location = $response->getLocation();
			$captureId = end(explode("/", $location));
			$capture = $order->fetchCapture($captureId);

			if(!$capture->getId())
				Mage::throwException("Capture not found");

			$amount = $capture["captured_amount"];
			$result = array('', $captureId);
			$mo = $this->createMageInvoice($mo, $result);
			$mo->save();

			return true;
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			return false;
		}
	}

	/**
	 * Refund specified amount from invoice
	 *
	 * @param   Varien_Object $payment
	 * @param   float $amount
	 * @return  Avenla_KlarnaCheckout_Model_KCOv3
	 */
    public function refund(Varien_Object $payment, $amount)
    {
		$mo = $payment->getOrder();
		$this->setStore($mo->getStore()->getStoreId());

		if(!$this->helper->isKcoOrder($mo))
			return $this;

		$creditmemo = $payment->getCreditmemo();
		$invoice = $creditmemo->getInvoice();
		$captureId = $invoice->getTransactionId();
		$klarnaOrderId = $mo->getPayment()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);
		$ko = $this->getOrderModel();
		$order = $ko->getManagement($klarnaOrderId);
		$capture = $order->fetchCapture($captureId);
		$products = array();

		foreach ($creditmemo->getAllItems() as $item){
			$products[] = $item->getSku();
		}

		$orderLines = array();
		foreach($capture['order_lines'] as $oLine){
			if(in_array($oLine['reference'], $products))
				$orderLines[] = $oLine;
		}

		$description = $this->helper->__("Refunded capture %s", $capture['klarna_reference']);
		$refundedAmount = $this->helper->formatPriceForKlarna($creditmemo->getBaseGrandTotal());
		$request = new Varien_Object();
		$request->setRefundedAmount($refundedAmount);
		$request->setDescription($description);
		if(!empty($orderLines))
			$request->setOrderLines($orderLines);

		try{
			$response = $order->refund($request->getData());
			$mo->addStatusHistoryComment($description);
			$mo->save();

			return $this;
		}
		catch(Exception $e) {
			$this->helper->logException($e);
			return false;
		}

		return $this;
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
			'invoice'   => $result[1]
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
}