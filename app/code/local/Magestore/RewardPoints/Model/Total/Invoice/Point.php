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
 * Rewardpoints Spend for Order by Point Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Total_Invoice_Point extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect total when create Invoice
     * 
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $invoiceCollection = $order->getInvoiceCollection();

        /** Earning point **/
        $earnPoint = 0;
        $maxEarn  = $order->getRewardpointsEarn();
        $maxEarn -= (int)Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action', 'earning_invoice')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($maxEarn >= 0) {
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy())
                    continue;
                $earnPoint += floor((int)$orderItem->getRewardpointsEarn() * $item->getQty() / $orderItem->getQtyOrdered());
            }
            if($invoiceCollection->getSize() == 0) 
                $earnPoint += Mage::helper('rewardpoints/calculation_earning')->getShippingEarningPoints($order);
            if($this->isLast($invoice)) 
                $earnPoint = $maxEarn;
        }
        if($earnPoint > 0)
            $invoice->setRewardpointsEarn($earnPoint);
        /** End earningn point **/
        
        /** Spending point **/
        if ($order->getRewardpointsDiscount() < 0.0001) {
            return ;
        }

        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;        
        $totalDiscountInvoiced     = 0;
        $baseTotalDiscountInvoiced = 0;

        /**
         * Checking if shipping discount was added in previous invoices.
         * So basically if we have invoice with positive discount and it
         * was not canceled we don't add shipping discount to this one.
         */
        $addShippingDicount = true;
        foreach ($invoiceCollection as $previusInvoice) {
            if ($previusInvoice->getRewardpointsDiscount()) {
                $addShippingDicount = false;
                $totalDiscountInvoiced     += $previusInvoice->getRewardpointsDiscount();
                $baseTotalDiscountInvoiced += $previusInvoice->getRewardpointsBaseDiscount();
            }
        }
        if ($addShippingDicount) {
            $totalDiscountAmount     = $order->getRewardpointsAmount();
            $baseTotalDiscountAmount = $order->getRewardpointsBaseAmount();
        }        
        if ($this->isLast($invoice)) {
            $totalDiscountAmount     = $order->getRewardpointsDiscount() - $totalDiscountInvoiced;
            $baseTotalDiscountAmount = $order->getRewardpointsBaseDiscount() - $baseTotalDiscountInvoiced;
        } else {
            /** @var $item Mage_Sales_Model_Order_Invoice_Item */
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                     continue;
                }
                $orderItemDiscount      = (float) $orderItem->getRewardpointsDiscount();
                $baseOrderItemDiscount  = (float) $orderItem->getRewardpointsBaseDiscount();
                $orderItemQty       = $orderItem->getQtyOrdered();
                if ($orderItemDiscount && $orderItemQty) {
                    $totalDiscountAmount += $invoice->roundPrice($orderItemDiscount / $orderItemQty * $item->getQty(), 'regular', true);
                    $baseTotalDiscountAmount += $invoice->roundPrice($baseOrderItemDiscount / $orderItemQty * $item->getQty(), 'base', true);
                }
            }
        }
        
        $invoice->setRewardpointsDiscount($totalDiscountAmount);
        $invoice->setRewardpointsBaseDiscount($baseTotalDiscountAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        /** End spending point **/
        return $this;
    }

    /**
     * @param $invoice
     * @return bool
     */
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
