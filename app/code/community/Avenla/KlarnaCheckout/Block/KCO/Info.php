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
class Avenla_KlarnaCheckout_Block_KCO_Info extends Mage_Payment_Block_Info
{
	protected function _toHtml()
	{
		$this->setTemplate('KCO/info.phtml');
		$helper = Mage::helper("klarnaCheckout");

		$payment = $this->getMethod();
		$order = $this->getInfo()->getOrder();
		$orderStore = $order->getStore()->getId();
		$klarnaOrderId = $this->getInfo()->getAdditionalInformation(Avenla_KlarnaCheckout_Model_Payment_Abstract::ADDITIONAL_FIELD_KLARNA_ORDER_ID);

		$this->assign('orderStore', $orderStore);
		$this->assign('orderState', $order->getState());

		$kco = $payment->getOrderModel();
		$kco->useConfigForStore($orderStore);
		if($klarnaOrderId){
			try{
				$this->assign('klarnaInfo', $kco->getOrderInformation($klarnaOrderId, $this->getInfo()));
			}
			catch(Exception $e){
				Mage::helper('klarnaCheckout')->logException($e);
			}
		}

		return parent::_toHtml();
	}
}