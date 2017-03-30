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
class Avenla_KlarnaCheckout_Adminhtml_KlarnaCheckout_KCOController extends Mage_Adminhtml_Controller_Action
{
	/**
	 *	Update Klarna PClasses
	 *
	 */
	public function updatePClassesAction()
	{
		$result = Mage::getModel('klarnaCheckout/api')->updatePClasses();
		Mage::app()->getResponse()->setBody($result);
	}

	/**
	 *	Activate Klarna reservation
	 *
	 */
	public function activateReservationAction()
	{
		try {
			if($orderId = $this->getRequest()->getParam('order_id')){
				$order = Mage::getModel('sales/order')->load($orderId);
				if(Mage::helper('klarnaCheckout')->isKcoOrder($order)){
					$method = $order->getPayment()->getMethodInstance()->setStore($order->getStore());
					$method->activateFullReservation($order);
				}
				else{
					$this->_getSession()->addError($this->__('Order was not placed with Klarna Checkout'));
				}
			}
			else{
				$this->_getSession()->addError($this->__('No order found'));
			}
		}
		catch(Exception $e) {
			Mage::helper('klarnaCheckout')->logException($e);
			$this->_getSession()->addError($this->__($e->getMessage()));
		}

		$this->_redirectReferer();
	}
}