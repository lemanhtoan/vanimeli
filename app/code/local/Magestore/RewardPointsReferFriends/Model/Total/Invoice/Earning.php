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
class Magestore_Rewardpointsreferfriends_Model_Total_Invoice_Earning extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

    public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
        $order = $invoice->getOrder();
        
        if ($this->isLast($invoice)) {
            $invoice->setRewardpointsReferalEarn(true);
        }else {
            $invoice->setRewardpointsReferalEarn(false);
        }
        
        if($order->getRewardpointsInvitedBaseDiscount()<0.0001) return $this;
        
        $invoice->setRewardpointsInvitedDiscount(0);
        $invoice->setRewardpointsInvitedBaseDiscount(0);

        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;
                
        if ($this->isLast($invoice)) {
            $baseTotalDiscountAmount   = $order->getRewardpointsInvitedBaseDiscount();
            $totalDiscountAmount       = $order->getRewardpointsInvitedDiscount();
            foreach ($order->getInvoiceCollection() as $existedInvoice) {
                $baseTotalDiscountAmount   -= $existedInvoice->getRewardpointsInvitedBaseDiscount();
                $totalDiscountAmount       -= $existedInvoice->getRewardpointsInvitedDiscount();
            }
        } else {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                     continue;
                }

                $orderItemDiscount      = (float) $orderItem->getRewardpointsInvitedDiscount();
                $baseOrderItemDiscount  = (float) $orderItem->getRewardpointsInvitedBaseDiscount();
                $orderItemQty       = $orderItem->getQtyOrdered();

                if ($orderItemDiscount && $orderItemQty) {
                    $discount = $invoice->roundPrice($orderItemDiscount / $orderItemQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $invoice->roundPrice($baseOrderItemDiscount / $orderItemQty * $item->getQty(), 'base', true);

                    $item->setRewardpointsInvitedDiscount($discount);
                    $item->setRewardpointsInvitedBaseDiscount($baseDiscount);

                    $totalDiscountAmount += $discount;
                    $baseTotalDiscountAmount += $baseDiscount;
                }
            }
        }
        $invoice->setRewardpointsInvitedDiscount($totalDiscountAmount);
        $invoice->setRewardpointsInvitedBaseDiscount($baseTotalDiscountAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);

        return $this;
    }
    public function isLast($invoice){
        foreach ($invoice->getAllItems() as $item) {
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
}