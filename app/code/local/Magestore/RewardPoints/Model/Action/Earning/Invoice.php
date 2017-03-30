<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Action Earn Point for Order
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Action_Earning_Invoice
    extends Magestore_RewardPoints_Model_Action_Abstract
    implements Magestore_RewardPoints_Model_Action_Interface
{
    /**
     * Calculate and return point amount that customer earned from order
     *
     * @return int
     */
    public function getPointAmount()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
            $isInvoice = true;
        } else {
            $order = $invoice;
            $isInvoice = false;
        }

        $maxEarn = $order->getRewardpointsEarn();

        $cancelPoint = 0;
        foreach ($order->getAllItems() as $item) {
            $itemPoint = (int)$item->getRewardpointsEarn();
            $cancelPoint += $itemPoint * ($item->getQtyCanceled() + $item->getQtyRefunded()) / $item->getQtyOrdered();
        }
        $maxEarn = $maxEarn - floor($cancelPoint);

        $maxEarn -= (int)Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action', 'earning_invoice')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn <= 0) {
            return 0;
        }

        if (!$isInvoice) {
            return (int)$maxEarn;
        }
        return $invoice->getRewardpointsEarn();
        // calculate earning points depend on invoice
//        $earnPoint = 0;
//        $isLastInvoice = true;
//        foreach ($invoice->getAllItems() as $item) {
//            $orderItem = $item->getOrderItem();
//            if ($orderItem->isDummy()) {
//                continue;
//            }
//            if ($orderItem->getQtyToInvoice() > 0.0001) {
//                $isLastInvoice = false;
//            }
//            $itemPoint  = (int)$orderItem->getRewardpointsEarn();
//            $itemPoint  = $itemPoint * $item->getQty() / $orderItem->getQtyOrdered();
//            $earnPoint += floor($itemPoint);
//        }
//        if ($isLastInvoice || $earnPoint > $maxEarn) {
//            $earnPoint = $maxEarn;
//        }
//        return (int)$earnPoint;
    }

    /**
     * get Label for this action, this is the reason to change
     * customer reward points balance
     *
     * @return string
     */
    public function getActionLabel()
    {
        return Mage::helper('rewardpoints')->__('Earn points for purchasing order');
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get Text Title for this action, used when create an transaction
     *
     * @return string
     */
    public function getTitle()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
        } else {
            $order = $invoice;
        }
        return Mage::helper('rewardpoints')->__('Earn points for purchasing order #%s', $order->getIncrementId());
    }

    /**
     * get HTML Title for action depend on current transaction
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return string
     */
    public function getTitleHtml($transaction = null)
    {
        if (is_null($transaction)) {
            return $this->getTitle();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $editUrl = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $transaction->getOrderId()));
        } else {
            $editUrl = Mage::getUrl('sales/order/view', array('order_id' => $transaction->getOrderId()));
        }
        return Mage::helper('rewardpoints')->__(
            'Earn points for purchasing order %s',
            '<a href="' . $editUrl . '" title="'
            . Mage::helper('rewardpoints')->__('View Order')
            . '">#' . $transaction->getOrderIncrementId() . '</a>'
        );
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     *
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction()
    {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
        } else {
            $order = $invoice;
        }

        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id' => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_base_amount' => $order->getBaseGrandTotal(),
            'order_amount' => $order->getGrandTotal(),
            'base_discount' => $invoice->getRewardpointsBaseDiscount(),
            'discount' => $invoice->getRewardpointsDiscount(),
            'store_id' => $order->getStoreId(),
        );

        // Check if transaction need to hold
        $holdDays = (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS,
            $order->getStoreId()
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE,
            $order->getStoreId()
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);

        // Set invoice id for current earning
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $transactionData['extra_content'] = $invoice->getIncrementId();
        }

        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

}
