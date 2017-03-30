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
 * RewardPoints Helper to show spending point on Shopping Cart/ Checkout Page/ Admin Create Order
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Block_Spend extends Magestore_RewardPoints_Helper_Calculation_Abstract
{
    /**
     * get spending calculation
     * 
     * @return Magestore_RewardPoints_Helper_Calculation_Spending
     */
    public function getCalculation()
    {
        return Mage::helper('rewardpoints/calculation_spending');
    }
    
    /**
     * get current working with quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        return Mage::getSingleton('checkout/session')->getQuote();
    }
    
    /**
     * check reward points is enable to use or not
     * 
     * @return boolean
     */
    public function enableReward()
    {
        if (!Mage::helper('rewardpoints')->isEnable(Mage::helper('rewardpoints/customer')->getStoreId())) {
            return false;
        }
        if ($this->getQuote()->getBaseGrandTotal() < 0.0001
            && !$this->getCalculation()->getTotalRulePoint()
        ) {
            return false;
        }
        if (!Mage::helper('rewardpoints/customer')->isAllowSpend($this->getQuote()->getStoreId())) {
            return false;
        }
        return true;
    }
    
    /**
     * get all spending rules available for current shopping cart
     * 
     * @return array
     */
    public function getSpendingRules()
    {
        $cacheKey = 'spending_rules_array';
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $container = new Varien_Object(array(
            'spending_rules'   => array()
        ));
        Mage::dispatchEvent('rewardpoints_block_spend_get_rules', array(
            'container' => $container,
        ));
        $this->saveCache($cacheKey, $container->getSpendingRules());
        return $this->getCache($cacheKey);
    }
    
    /**
     * get all spending rule with type is slider
     * 
     * @return array
     */
    public function getSliderRules()
    {
        $rules = array();
        $rule = $this->getCalculation()->getSpendingRateAsRule();
        if ($rule && $rule->getId()) {
            $rules[] = $rule;
        }
        foreach ($this->getSpendingRules() as $rule) {
            if ($rule->getSimpleAction() == 'by_price') {
                $rules[] = $rule;
            }
        }
        return $rules;
    }
    
    /**
     * get all spending rule with type is checkbox
     * 
     * @return array
     */
    public function getCheckboxRules()
    {
        $rules = array();
        $customerPoints = $this->getCustomerTotalPoints() - $this->getCalculation()->getPointItemSpent();
        foreach ($this->getSpendingRules() as $rule) {
            if (in_array($rule->getId(), $this->getCheckedData()) ||
                ($rule->getSimpleAction() == 'fixed'
                && $rule->getPointsSpended() <= $customerPoints
            )) {
                $rules[] = $rule;
            }
        }
        return $rules;
    }
    
    /**
     * get JSON string used for JS
     * 
     * @param array $rules
     * @return string
     */
    public function getRulesJson($rules = null) {
        if (is_null($rules)) {
            $rules = $this->getSliderRules();
        }
        $result = array();
        foreach ($rules as $rule) {
            $ruleOptions = array();
            if ($this->getCustomerPoint() < $rule->getPointsSpended()) {
                $ruleOptions['optionType'] = 'needPoint';
                $ruleOptions['needPoint'] = $rule->getPointsSpended() - $this->getCustomerPoint();
            } else {
                $quote = $this->getQuote();
                $sliderOption = array();
                
                $sliderOption['minPoints'] = 0;
                $sliderOption['pointStep'] = (int)$rule->getPointsSpended();
                
                $maxPoints = $this->getCustomerPoint();
                if ($rule->getMaxPointsSpended() && $maxPoints > $rule->getMaxPointsSpended()) {
                    $maxPoints = $rule->getMaxPointsSpended();
                }
                if ($maxPoints > $this->getCalculation()->getRuleMaxPointsForQuote($rule, $quote)) {
                    $maxPoints = $this->getCalculation()->getRuleMaxPointsForQuote($rule, $quote);
                }
                // Refine max points
                if ($sliderOption['pointStep']) {
                    $maxPoints = floor($maxPoints / $sliderOption['pointStep']) * $sliderOption['pointStep'];
                }
                $sliderOption['maxPoints'] = max(0, $maxPoints);
                
                $ruleOptions['sliderOption'] = $sliderOption;
                $ruleOptions['optionType'] = 'slider';
            }
            $result[$rule->getId()] = $ruleOptions;
        }
        return Mage::helper('core')->jsonEncode($result);
    }
    /**
     * get JSON string used for JS
     * 
     * @param array $rules
     * @return string
     */
    public function getRulesArray($rules = null) {
        if (is_null($rules)) {
            $rules = $this->getSliderRules();
        }
        $result = array();
        foreach ($rules as $rule) {
            $ruleOptions = array();
            if ($this->getCustomerPoint() < $rule->getPointsSpended()) {
                $ruleOptions['optionType'] = 'needPoint';
                $ruleOptions['needPoint'] = $rule->getPointsSpended() - $this->getCustomerPoint();
            } else {
                $quote = $this->getQuote();
                $sliderOption = array();
                
                $sliderOption['minPoints'] = 0;
                $sliderOption['pointStep'] = (int)$rule->getPointsSpended();
                
                $maxPoints = $this->getCustomerPoint();
                if ($rule->getMaxPointsSpended() && $maxPoints > $rule->getMaxPointsSpended()) {
                    $maxPoints = $rule->getMaxPointsSpended();
                }
                if ($maxPoints > $this->getCalculation()->getRuleMaxPointsForQuote($rule, $quote)) {
                    $maxPoints = $this->getCalculation()->getRuleMaxPointsForQuote($rule, $quote);
                }
                // Refine max points
                if ($sliderOption['pointStep']) {
                    $maxPoints = floor($maxPoints / $sliderOption['pointStep']) * $sliderOption['pointStep'];
                }
                $sliderOption['maxPoints'] = max(0, $maxPoints);
                
                $ruleOptions['sliderOption'] = $sliderOption;
                $ruleOptions['optionType'] = 'slider';
            }
            $result[$rule->getId()] = $ruleOptions;
        }
        return $result;
    }
    
    /**
     * get customer total points on his balance
     * 
     * @return int
     */
    public function getCustomerTotalPoints()
    {
        return Mage::helper('rewardpoints/customer')->getBalance();
    }
    
    /**
     * get customer point after he use to spend for order (estimate)
     * 
     * @return int
     */
    public function getCustomerPoint()
    {
        if (!$this->hasCache('customer_point')) {
            $points  = $this->getCustomerTotalPoints();
            $points -= $this->getCalculation()->getPointItemSpent();
            $points -= $this->getCalculation()->getCheckedRulePoint();
            if ($points < 0) {
                $points = 0;
            }
            $this->saveCache('customer_point', $points);
        }
        return $this->getCache('customer_point');
    }
    
    /**
     * get current customer model
     * 
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return Mage::helper('rewardpoints/customer')->getCustomer();
    }
    
    /**
     * format discount for a rule
     * 
     * @param Varien_Object $rule
     * @return string
     */
    public function formatDiscount($rule)
    {
        $store = Mage::app()->getStore(Mage::helper('rewardpoints/customer')->getStoreId());
        if ($rule->getId() == 'rate') {
            $price = $store->convertPrice($rule->getBaseRate());
        } else {
            if ($rule->getDiscountStyle() == 'cart_fixed') {
                $price = $store->convertPrice($rule->getDiscountAmount());
            } else {
                return round($rule->getDiscountAmount(), 2) . '%';
            }
        }
        return $store->formatPrice($price);
    }
    
    /**
     * get slider rules date that applied
     * 
     * @return Varien_Object
     */
    public function getSliderData()
    {
        $session = Mage::getSingleton('checkout/session');
        return new Varien_Object($session->getRewardSalesRules());
    }
    
    /**
     * get checked rule data that applied
     * 
     * @return array
     */
    public function getCheckedData()
    {
        if (!$this->hasCache('checked_data')) {
            $session = Mage::getSingleton('checkout/session');
            $rewardCheckedRules = $session->getRewardCheckedRules();
            if (!is_array($rewardCheckedRules)) {
                $this->saveCache('checked_data', array());
            } else {
                $this->saveCache('checked_data', array_keys($rewardCheckedRules));
            }
        }
        return $this->getCache('checked_data');
    }
    
    /**
     * check current checkout session is using point or not
     * 
     * @return boolean
     */
    public function isUsePoint()
    {
        return Mage::getSingleton('checkout/session')->getData('use_point');
    }
}
