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
class Magestore_RewardPoints_Model_Action_Earning_Creditmemo
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
        $creditmemo = $this->getData('action_object');
        return -(int)$creditmemo->getRefundEarnedPoints();
    }
    
    /**
     * get Label for this action, this is the reason to change 
     * customer reward points balance
     * 
     * @return string
     */
    public function getActionLabel()
    {
        return Mage::helper('rewardpoints')->__('Taken back points for refunding order');
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
        $creditmemo = $this->getData('action_object');
        $order      = $creditmemo->getOrder();
        return Mage::helper('rewardpoints')->__('Taken back points for refunding order #%s', $order->getIncrementId());
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
            'Taken back points for refunding order %s',
            '<a href="' . $editUrl .'" title="'
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
        $creditmemo = $this->getData('action_object');
        $order      = $creditmemo->getOrder();
        
        $transactionData = array(
            'status'    => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'order_id'  => $order->getId(),
            'order_increment_id'    => $order->getIncrementId(),
            'order_base_amount'     => $order->getBaseGrandTotal(),
            'order_amount'          => $order->getGrandTotal(),
            'base_discount'         => $creditmemo->getRewardpointsBaseDiscount(),
            'discount'              => $creditmemo->getRewardpointsDiscount(),
            'store_id'      => $order->getStoreId(),
            'extra_content' => $creditmemo->getIncrementId(),
            'creditmemo_transaction' => true
        );
        
        // Check all earning transaction is holding
        $earningTransactions = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action', 'earning_invoice')
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
