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
 * Rewardpoints Total Point Earn/Spend Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Totals_Creditmemo_Rewards extends Mage_Adminhtml_Block_Template
{
    /**
     * get current creditmemo
     * 
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        return Mage::registry('current_creditmemo');
    }
    
    /**
     * check admin can refund point that customer spent
     * 
     * @return boolean
     */
    public function canRefundPoints()
    {
        if ($this->getCreditmemo()->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        if ($this->getMaxPointRefund()) {
            return true;
        }
        return false;
    }
    
    /**
     * max point that admin can refund to customer
     * 
     * @return int
     */
    public function getMaxPointRefund()
    {
        if ($this->hasData('max_point_refund')) {
            return $this->getData('max_point_refund');
        }
        $maxPointRefund = 0;
        if ($creditmemo = $this->getCreditmemo()) {
            $order = $creditmemo->getOrder();
            
            $maxPoint = $order->getRewardpointsSpent();
            $maxPointRefund = $maxPoint - (int)Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'spending_creditmemo')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFieldTotal();
            if ($creditmemo->getRewardpointsDiscount()) {
                $currentPoint = ceil($maxPoint * $creditmemo->getRewardpointsDiscount() / $order->getRewardpointsDiscount());
            } else {
                $currentPoint = 0;
            }
            $this->setData('total_point', $maxPoint);
            $this->setData('current_point', min($currentPoint, $maxPointRefund));
        }
        $this->setData('max_point_refund', $maxPointRefund);
        return $this->getData('max_point_refund');
    }
    
    /**
     * get current refund points for this credit memo
     * 
     * @return int
     */
    public function getCurrentPoint()
    {
        if (!$this->hasData('max_point_refund')) {
            $this->getMaxPointRefund();
        }
        return (int)$this->getData('current_point');
    }
    
    /**
     * check admin can refund earned point of customer
     * (deduct point from customer points balance)
     * 
     * @return boolean
     */
    public function canRefundEarnedPoints()
    {
        if ($this->getCreditmemo()->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        if ($this->getMaxEarnedRefund()) {
            return true;
        }
        return false;
    }
    
    /**
     * get max point can deduct from customer balance
     * 
     * @return int
     */
    public function getMaxEarnedRefund()
    {
        if (!$this->hasData('max_earned_refund')) {
            $maxEarnedRefund = 0;
            $earnPoint = 0;
            if ($creditmemo = $this->getCreditmemo()) {
                $order = $creditmemo->getOrder();
                
                $maxEarnedRefund  = (int)Mage::getResourceModel('rewardpoints/transaction_collection')
                    ->addFieldToFilter('action', 'earning_invoice')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getRewardpointsEarn()) {
                    $maxEarnedRefund = $order->getRewardpointsEarn();
                }
                $maxEarnedRefund += (int)Mage::getResourceModel('rewardpoints/transaction_collection')
                    ->addFieldToFilter('action', 'earning_creditmemo')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFieldTotal();
                if ($maxEarnedRefund > $order->getRewardpointsEarn()) {
                    $maxEarnedRefund = $order->getRewardpointsEarn();
                }
                
                foreach ($creditmemo->getAllItems() as $item) {
                    $orderItem = $item->getOrderItem();
                    if ($orderItem->isDummy()) {
                        continue;
                    }
                    $itemPoint  = (int)$orderItem->getRewardpointsEarn();
                    $itemPoint  = $itemPoint * $item->getQty() / $orderItem->getQtyOrdered();
                    $earnPoint += floor($itemPoint);
                }
                // Hiepdd add shipping earned points
                if($order->getCreditmemosCollection()->getSize() == 0) {
                    $earnPoint += Mage::helper('rewardpoints/calculation_earning')->getShippingEarningPoints($order);                    
                }
                // end
              //  if(!$this->isLast($creditmemo) && $maxEarnedRefund >= $earnPoint) 
                    $maxEarnedRefund = $earnPoint;             
            }
            $this->setData('max_earned_refund', $maxEarnedRefund);
        }
        return $this->getData('max_earned_refund');
    }

    /**
     * @param $creditmemo
     * @return bool
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $order
     */
    public function isFirstCreditmemo($order){
        foreach ($order->getCreditmemoCollection() as $creditmemo){
            
        }
    }
}
