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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Model Total Earning
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_Model_Total_Creditmemo_Earning extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo) {
        $creditmemo->setRewardpointsInvitedDiscount(0);
        $creditmemo->setRewardpointsInvitedBaseDiscount(0);

        $order = $creditmemo->getOrder();

        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;
        
        if ($this->isLast($creditmemo)) {
            $baseTotalDiscountAmount   = $order->getRewardpointsInvitedBaseDiscount();
            $totalDiscountAmount       = $order->getRewardpointsInvitedDiscount();
            foreach ($order->getCreditmemosCollection() as $existedCreditmemo) {
                if ($baseTotalDiscountAmount > 0.0001) {
                    $baseTotalDiscountAmount   -= $existedCreditmemo->getRewardpointsInvitedBaseDiscount();
                    $totalDiscountAmount       -= $existedCreditmemo->getRewardpointsInvitedDiscount();
                }
            }
        } else {
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();

                if ($orderItem->isDummy()) {
                    continue;
                }

                $orderItemDiscount      = (float) $orderItem->getRewardpointsInvitedDiscount();
                $baseOrderItemDiscount  = (float) $orderItem->getRewardpointsInvitedBaseDiscount();
                $orderItemQty           = $orderItem->getQtyOrdered();

                if ($orderItemDiscount && $orderItemQty) {
                    $discount = $creditmemo->roundPrice(
                        $orderItemDiscount / $orderItemQty * $item->getQty(), 'regular', true
                    );
                    $baseDiscount = $creditmemo->roundPrice(
                        $baseOrderItemDiscount / $orderItemQty * $item->getQty(), 'base', true
                    );

                    $item->setRewardpointsInvitedDiscount($discount);
                    $item->setRewardpointsInvitedBaseDiscount($baseDiscount);

                    $totalDiscountAmount += $discount;
                    $baseTotalDiscountAmount+= $baseDiscount;
                }
            }
        }

        $creditmemo->setRewardpointsInvitedDiscount($totalDiscountAmount);
        $creditmemo->setRewardpointsInvitedBaseDiscount($baseTotalDiscountAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);

        return $this;
    }
    
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }
}