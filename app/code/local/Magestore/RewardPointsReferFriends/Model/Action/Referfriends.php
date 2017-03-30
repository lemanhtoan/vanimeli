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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Action ReferFriends
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Action_Referfriends extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    public function getActionLabel() {
        return Mage::helper('rewardpointsreferfriends')->__('Earn points when refer friends');
    }

    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    public function getPointAmount() {
        $invoice = $this->getData('action_object');

        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
            $isInvoice = true;
        } else {
            $order = $invoice;
            $isInvoice = false;
        }

        return (int) $order->getRewardpointsReferalEarn();
    }

    public function getTitle() {
        $invoice = $this->getData('action_object');
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $order = $invoice->getOrder();
        } else {
            $order = $invoice;
        }
        return Mage::helper('rewardpointsreferfriends')->__('Receive point(s) for a purchase made by your friend %s', ucwords($order->getCustomerFirstname().' '.$order->getCustomerLastname()));
    }

    /**
     * get HTML Title for action depend on current transaction
     * 
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return string
     */
    public function getTitleHtml($transaction = null) {
        if (is_null($transaction)) {
            return $this->getTitle();
        }
        if (Mage::app()->getStore()->isAdmin()) {
            $editUrl = Mage::getUrl('adminhtml/sales_order/view', array('order_id' => $transaction->getOrderId()));
        } else {
            $editUrl = Mage::getUrl('sales/order/view', array('order_id' => $transaction->getOrderId()));
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId($transaction->getOrderIncrementId());
        return Mage::helper('rewardpointsreferfriends')->__('Receive point(s) for a purchase made by your friend %s', ucwords($order->getCustomerFirstname().' '.$order->getCustomerLastname()));
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction() {
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
        $holdDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $order->getStoreId()
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE, $order->getStoreId()
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
