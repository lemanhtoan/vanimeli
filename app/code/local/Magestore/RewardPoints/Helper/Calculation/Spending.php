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
 * RewardPoints Earning Calculation Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Calculation_Spending extends Magestore_RewardPoints_Helper_Calculation_Abstract {

    const XML_PATH_MAX_POINTS_PER_ORDER = 'rewardpoints/spending/max_points_per_order';
    const XML_PATH_SPEND_FOR_TAX = 'rewardpoints/spending/spend_for_tax';
    const XML_PATH_FREE_SHIPPING = 'rewardpoints/spending/free_shipping';
    const XML_PATH_SPEND_FOR_SHIPPING = 'rewardpoints/spending/spend_for_shipping';
    const XML_PATH_SPEND_FOR_SHIPPING_TAX = 'rewardpoints/spending/spend_for_shipping_tax';
    const XML_PATH_ORDER_REFUND_STATUS = 'rewardpoints/spending/order_refund_state';
    const XML_PATH_MAX_POINTS_DEFAULT = 'rewardpoints/spending/max_point_default';

    /**
     * get Max point that customer can used to spend for an order
     * 
     * @param mixed $store
     * @return int
     */
    public function getMaxPointsPerOrder($store = null) {
        $maxPerOrder = (int) Mage::getStoreConfig(self::XML_PATH_MAX_POINTS_PER_ORDER, $store);
        if ($maxPerOrder > 0) {
            return $maxPerOrder;
        }
        return 0;
    }

    /**
     * get Total Point that customer used to spent for the order
     * 
     * @return int
     */
    public function getTotalPointSpent() {
        $container = new Varien_Object(array(
            'total_point_spent' => 0
        ));
        Mage::dispatchEvent('rewardpoints_calculation_spending_get_total_point', array(
            'container' => $container,
        ));
        return $this->getPointItemSpent() + $this->getCheckedRulePoint() + $this->getSliderRulePoint() + $container->getTotalPointSpent();
    }

    /**
     * get discount (Base Currency) by points of each product item on the shopping cart
     * with $item is null, result is the total discount of all items
     * 
     * @param Mage_Sales_Model_Quote_Item|null $item
     * @return float
     */
    public function getPointItemDiscount($item = null) {
        $container = new Varien_Object(array(
            'point_item_discount' => 0
        ));
        Mage::dispatchEvent('rewardpoints_calculation_spending_point_item_discount', array(
            'item' => $item,
            'container' => $container,
        ));
        return $container->getPointItemDiscount();
    }

    /**
     * get point that customer used to spend for each product item
     * with $item is null, result is the total points used for all items
     * 
     * @param Mage_Sales_Model_Quote_Item|null $item
     * @return int
     */
    public function getPointItemSpent($item = null) {
        $container = new Varien_Object(array(
            'point_item_spent' => 0
        ));
        Mage::dispatchEvent('rewardpoints_calculation_spending_point_item_spent', array(
            'item' => $item,
            'container' => $container,
        ));
        return $container->getPointItemSpent();
    }

    /**
     * pre collect total for quote/address and return quote total
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param null|Mage_Sales_Model_Quote_Address $address
     * @return float
     */
    public function getQuoteBaseTotal($quote, $address = null) {
        $cacheKey = 'quote_base_total';
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if (is_null($address)) {
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
        }
        $baseTotal = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseTotal += $item->getQty() * ($child->getQty() * $this->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                }
            } elseif ($item->getProduct()) {
                $baseTotal += $item->getQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
            }
        }
        if (Mage::getStoreConfig(self::XML_PATH_SPEND_FOR_SHIPPING, $quote->getStoreId())) {
            $shippingAmount = $address->getShippingAmountForDiscount();
            if ($shippingAmount !== null) {
                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
            } else {
                $baseShippingAmount = $address->getBaseShippingAmount();
            }
            $baseTotal += $baseShippingAmount - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
        }
        $this->saveCache($cacheKey, $baseTotal);
        return $baseTotal;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function _getItemBasePrice($item) {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * get discount (Base Currency) by points that spent with check rule type
     * 
     * @return float
     */
    public function getCheckedRuleDiscount() {
        $container = new Varien_Object(array(
            'checked_rule_discount' => 0
        ));
        Mage::dispatchEvent('rewardpoints_calculation_spending_checked_rule_discount', array(
            'container' => $container,
        ));
        return $container->getCheckedRuleDiscount();
    }

    /**
     * get points used to spend for checked rules
     * 
     * @return int
     */
    public function getCheckedRulePoint() {
        $container = new Varien_Object(array(
            'checked_rule_point' => 0
        ));
        Mage::dispatchEvent('rewardpoints_calculation_spending_checked_rule_point', array(
            'container' => $container,
        ));
        return $container->getCheckedRulePoint();
    }

    /**
     * get discount (base currency) by points that spent with slider rule type
     * 
     * @return float
     */
    public function getSliderRuleDiscount() {
        $session = Mage::getSingleton('checkout/session');
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['base_discount']) && $session->getData('use_point')
        ) {
            return $rewardSalesRules['base_discount'];
        }
        return 0;
    }

    /**
     * get points used to spend by slider rule
     * 
     * @return int
     */
    public function getSliderRulePoint() {
        $session = Mage::getSingleton('checkout/session');
        $rewardSalesRules = $session->getRewardSalesRules();
        if (is_array($rewardSalesRules) && isset($rewardSalesRules['use_point']) && $session->getData('use_point')
        ) {
            return $rewardSalesRules['use_point'];
        }
        return 0;
    }

    /**
     * get total point spent by rules on shopping cart
     * 
     * @return int
     */
    public function getTotalRulePoint() {
        return $this->getCheckedRulePoint() + $this->getSliderRulePoint();
    }

    /**
     * get quote spending rule by RuleID
     * 
     * @param int|'rate' $ruleId
     * @return Varien_Object
     */
    public function getQuoteRule($ruleId = 'rate') {
        $cacheKey = "quote_rule_model:$ruleId";
        if (!$this->hasCache($cacheKey)) {
            if ($ruleId == 'rate') {
                $this->saveCache($cacheKey, $this->getSpendingRateAsRule());
                return $this->getCache($cacheKey);
            }
            $container = new Varien_Object(array(
                'quote_rule_model' => null
            ));
            Mage::dispatchEvent('rewardpoints_calculation_spending_quote_rule_model', array(
                'container' => $container,
                'rule_id' => $ruleId,
            ));
            $this->saveCache($cacheKey, $container->getQuoteRuleModel());
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get Spend Rates as a special rule (with id = 'rate')
     * 
     * @return Varien_Object|false
     */
    public function getSpendingRateAsRule() {
        $customerGroupId = $this->getCustomerGroupId();
        $websiteId = $this->getWebsiteId();
        $cacheKey = "rate_as_rule:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $rate = Mage::getSingleton('rewardpoints/rate')->getRate(
                Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY, $customerGroupId, $websiteId
        );
        if ($rate && $rate->getId()) {
            /**
             * end update
             */
            $this->saveCache($cacheKey, new Varien_Object(array(
                'points_spended' => $rate->getPoints(),
                'base_rate' => $rate->getMoney(),
                'simple_action' => 'by_price',
                'id' => 'rate',
                'max_price_spended_type' => $rate->getMaxPriceSpendedType(), //Hai.Tran 13/11
                'max_price_spended_value' => $rate->getMaxPriceSpendedValue()//Hai.Tran 13/11
            )));
        } else {
            $this->saveCache($cacheKey, false);
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get max points can used to spend for a quote
     * 
     * @param Varien_Object $rule
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    public function getRuleMaxPointsForQuote($rule, $quote) {
        $cacheKey = "rule_max_points_for_quote:{$rule->getId()}";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointsSpended()) {
                $quoteTotal = $this->getQuoteBaseTotal($quote);

                //Hai.Tran 13/11/2013 add limit spend theo quote total
                //Tinh max point cho max total
                $maxPrice = $rule->getMaxPriceSpendedValue() > 0 ? $rule->getMaxPriceSpendedValue() : 0;
                if ($rule->getMaxPriceSpendedType() == 'by_price') {
                    $maxPriceSpend = $maxPrice;
                } elseif ($rule->getMaxPriceSpendedType() == 'by_percent') {
                    $maxPriceSpend = $quoteTotal * $maxPrice / 100;
                } else {
                    $maxPriceSpend = 0;
                }
                if ($quoteTotal > $maxPriceSpend && $maxPriceSpend > 0)
                    $quoteTotal = $maxPriceSpend;
                //End Hai.Tran 13/11/2013 add limit spend theo quote total

                $maxPoints = ceil(($quoteTotal - $this->getCheckedRuleDiscount()) / $rule->getBaseRate()
                        ) * $rule->getPointsSpended();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRulePoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                        $maxPoints = floor($maxPoints / $rule->getPointsSpended()) * $rule->getPointsSpended();
                    } else {
                        $maxPoints = 0;
                    }
                }
                $this->saveCache($cacheKey, $maxPoints);
            }
        } else {
            $container = new Varien_Object(array(
                'rule_max_points' => 0
            ));
            Mage::dispatchEvent('rewardpoints_calculation_spending_rule_max_points', array(
                'rule' => $rule,
                'quote' => $quote,
                'container' => $container,
            ));
            $this->saveCache($cacheKey, $container->getRuleMaxPoints());
        }
        if (!$this->hasCache($cacheKey)) {
            $this->saveCache($cacheKey, 0);
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get discount for quote when a rule is applied and recalculate real point used
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object $rule
     * @param int $points
     * @return float
     */
    public function getQuoteRuleDiscount($quote, $rule, &$points) {
        $cacheKey = "quote_rule_discount:{$rule->getId()}:$points";

        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if ($rule->getId() == 'rate') {
            if ($rule->getBaseRate() && $rule->getPointsSpended()) {
                $baseTotal = $this->getQuoteBaseTotal($quote) - $this->getCheckedRuleDiscount();
                /** Brian 26/1/2015 * */
                $maxDiscountSpended = 0;
                if ($maxPriceSpended = $rule->getMaxPriceSpendedValue()) {
                    if ($rule->getMaxPriceSpendedType() == 'by_price') {
                        $maxDiscountSpended = $maxPriceSpended;
                    } elseif ($rule->getMaxPriceSpendedType() == 'by_percent') {
                        $maxDiscountSpended = $this->getQuoteBaseTotal($quote) * $maxPriceSpended / 100;
                    }
                }
                if ($maxDiscountSpended > 0)
                    $baseTotal = min($maxDiscountSpended, $baseTotal);
                /** end * */
                $maxPoints = ceil($baseTotal / $rule->getBaseRate()) * $rule->getPointsSpended();
                if ($maxPerOrder = $this->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $this->getPointItemSpent();
                    $maxPerOrder -= $this->getCheckedRulePoint();
                    if ($maxPerOrder > 0) {
                        $maxPoints = min($maxPoints, $maxPerOrder);
                    } else {
                        $maxPoints = 0;
                    }
                }
                $points = min($points, $maxPoints);
                $points = floor($points / $rule->getPointsSpended()) * $rule->getPointsSpended();
                $this->saveCache($cacheKey, min($points * $rule->getBaseRate() / $rule->getPointsSpended(), $baseTotal));
            } else {
                $points = 0;
                $this->saveCache($cacheKey, 0);
            }
        } else {
            $container = new Varien_Object(array(
                'quote_rule_discount' => 0,
                'points' => $points
            ));
            Mage::dispatchEvent('rewardpoints_calculation_spending_quote_rule_discount', array(
                'rule' => $rule,
                'quote' => $quote,
                'container' => $container,
            ));
            $points = $container->getPoints();
            $this->saveCache($cacheKey, $container->getQuoteRuleDiscount());
        }
        return $this->getCache($cacheKey);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isUseMaxPointsDefault($store = null) {
        return Mage::getStoreConfig(self::XML_PATH_MAX_POINTS_DEFAULT, $store);
    }

    /**
     * @return mixed
     */
    public function isUsePoint() {
        return Mage::getSingleton('checkout/session')->getData('use_point');
    }


}
