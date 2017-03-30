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

class Avenla_KlarnaCheckout_Model_Payment_Abstract extends Mage_Payment_Model_Method_Abstract
{
	const ADDITIONAL_FIELD_KLARNA_RESERVATION   = "klarna_order_reservation";
	const ADDITIONAL_FIELD_KLARNA_ORDER_ID      = "klarna_order_id";
	const ADDITIONAL_FIELD_NEWSLETTER           = "klarna_newsletter";
	const ADDITIONAL_FIELD_VALIDATION_MSG       = "kco_validation_message";
	const ADDITIONAL_FIELD_KLARNA_MESSAGE		= "klarna_message";
	const ADDITIONAL_FIELD_KLARNA_INVOICE		= "klarna_order_invoice";

	const REGISTRY_KEY_INVOICE 		= "kco_invoicekey";
	const REGISTRY_KEY_TRANSACTION 	= "kco_transaction";
	const REGISTRY_KEY_KCO_SAVE		= "kco_save";
	const REQUEST_KLARNA_ORDER 		= 'klarna_order';

	protected $_isGateway               = true;
	protected $_canAuthorize            = true;
	protected $_canCapture              = true;
	protected $_canCapturePartial       = true;
	protected $_canRefund               = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid                 = false;
	protected $_canUseInternal          = false;
	protected $_canUseForMultishipping  = false;
	protected $_order                   = null;

	protected $helper;

	public function __construct()
	{
		$this->helper = Mage::helper('klarnaCheckout');
		parent::__construct();
	}

	/**
	 * Activate Klarna reservation
	 *
	 * @param   Magento_Sales_Order $mo
	 * @param   string $invoiceId|null
	 * @return  bool
	 */
	public function activateReservation($mo, $invoiceId = null)
	{
		$this->helper->prepareKcoSave();

		if($invoiceId != null){
			$result = $this->activateFromInvoice($mo, Mage::getModel('sales/order_invoice')->load($invoiceId));
		}
		else if(false !== $qtys = $this->checkIfPartial($mo)){
			$result = $this->activatePartialReservation($mo, $qtys);
		}
		else{
			$result = $this->activateFullReservation($mo);
		}

		$this->helper->finishKcoSave();

		return $result;
	}

	/**
	 *	Check if activation is partial or full
	 *
	 *	@param   Magento_Sales_Order $mo
	 *	@return  array $qtys|false
	 */
	protected function checkIfPartial($mo)
	{
		$qtys = array();
		$partial = false;

		foreach($mo->getAllVisibleItems() as $item){
			if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
				if($item->isChildrenCalculated()){
					foreach($item->getChildrenItems() as $child){
						$qtys[$child->getId()] = $child->getQtyShipped() - $child->getQtyInvoiced();

						if($child->getQtyOrdered() != $child->getQtyShipped())
						$partial = true;
					}
				}
				else{
					if($item->isDummy()){
						$bundleQtys = array();
						foreach($item->getChildrenItems() as $child){
							$parentCount = 0;
							$bundleCount = $child->getQtyOrdered() / $item->getQtyOrdered();
							$qtyInvoiced = $bundleCount * $item->getQtyInvoiced();
							$diff = $child->getQtyShipped() - $qtyInvoiced;

							if($diff >= $bundleCount)
								$parentCount = floor($bundleCount / $diff);

							$bundleQtys[] = $parentCount;

							if($child->getQtyOrdered() != $child->getQtyShipped())
								$partial = true;
						}

						$qtys[$item->getId()] = min($bundleQtys);
					}
					else{
						$qtys[$item->getId()] = $item->getQtyShipped() - $item->getQtyInvoiced();

						if($item->getQtyShipped() != $item->getQtyOrdered())
							$partial = true;
					}
				}
			}
			else{
				$qtys[$item->getId()] = $item->getQtyShipped() - $item->getQtyInvoiced();

				if($item->getQtyShipped() != $item->getQtyOrdered())
					$partial = true;
			}
		}

		if($partial)
			return $qtys;

		return false;
	}
}