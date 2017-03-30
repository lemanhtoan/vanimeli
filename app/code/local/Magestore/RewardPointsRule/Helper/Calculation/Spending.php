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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Calculation Spending Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Helper_Calculation_Spending extends Magestore_RewardPoints_Helper_Calculation_Spending {

    /**
     * get available catalog spending rules
     * 
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return Magestore_RewardPointsRule_Model_Mysql4_Spending_Catalog_Collection
     */
    public function getCatalogSpendingRules($customerGroupId = null, $websiteId = null, $date = null) {
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $collectionKey = "catalog_spending_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/spending_catalog_collection')
                    ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        }
        return $this->getCache($collectionKey);
    }

    /**
     * get catalog spending rules for a product
     * 
     * @param mixed $product
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return array
     */
    public function getProductSpendingRules($product, $customerGroupId = null, $websiteId = null, $date = null) {
        if (!is_object($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $cacheKey = "product_spending_collection:{$product->getId()}:$customerGroupId:$websiteId";
        if (!$this->hasCache($cacheKey)) {
            $rules = array();
            foreach ($this->getCatalogSpendingRules($customerGroupId, $websiteId, $date) as $rule) {
                /**
                 * end update
                 */
                if ($rule->validate($product)) {
                    $rules[] = $rule;
                    if ($rule->getStopRulesProcessing()) {
                        break;
                    }
                }
            }
            $this->saveCache($cacheKey, $rules);
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get session catalog spending point
     * 
     * @param type $item
     * @return int
     */
    public function getCatalogSpendingPoints($item = null) {
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            return 0;
        }
        if ($item && $item->getId()) {
            if (isset($catalogRules[$item->getId()]) && $rule = $catalogRules[$item->getId()]) {
                return isset($rule['point_used']) && isset($rule['item_qty']) ? $rule['point_used'] * $rule['item_qty'] : 0;
            }
            return 0;
        }
        $points = 0;
        foreach ($catalogRules as $rule) {
            $rulePoints = isset($rule['point_used']) && isset($rule['item_qty']) ? $rule['point_used'] * $rule['item_qty'] : 0;
            $points += $rulePoints;
        }
        return $points;
    }

    /**
     * get discount for current item
     * 
     * @param type $item
     * @return int
     */
    public function getItemDiscount($item = null) {
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            return 0;
        }
        if (is_null($item)) {
            $discount = 0;
            foreach ($catalogRules as $catalogPoints) {
                $discount += (isset($catalogPoints['base_point_discount']) && isset($catalogPoints['item_qty'])) ? $catalogPoints['base_point_discount'] * $catalogPoints['item_qty'] : 0;
            }
            return $discount;
        }
        if (!isset($catalogRules[$item->getId()])) {
            return 0;
        }
        $catalogPoints = $catalogRules[$item->getId()];
        if (isset($catalogPoints['base_point_discount'])) {
            return $catalogPoints['base_point_discount'];
        }
        return 0;
    }

    /**
     * get Catalog Rule discount (each step)
     * 
     * @param type $rule
     * @return float
     */
    public function getCatalogRuleDiscount($rule, $productPrice) {
        $ruleDiscount = 0;
        switch ($rule->getDiscountStyle()) {
            case 'by_fixed':
                $ruleDiscount = $rule->getDiscountAmount();
                break;
            case 'to_fixed':
                $ruleDiscount = $productPrice - $rule->getDiscountAmount();
                break;
            case 'by_percent':
                $ruleDiscount = $productPrice * $rule->getDiscountAmount() / 100;
                break;
            case 'to_percent':
                $ruleDiscount = $productPrice * max(0, 100 - $rule->getDiscountAmount()) / 100;
                // $ruleDiscount = $productPrice * $rule->getDiscountAmount() / (100 + $rule->getDiscountAmount());
                break;
        }
        if ($ruleDiscount < 0) {
            $ruleDiscount = 0;
        }
        return $ruleDiscount;
    }

    /**
     * calculate catalog rule discount
     * 
     * @param type $rule
     * @param type $product
     * @param int $points
     * @return float
     */
    public function getCatalogDiscount($rule, $product, &$points) {
        $stepDiscount = $this->getCatalogRuleDiscount($rule, $product->getPrice());
        $pointStep = $this->getPointStep($rule, $product->getPrice()); //$pointStep = $this->getPointStep($rule, $product->getFinalPrice());
        if ($pointStep == 0) {
            $points = 0;
            $productDiscount = $stepDiscount;
        } else {
            $points = floor($points / $pointStep) * $pointStep;
            if ($timeUses = (int) $rule->getUsesPerProduct()) {
                $zMaxPoints = $timeUses * $pointStep;
                if ($points > $zMaxPoints) {
                    $points = $zMaxPoints;
                }
            }
            /** Brian 26/1/2015 **/
            $limitDiscount = 0;
            if($maxPriceSpended = $rule->getMaxPriceSpendedValue()){
                if ($rule->getMaxPriceSpendedType() == 'by_price') {
                    $limitDiscount = $maxPriceSpended;
                } elseif ($rule->getMaxPriceSpendedType() == 'by_percent') {
                    $limitDiscount = $product->getPrice() * $maxPriceSpended / 100;
                }
            }
            $maxDiscountForItem = Mage::helper('tax')->getPrice($product, $product->getPrice()) - $product->getDiscountAmount();
            if($limitDiscount) $maxDiscountForItem = min($limitDiscount, $maxDiscountForItem);
            $maxTimeUses = ceil($maxDiscountForItem/$stepDiscount);
            if($points > ($maxTimeUses * $pointStep)){
                $points = $maxTimeUses * $pointStep;
            }
            /** End **/
            $productDiscount = $stepDiscount * $points / $pointStep;
        }
        $finalPrice = Mage::helper('tax')->getPrice($product, $product->getPrice()) - $product->getDiscountAmount();
        if ($productDiscount > $finalPrice) {
            return $finalPrice;
        }
        return $productDiscount;
    }

    public function getPointStep($rule, $price) {
        $pointStep = 0;
        if ($rule->getSimpleAction() == 'fixed') {
            $pointStep = (int) $rule->getPointsSpended();
        } else {
            if ($rule->getMoneyStep() > 0) {
                $pointStep = floor($price / $rule->getMoneyStep()) * $rule->getPointsSpended();
            }
            if ($rule->getMaxPointsSpended() && $pointStep > $rule->getMaxPointsSpended()) {
                $pointStep = $rule->getMaxPointsSpended();
            }
        }
        return $pointStep;
    }

    public function getCatalogRule($ruleId) {
        $cacheKey = "catalog_rule_model:$ruleId";
        if (!$this->hasCache($cacheKey)) {
            $this->saveCache($cacheKey, Mage::getModel('rewardpointsrule/spending_catalog')->load($ruleId));
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get spending rules for shopping cart
     * 
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return Magestore_RewardPointsRule_Model_Mysql4_Spending_Sales_Collection
     */
    public function getShoppingCartRules($customerGroupId = null, $websiteId = null, $date = null) {
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $rules = Mage::getResourceModel('rewardpointsrule/spending_sales_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
        foreach ($rules as $rule) {
            $rule->afterLoad();
        }
        return $rules;
    }

    /**
     * get spending rules depend on current quote
     * 
     * @param type $quote
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return array
     */
    public function getQuoteSpendingRules($quote = null, $customerGroupId = null, $websiteId = null, $date = null) {
        if (is_null($quote)) {
            $quote = Mage::helper('rewardpoints/block_spend')->getQuote();
        }
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        if (is_null($customerGroupId)) {
            $customerGroupId = $this->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $cacheKey = "quote_spending_collection:{$address->getId()}:$customerGroupId:$websiteId";
        if (!$this->hasCache($cacheKey)) {
            $rules = array();
            $session = Mage::getSingleton('checkout/session');
            $rewardCheckedRules = $session->getRewardCheckedRules();
            if (is_array($rewardCheckedRules)) {
                $rewardCheckedRules = array_keys($rewardCheckedRules);
            } else {
                $rewardCheckedRules = array();
            }
            foreach ($this->getShoppingCartRules($customerGroupId, $websiteId, $date) as $rule) {
                /**
                 * end update
                 */
                if ($rule->validate($address)) {
                    $checkActionRule = false;
                    foreach ($address->getAllItems() as $item) {
                        if ($item->getParentItemId())
                            continue;
                        if ($rule->getActions()->validate($item)) {
                            $checkActionRule = true;
                            break;
                        }
                    }
                    if(!$checkActionRule) continue;
                    
                    $rules[] = $rule;
                    if ($rule->getStopRulesProcessing() && in_array($rule->getId(), $rewardCheckedRules)) {
                        break;
                    }
                }
            }
            $this->saveCache($cacheKey, $rules);
        }
        return $this->getCache($cacheKey);
    }

    /**
     * get checked rule discount (base currency) without a special rule
     * 
     * @param type $ruleId
     * @return float
     */
    public function getCheckedRuleDiscountWithout($ruleId = null) {
        $session = Mage::getSingleton('checkout/session');
        $rewardCheckedRules = $session->getRewardCheckedRules();
        if (!$rewardCheckedRules || !is_array($rewardCheckedRules)) {
            return 0;
        }
        $baseDiscount = 0;
        foreach ($rewardCheckedRules as $_ruleId => $ruleData) {
            if ($_ruleId != $ruleId) {
                $baseDiscount += isset($ruleData['base_discount']) ? $ruleData['base_discount'] : 0;
            }
        }
        return $baseDiscount;
    }

    /**
     * get checked rule points without a special rule
     * 
     * @param type $ruleId
     * @return int
     */
    public function getCheckedRulePointWithout($ruleId = null) {
        $session = Mage::getSingleton('checkout/session');
        $rewardCheckedRules = $session->getRewardCheckedRules();
        if (!$session->getData('use_point') || !$rewardCheckedRules || !is_array($rewardCheckedRules)) {
            return 0;
        }
        $points = 0;
        foreach ($rewardCheckedRules as $_ruleId => $ruleData) {
            if ($_ruleId != $ruleId) {
                $points += isset($ruleData['use_point']) ? $ruleData['use_point'] : 0;
            }
        }
        return $points;
    }

}
