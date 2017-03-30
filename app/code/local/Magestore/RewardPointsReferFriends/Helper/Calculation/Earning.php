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
 * RewardPoints Earning Calculation Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Helper_Calculation_Earning extends Magestore_RewardPoints_Helper_Calculation_Earning {

    /**
     * get discount/point for referfriends
     * @param type $address
     * @return int
     */
    public function getReferValue($address, $refer_cus, $customerGroupId = null, $websiteId = null, $date = null) {
        $cacheKey = "referfriends_points_discount";
        if ($this->hasCache($cacheKey))
            return $this->getCache($cacheKey);

        $quote = $address->getQuote();
        $base_total_discount = 0;
        $total_points_earn = 0;
        if (is_null($customerGroupId))
            $customerGroupId = $quote->getCustomerGroupId();
        if (is_null($websiteId))
            $websiteId = Mage::app()->getStore($quote->getStoreId())->getWebsiteId();
        if (is_null($date))
            $date = Mage::getModel('core/date')->date();

        /* ------ Get special offer and validate address ---------- */
        $offers = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->loadByCustomerOrder($address, $customerGroupId, $websiteId, $date);
        $items = $address->getAllItems();
        $quoteTotal = Mage::helper('rewardpoints/calculation_spending')->getQuoteBaseTotal($quote, $address);
        $spendHelper = Mage::helper('rewardpoints/calculation_spending');
        foreach ($offers as $offer) {
            $baseItemsPrice = 0;
            $total_items_qty = 0;
            $discountOffer = 0;
            foreach ($items as $item) {
                if ($item->getParentItemId())
                    continue;
                if ($offer->getActions()->validate($item)) {
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            $baseItemsPrice += $item->getQty() * ($child->getQty() * $spendHelper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        }
                    } elseif ($item->getProduct()) {
                        $baseItemsPrice += $item->getQty() * $spendHelper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    }
                    $total_items_qty += $item->getQty();
                }
            }
            $baseItemsPrice += $this->getShippingAmount($address);
            if ($baseItemsPrice > 0) {
                $total_points_earn += $this->getOfferPoint($offer, $baseItemsPrice, $total_items_qty);
                $discountOffer += $this->getOfferDiscount($offer, $baseItemsPrice);
                if (($base_total_discount + $discountOffer) > $quoteTotal) {
                    $discountOffer = max(0, $quoteTotal - $base_total_discount);
                }
                $base_total_discount += $discountOffer;
                if ($base_total_discount >= $quoteTotal || $offer->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        if ($base_total_discount == 0) {
            // calculate from default offer.
            $offer = Mage::getResourceModel('rewardpointsreferfriends/rewardpointsspecialrefer_collection')
                    ->getDefaultOffer($customerGroupId, $websiteId, $date);
            if ($offer->getId()) {
                if ($offer->getData('discount_type') == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED) {
                    $base_total_discount += $offer->getData('discount_value');
                } else {
                    $base_total_discount += $quoteTotal * $offer->getData('discount_value') / 100;
                }
                if ($offer->getData('commission_point')) {
                    $total_points_earn += (int) $this->round($offer->getData('commission_point'));
                }
            }
        }
        $base_total_discount = min($base_total_discount, $quoteTotal);
        $value = array($base_total_discount, $total_points_earn);
        $this->saveCache($cacheKey, $value);
        return $value;
    }

    public function getShippingAmount($address) {
        $quote = $address->getQuote();
        if (Mage::getStoreConfig(Magestore_RewardPoints_Helper_Calculation_Spending::XML_PATH_SPEND_FOR_SHIPPING, $quote->getStoreId())) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            return $baseShippingAmount - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
        }
        return 0;
    }

    public function getOfferDiscount($offer, $baseItemsPrice) {
        if ($offer->getDiscountType() == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED) {
            $discount = $offer->getDiscountValue();
        } else {
            $discount = $baseItemsPrice * $offer->getDiscountValue() / 100;
        }
        return min($discount, $baseItemsPrice);
    }

    public function getOfferPoint($offer, $baseItemsPrice, $total_items_qty) {
        $commissionAction = $offer->getCommissionAction();
        if ($commissionAction == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_TO_CUSTOMER) {
            return (int) $offer->getCommissionPoint();
        } else if ($commissionAction == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_EVERY_MONEY) {
            return (int) $offer->getCommissionPoint() * $this->round($baseItemsPrice / $offer->getMoneyStep());
        } else {
            return (int) $offer->getCommissionPoint() * $this->round($total_items_qty / $offer->getQtyStep());
        }
        return 0;
    }

    /**
     * set store id for current working helper
     * 
     * @param int $value
     * @return Magestore_RewardPointsRule_Helper_Calculation_Earning
     */
    public function setStoreId($value) {
        $this->saveCache('store_id', $value);
        return $this;
    }

    public function round($number) {
        return Mage::helper('rewardpoints/calculator')->round(
                        $number, $this->getCache('store_id')
        );
    }

    /**
     * check uses per customer when offer
     * @param type $quote
     * @return boolean
     */
    public function checkUsesPerCustomer($quote) {
        $uses_per_customer = Mage::helper('rewardpointsreferfriends')->getUsesPerCustomer();
        if ($uses_per_customer) {
            $collection = $this->getListOrderByCustomer($quote);
            $collection->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                    ->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CLOSED));
            if (count($collection) >= $uses_per_customer) {
                return false;
            }
        }
        return true;
    }

    /**
     * check limit order of customer when offer
     * @param type $quote
     * @return boolean
     */
    public function checkNewCustomer($quote) {
        $newcustomer = Mage::helper('rewardpointsreferfriends')->getReferConfig('apply_old_customer', $quote->getStoreId());
        if (!$newcustomer) {
            $collection = $this->getListOrderByCustomer($quote);
            if ($collection->getSize()) {
                return true;
            }
            $collection = Mage::getModel('sales/order')->getCollection()
                    /*  ->addFieldToFilter(
                      array('customer_id','customer_email'),
                      array($customer_id, $customer_email)
                      ) */
                    ->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                    ->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CLOSED));
            $readAdapter = $collection->getConnection();
            $sql = '(' . $readAdapter->quoteInto('customer_id = ?', $customer_id) . ' OR ' . $readAdapter->quoteInto('customer_email = ?', $customer_email) . ')';
            $collection->getSelect()->where($sql);
            if ($collection->getSize()) {
                return false;
            }
        }
        return true;
    }

    /**
     * check limit get discount for order of customer when offer
     * @param type $quote
     * @return boolean
     */
    public function checkCustomerGetDiscount($quote) {
        $collection = $this->getListOrderByCustomer($quote);
        $collection->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                ->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CLOSED));
        if ($collection->getSize()) {
            return false;
        }

        return true;
    }

    public function getListOrderByCustomer($quote) {
        $customer_id = 0;
        if ($quote->getCustomerId())
            $customer_id = $quote->getCustomerId();
        $customer_email = '';
        if ($quote->getCustomerEmail())
            $customer_email = $quote->getCustomerEmail();
        $collection = Mage::getModel('sales/order')->getCollection()
        /*                     ->addFieldToFilter(
          array('customer_id','customer_email'),
          array($customer_id, $customer_email)
          )
          ->addFieldToFilter(
          array('rewardpoints_invited_discount','rewardpoints_referal_earn'),
          array(array('gt' => 0),array('gt' => 0))
          ) */
        ;
        $readAdapter = $collection->getConnection();
        $sql = '(' . $readAdapter->quoteInto('customer_id = ?', $customer_id) . ' OR ' . $readAdapter->quoteInto('customer_email = ?', $customer_email) . ')'
                . 'AND (rewardpoints_invited_discount > 0 OR rewardpoints_referal_earn > 0)';
        $collection->getSelect()->where($sql);
        return $collection;
    }

}
