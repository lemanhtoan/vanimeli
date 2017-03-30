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

class Avenla_KlarnaCheckout_Model_Observer
{
	/**
	 * Process order after status change
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function orderStatusChanged($observer)
	{
		if(Mage::helper('klarnaCheckout')->isKcoSave())
			return $this;

		$order = $observer->getEvent()->getOrder();
		$isKcoOrder = Mage::helper('klarnaCheckout')->isKcoOrder($order);

		if(!$isKcoOrder)
			return $this;

		$kco = $order->getPayment()->getMethodInstance()->setStore($order->getStore());
		switch ($order->getState()){
			case Mage_Sales_Model_Order::STATE_COMPLETE:
				if($order->canInvoice()){
					$kco->activateReservation($order);
				}
				else{
					$kco->cancelReservation($order);
				}

				break;
			case Mage_Sales_Model_Order::STATE_CANCELED:
				$kco->cancelReservation($order);
				break;
			default:
				$mixed = false;
				foreach($order->getAllItems() as $item){
					if($item->getQtyShipped() > $item->getQtyInvoiced())
						$mixed = true;
				}

				if($mixed)
					$kco->activateReservation($order);
		}
	}

	/**
	 * Process invoice after save
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function invoiceSaved($observer)
	{
		if(Mage::helper('klarnaCheckout')->isKcoSave())
			return $this;

		if($kcoInvoiceKey = Mage::registry(Avenla_KlarnaCheckout_Model_Payment_Abstract::REGISTRY_KEY_INVOICE)){
			$invoice = $observer->getEvent()->getInvoice();
			$order = $invoice->getOrder();

			if(Mage::helper('klarnaCheckout')->isKcoOrder($order)){
				if(false !== $klarnainvoices = Mage::helper('klarnaCheckout')->getKlarnaInvoices($order)){
					if (!array_key_exists($invoice->getId(), $klarnainvoices)){
						$klarnainvoices[$invoice->getId()] = $klarnainvoices[$kcoInvoiceKey];
						unset($klarnainvoices[$kcoInvoiceKey]);
						$order = Mage::helper('klarnaCheckout')->saveKlarnaInvoices($order, $klarnainvoices);
						Mage::helper('klarnaCheckout')->prepareKcoSave();
						$order->save();
						Mage::helper('klarnaCheckout')->finishKcoSave();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add Klarna link in default Checkout
	 *
	 * @param	Varien_Event_Observer $observer
	 */
	public function insertKlarnaLink($observer)
	{
		$block = $observer->getBlock();
		$isLogged = Mage::helper('customer')->isLoggedIn();

		$kco = Mage::helper('klarnaCheckout')->getKco();

		if(!$kco->getConfig()->isActive())
			return $this;

		if (
			$block->getType() == 'checkout/onepage_login' ||
			($isLogged && $block->getType() == 'checkout/onepage_billing') ||
			($block->getType() == 'checkout/onepage_payment_methods' && $block->getBlockAlias() != 'methods') &&
			$kco->isAvailable()
			)
		{
			$child = clone $block;
			$child->setType('klarnaCheckout/KCO_Link');
			$block->setChild('original', $child);
			$block->setTemplate('KCO/link.phtml');
		}
	}

	/**
	 * Add activate reservation button to admin order view
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function addActivate($observer)
	{
		$block = $observer->getEvent()->getBlock();

		if(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_View'
			&& $block->getRequest()->getControllerName() == 'sales_order')
		{
			$order = $block->getOrder();
			if(!Mage::helper('klarnaCheckout')->isKcoOrder($order))
				return $this;

			$block->addButton('activate_klarna_reservation', array(
				'label'     => Mage::helper('klarnaCheckout')->__('Activate Klarna reservation'),
				'onclick'   => 'setLocation(\'' . $block->getUrl('adminhtml/klarnaCheckout_KCO/activateReservation', array('order_id' => $order->getId())) . '\')',
				'class'     => 'save'
			));
		}
	}

	/**
	 * Add new layout handle if needed
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function layoutLoadBefore($observer)
	{
		if (Mage::getModel('klarnaCheckout/config')->hideDefaultCheckout())
			$observer->getEvent()->getLayout()->getUpdate()->addHandle('kco_only');

		$kcoLayout = Mage::getModel('klarnaCheckout/config')->getKcoLayout();
		if($observer->getAction()->getFullActionName() == "checkout_cart_index" && ($kcoLayout && $kcoLayout != "default"))
			$observer->getEvent()->getLayout()->getUpdate()->addHandle($kcoLayout);
	}
}