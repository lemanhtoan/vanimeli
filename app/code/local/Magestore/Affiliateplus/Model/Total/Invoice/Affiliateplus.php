<?php

class Magestore_Affiliateplus_Model_Total_Invoice_Affiliateplus extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
            // Changed By Adam 22/09/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this;
	
        $baseDiscount = 0;
        $discount = 0;
        
        foreach ($invoice->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            
            $orderItemDiscount = (float)$orderItem->getAffiliateplusAmount();
            $baseOrderItemDiscount = (float)$orderItem->getBaseAffiliateplusAmount();
            
            $orderItemQty = $orderItem->getQtyOrdered();
            if ($orderItemDiscount && $orderItemQty) {
                $discount -= $orderItemDiscount * $item->getQty() / $orderItemQty;
                $baseDiscount -= $baseOrderItemDiscount * $item->getQty() / $orderItemQty;
            }
        }
        /* Changed By Adam 30/09/2014: to solve the problem: 
         * invoice san pham ko phai affiliate nhung van hien discount
         */
//        if (!floatval($baseDiscount)) {
//            $order = $invoice->getOrder();
//            $baseDiscount = $order->getBaseAffiliateplusDiscount();
//            $discount = $order->getAffiliateplusDiscount();
//        }
        if (floatval($baseDiscount)){
            $baseDiscount = Mage::app()->getStore()->roundPrice($baseDiscount);
            $discount = Mage::app()->getStore()->roundPrice($discount);
            
			$invoice->setBaseAffiliateplusDiscount($baseDiscount);
			$invoice->setAffiliateplusDiscount($discount);
			
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseDiscount);
			$invoice->setGrandTotal($invoice->getGrandTotal() + $discount);
		}
		return $this;
	}
}