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
 * @package     Magestore_RewardPointsReferiends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints earn points for Order by Point Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferfriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Total_Quote_Earningaftertax extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    /**
     * collect reward points that customer earned (per each item and address) total
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Mage_Sales_Model_Quote $quote
     * @return Magestore_RewardPointsReferFriends_Model_Total_Quote_Earning
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId());
        if ($applyTaxAfterDiscount ||
            !Mage::helper('rewardpointsreferfriends')->isEnable($quote->getStoreId()) || 
            (!$quote->isVirtual() && $address->getAddressType() == 'billing') || 
            ($quote->isVirtual() && $address->getAddressType() == 'shipping')) {
            return $this;
        }
        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');
        if(!$key) $key = $quote->getRewardpointsOfferKey();
        if (!$key || !Mage::helper('rewardpointsreferfriends/calculation_earning')->checkUsesPerCustomer($quote))
            return $this;
        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
        if (!$refer_cus || !$refer_cus->getId() || Mage::getSingleton('customer/session')->getCustomerId() == $refer_cus->getCustomerId())
            return $this;
           
        $quoteTotal = Mage::helper('rewardpoints/calculation_spending')->getQuoteBaseTotal($quote, $address);
        if($quoteTotal <= 0) return $this;
        
        list($invited_base_discount, $earningPoints) = Mage::helper('rewardpointsreferfriends/calculation_earning')->getReferValue($address, $refer_cus);
        //Zend_debug::dump('discount: '.$invited_base_discount.'<br />point: '.$earningPoints); die();
        if (!Mage::helper('rewardpointsreferfriends/calculation_earning')->checkNewCustomer($quote))
            $invited_base_discount=0;
        if (!Mage::helper('rewardpointsreferfriends/calculation_earning')->checkCustomerGetDiscount($quote))
            $invited_base_discount=0;
        if($invited_base_discount > 0 || $earningPoints > 0){
            $invited_discount = Mage::app()->getStore()->convertPrice($invited_base_discount);
            
            $this->_prepareDiscountForItem($address, $invited_base_discount, $earningPoints);
                    
            Mage::getSingleton('checkout/session')->setRewardpointsInvitedBaseDiscount($invited_base_discount);
            $address->setRewardpointsReferalEarn($earningPoints);
            $address->setRewardpointsInvitedBaseDiscount($invited_base_discount);
            $address->setRewardpointsInvitedDiscount($invited_discount);
            $quote->setRewardpointsInvitedBaseDiscount($invited_base_discount);
            $quote->setRewardpointsInvitedDiscount($invited_discount);
            $quote->setRewardpointsOfferKey($key);

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $invited_base_discount);
            $address->setGrandTotal($address->getGrandTotal() - $invited_discount);
            
            $address->setRewardpointsReferCustomerId($refer_cus->getCustomerId());
        }
        return $this;        
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        if ($applyTaxAfterDiscount) {
            return $this;
        }
        $amount = $address->getRewardpointsInvitedDiscount();
        if ($amount != 0) {
            $title = Mage::helper('rewardpointsreferfriends')->__('Offer Discount');
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => -$amount
            ));
        }
        return $this;
    }
    /**
     * Prepare Discount Amount used for Tax
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @param type $baseDiscount
     * @return Magestore_RewardPoints_Model_Total_Quote_Point
     */
    public function _prepareDiscountForItem(Mage_Sales_Model_Quote_Address $address, $baseDiscount, $points) {
        $items = $address->getAllItems();
        if (!count($items))
            return $this;

        // Calculate total item prices
        $baseItemsPrice = 0;
        $store = Mage::app()->getStore();
        $spendHelper = Mage::helper('rewardpoints/calculation_spending');
        $baseParentItemsPrice = array();
        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $baseParentItemsPrice[$item->getId()] = 0;
                foreach ($item->getChildren() as $child) {
                    $baseParentItemsPrice[$item->getId()] += $item->getQty() * ($child->getQty() * $spendHelper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                }
                $baseItemsPrice += $baseParentItemsPrice[$item->getId()];
            } elseif ($item->getProduct()) {
                $baseItemsPrice += $item->getQty() * $spendHelper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
            }
        }
        if ($baseItemsPrice < 0.0001)
            return $this;
        $discountForShipping = Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Spending::XML_PATH_SPEND_FOR_SHIPPING, $address->getQuote()->getStoreId()
        );
        if ($baseItemsPrice < $baseDiscount && $discountForShipping) {
            $baseDiscountForShipping = $baseDiscount - $baseItemsPrice;
            $baseDiscount = $baseItemsPrice;
        } else {
            $baseDiscountForShipping = 0;
        }
        // Update discount for each item
        foreach ($items as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemBaseDiscount = $baseDiscount * $baseParentItemsPrice[$item->getId()] / $baseItemsPrice;
                foreach ($item->getChildren() as $child) {
                    if ($parentItemBaseDiscount <= 0)
                        break;
                    $baseItemPrice = $item->getQty() * ($child->getQty() * $spendHelper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    $itemBaseDiscount = min($baseItemPrice, $parentItemBaseDiscount); //$baseDiscount * $baseItemPrice / $baseItemsPrice;
                    $parentItemBaseDiscount -= $itemBaseDiscount;
                    $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                    $itemBaseEarn = round($points * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_DOWN);
                    $child->setRewardpointsInvitedBaseDiscount($itemBaseDiscount)
                          ->setRewardpointsInvitedDiscount($itemDiscount)
                          ->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount() + $itemBaseDiscount)
                          ->setRewardpointsReferalEarn($itemBaseEarn);
                }
            } elseif ($item->getProduct()) {
                $baseItemPrice = $item->getQty() * $spendHelper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                $itemBaseDiscount = $baseDiscount * $baseItemPrice / $baseItemsPrice;
                $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                $itemBaseEarn = round($points * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_DOWN);
                $item->setRewardpointsInvitedBaseDiscount($itemBaseDiscount)
                     ->setRewardpointsInvitedDiscount($itemDiscount)
                     ->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemBaseDiscount)
                     ->setRewardpointsReferalEarn($itemBaseEarn);
            }
        }
        if ($baseDiscountForShipping > 0) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseShipping = $baseShippingAmount - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
            $itemBaseDiscount = ($baseDiscountForShipping <= $baseShipping) ? $baseDiscountForShipping : $baseShipping; //$baseDiscount * $address->getBaseShippingAmount() / $baseItemsPrice;
            $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
            $address->setRewardpointsBaseAmount($address->getRewardpointsBaseAmount() + $itemBaseDiscount)
                    ->setRewardpointsAmount($address->getRewardpointsAmount() + $itemDiscount)
                    ->setMagestoreBaseDiscountForShipping($address->getMagestoreBaseDiscountForShipping() + $itemBaseDiscount);
        }

        return $this;
    }

    //get Rate
    public function getItemRateOnQuote($address, $product, $store) {
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                    $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
            );
            $rate = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
            return $rate;
        }
        return 0;
    }

    public function getShipingTaxRate($address, $store) {
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
        );
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));
        $rate = Mage::getSingleton('tax/calculation')->getRate($request);
        return $rate;
    }

    public function calTax($price, $rate) {
        return $this->round(Mage::getSingleton('tax/calculation')->calcTaxAmount($price, $rate, true, false));
    }

    public function round($price) {
        return Mage::getSingleton('tax/calculation')->round($price);
    }

}
