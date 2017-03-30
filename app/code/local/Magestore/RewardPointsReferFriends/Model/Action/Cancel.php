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
 * Action Earn Point for Order
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Action_Cancel extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    /**
     * Calculate and return point amount that customer earned from order
     * 
     * @return int
     */
    public function getPointAmount() {
        $order = $this->getData('action_object');
        return -(int) $order->getRefundEarnedPoints();
    }

    /**
     * get Label for this action, this is the reason to change 
     * customer reward points balance
     * 
     * @return string
     */
    public function getActionLabel() {
        return Mage::helper('rewardpointsreferfriends')->__('Taken back points for refunded order by invited friend');
    }

    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get Text Title for this action, used when create an transaction
     * 
     * @return string
     */
    public function getTitle() {
        $order = $this->getData('action_object');
        return Mage::helper('rewardpointsreferfriends')->__('Taken back points for refunded order #%s by invited friend', $order->getIncrementId());
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
        return Mage::helper('rewardpointsreferfriends')->__(
                        'Taken back points for refunded order %s by invited friend', '<a href="' . $editUrl . '" title="'
                        . Mage::helper('rewardpointsreferfriends')->__('View Order')
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
    public function prepareTransaction() {
        $order = $this->getData('action_object');

        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id' => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_base_amount' => $order->getBaseGrandTotal(),
            'order_amount' => $order->getGrandTotal(),
            'base_discount' => $order->getRewardpointsBaseDiscount(),
            'discount' => $order->getRewardpointsDiscount(),
            'store_id' => $order->getStoreId(),
        );

        // Check all earning transaction is holding
        $earningTransactions = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'referfriends')
                ->addFieldToFilter('order_id', $order->getId());
        $holdingAll = true;
        foreach ($earningTransactions as $transaction) {
            if ($transaction->getStatus() != Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD) {
                $holdingAll = false;
                break;
            }
        }
        if ($holdingAll) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
			$transactionData['creditmemo_holding'] = true;
        } else {
            // Complete holding transaction before refund
            foreach ($earningTransactions as $transaction) {
                if ($transaction->getStatus() != Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD) {
                    continue;
                }
                try {
                    $transaction->completeTransaction();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

}
