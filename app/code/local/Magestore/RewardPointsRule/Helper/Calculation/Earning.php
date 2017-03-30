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
 * RewardPointsRule Calculation Earning Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Helper_Calculation_Earning extends Magestore_RewardPoints_Helper_Calculation_Earning {

    const XML_PATH_EARNING_BY_SHIPPING = 'rewardpoints/earning/by_shipping';
    const XML_PATH_EARNING_BY_TAX = 'rewardpoints/earning/by_tax';

    /**
     * get calculate earning point for each product
     * 
     * @param mixed $product
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getCatalogEarningPoints($product, $customerGroupId = null, $websiteId = null, $date = null) {
        if (!is_object($product) and is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if ($product->getRewardpointsSpend() != null && $product->getRewardpointsSpend() >= 0){
            return 0;
        }
        if ($product->getRewardpointsEarn() != null && $product->getRewardpointsEarn() >= 0) {
            return $product->getRewardpointsEarn();
        }
        if (is_null($customerGroupId)) {
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->getCustomerGroupId();
            }
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getWebsiteId();
        }
        $cacheKey = "catalog_earning:{$product->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                    ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }
        $earnPointByTax = Mage::getStoreConfig('rewardpoints/earning/by_tax');
        foreach ($rules as $rule) {
            /**
             * end update
             */
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                        $rule->getSimpleAction(), $rule->getPointsEarned(), Mage::helper('rewardpoints/calculator')->getPrice($product, $product->getFinalPrice(), $earnPointByTax), Mage::helper('rewardpoints/calculator')->getPrice($product, $product->getFinalPrice(), $earnPointByTax) - $product->getCost(),
//                                $product->getPrice(),
//                                $product->getPrice() - $product->getCost(),
                        $rule->getMoneyStep(), $rule->getMaxPointsEarned()
                );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $this->saveCache($cacheKey, $points);
        return $this->getCache($cacheKey);
    }

    /**
     * calculate earning for quote/order item
     * 
     * @param Varien_Object $item
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getCatalogItemEarningPoints($item, $customerGroupId = null, $websiteId = null, $date = null) {
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        if ($product->getRewardpointsSpend() != null && $product->getRewardpointsSpend() >= 0){
            return 0;
        }
        if ($product->getRewardpointsEarn() != null && $product->getRewardpointsEarn() >= 0) {
            return $product->getRewardpointsEarn() * $item->getQty();
        }
        if (is_null($customerGroupId)) {
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->getCustomerGroupId();
            }
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore($item->getStoreId())->getWebsiteId();
        }
//        if (is_null($date)) {
//            $date = date('Y-m-d', strtotime($item->getCreatedAt()));
//        }
        $cacheKey = "catalog_item_earning:{$item->getId()}:$customerGroupId:$websiteId";
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                    ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getCache($collectionKey);
        }

        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (is_array($catalogRules) && isset($catalogRules[$item->getId()])) {
            $catalog = $catalogRules[$item->getId()]['point_discount'] / Mage::app()->getStore($item->getStoreId())->convertPrice(1);
        } else
            $catalog = 0;
        $earnPointByTax = Mage::getStoreConfig('rewardpoints/earning/by_tax');
        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
            $price = 0;
            $profit = 0;
            foreach ($item->getChildren() as $child) {
                $price += $child->getQty() * ($child->getBasePrice() - $catalog);
                $profit += $child->getQty() * ($child->getBasePrice() - $child->getBaseCost());
            }
            $price = Mage::helper('rewardpoints/calculator')->getPrice($product, $price, $earnPointByTax, true);
        } else {
            $price = $item->getBasePrice();
            if (!$price && $item->getPrice()) {
                $price = $item->getPrice() / Mage::app()->getStore($item->getStoreId())->convertPrice(1);
            }
            $profit = $price - $item->getBaseCost();
            $price -= $catalog;
            $price = Mage::helper('rewardpoints/calculator')->getPrice($product, $price, $earnPointByTax, true);
        }
        foreach ($rules as $rule) {
            /**
             * end update
             */
            if ($rule->validate($product)) {
                $points += $this->calcCatalogPoint(
                        $rule->getSimpleAction(), $rule->getPointsEarned(), $price, $profit, $rule->getMoneyStep(), $rule->getMaxPointsEarned()
                );
				if($points * $item->getQty() >= 1){
					$rule->setUsed(1);
				}
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
		if(!Mage::registry("rp_catalog_rules")){
        	Mage::register("rp_catalog_rules", $rules);
        }
		
        $this->saveCache($cacheKey, $points * $item->getQty());
        return $this->getCache($cacheKey);
    }

    /**
     * Calculate points for product by Catalog Rule
     * 
     * @param type $actionOperator is action type when chose at action in created rule
     * @param type $xAmount
     * @param type $price
     * @param type $profit
     * @param type $yStep
     * @param type $maxAmount is max amount could earned when was input ago
     * @return int
     */
    public function calcCatalogPoint($actionOperator, $xAmount, $price, $profit, $yStep, $maxAmount) {
        $points = 0;
        switch ($actionOperator) {
            case 'fixed':
                $points = $xAmount;
                break;
            case 'by_price':
                if ($yStep > 0) {
                    $points = $this->round($price / $yStep) * $xAmount;
                    if ($maxAmount && $points > $maxAmount) {
                        $points = $maxAmount;
                    }
                }
                break;
            case 'by_profit':
                if ($yStep > 0) {
                    $points = $this->round($profit / $yStep) * $xAmount;
                    if ($maxAmount && $points > $maxAmount) {
                        $points = $maxAmount;
                    }
                }
                break;
        }
        return (int) $points;
    }

    /**
     * calculate earning point for order quote
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string $date
     * @return int
     */
    public function getShoppingCartPoints($quote, $customerGroupId = null, $websiteId = null, $date = null) {
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $customerGroupId = is_null($customerGroupId) ? $quote->getCustomerGroupId() : $customerGroupId;
        $websiteId = is_null($websiteId) ? Mage::app()->getStore($quote->getStoreId())->getWebsiteId() : $websiteId;
        $points = 0;

        $rules = Mage::getResourceModel('rewardpointsrule/earning_sales_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
        $items = $quote->getAllItems();
        $this->setStoreId($quote->getStoreId());
        foreach ($rules as $rule) {
            $rule->afterLoad();
            if (!$rule->validate($address)) {
                continue;
            }
            $rowTotal = 0;
            $qtyTotal = 0;
            foreach ($items as $item) {
                if ($item->getParentItemId())
                    continue;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        if ($rule->getActions()->validate($child)) {
                            $qtyTotal += $child->getQty();
                            $rowTotal += max(0, $item->getQty() * ($child->getQty() * $child->getBasePrice()) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount());
                            if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX, $quote->getStoreId())) {
                                $rowTotal += $child->getBaseTaxAmount();
                            }
                        }                        
                    }
                }else if($item->getProduct()){
                    if ($rule->getActions()->validate($item)) {
                        $qtyTotal += $item->getQty();
                        $rowTotal += max(0, $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount());
                        if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX, $quote->getStoreId())) {
                            $rowTotal += $item->getBaseTaxAmount();
                        }
                    }  
                }
            }
            if (!$qtyTotal) continue;
            if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_SHIPPING, $quote->getStoreId())) {
                $rowTotal += $address->getBaseShippingAmount() - $address->getMagestoreBaseDiscountForShipping();
                if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX, $quote->getStoreId())) {
                    $rowTotal += $address->getBaseShippingTaxAmount();
                }
            }
            $pointsRule = $this->calcSalesPoints(
                    $rule->getSimpleAction(), $rule->getPointsEarned(), $rule->getMoneyStep(), $rowTotal, $rule->getQtyStep(), $qtyTotal, $rule->getMaxPointsEarned()
            );
            $points += $pointsRule;
			if($points >= 1){
				$rule->setUsed(1);
            }
            $this->_updateEarningPoints($address, $pointsRule, $rowTotal, $qtyTotal, $rule);
            if ($pointsRule && $rule->getStopRulesProcessing()) {
                break;
            }
        }
		 if(!Mage::registry("rp_shoppingcart_rules")){
        	Mage::register("rp_shoppingcart_rules", $rules);
        }
		
        return $points;
    }
    protected function _updateEarningPoints($address, $points, $rowTotal, $qtyTotal, $rule) {
        // Update for items
        $deltaRound = 0; //Brian
        foreach ($address->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId()) continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if ($rule->getActions()->validate($child)) {
                        $baseItemPrice = $item->getQty() * ($child->getQty() * $child->getBasePrice()) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                        if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX)) {
                            $baseItemPrice += $child->getBaseTaxAmount();
                        }
                        $itemQty = $item->getQty() * $child->getQty();
                        if ($rule->getSimpleAction() == 'by_qty' || $rule->getSimpleAction() == 'fixed') {
                            $realItemEarning = $itemQty * $points / $qtyTotal + $deltaRound;
                        } else {
                            $realItemEarning = $baseItemPrice * $points / $rowTotal + $deltaRound;
                        }
                        $itemEarning = Mage::helper('rewardpoints/calculator')->round($realItemEarning);
                        $deltaRound = $realItemEarning - $itemEarning;
                        $child->setRewardpointsEarn($child->getRewardpointsEarn() + $itemEarning);
                    }
                }
            } elseif ($item->getProduct()) {
                if ($rule->getActions()->validate($item)) {
                    $baseItemPrice = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                    if (Mage::getStoreConfigFlag(self::XML_PATH_EARNING_BY_TAX)) {
                        $baseItemPrice += $item->getBaseTaxAmount();
                    }
                    $itemQty = $item->getQty();
                    if ($rule->getSimpleAction() == 'by_qty' || $rule->getSimpleAction() == 'fixed') {
                        $realItemEarning = $itemQty * $points / $qtyTotal + $deltaRound;
                    } else {
                        $realItemEarning = $baseItemPrice * $points / $rowTotal + $deltaRound;
                    }
                    $itemEarning = Mage::helper('rewardpoints/calculator')->round($realItemEarning);
                    $deltaRound = $realItemEarning - $itemEarning;
                    $item->setRewardpointsEarn($item->getRewardpointsEarn() + $itemEarning);
                }
            }
        }
    }

    /**
     * Calculate the point received for shopping cart rule
     * 
     * @param string $pointOperation
     * @param float $xAmount
     * @param float $yStep
     * @param float $price
     * @param int $qtyStep
     * @param float $qty
     * @param int $maxPoint
     * @return int
     */
    public function calcSalesPoints($pointOperation, $xAmount, $yStep, $price, $qtyStep, $qty, $maxPoint) {
        $points = 0;
        switch ($pointOperation) {
            case 'fixed':
                $points = $xAmount;
                break;
            case 'by_total':
                if ($yStep > 0) {
                    $points = $this->round($price / $yStep) * $xAmount;
                    if ($maxPoint && $points > $maxPoint) {
                        $points = $maxPoint;
                    }
                }
                break;
            case 'by_qty':
                if ($qtyStep > 0) {
                    $points = $this->round($qty / $qtyStep) * $xAmount;
                    if ($maxPoint && $points > $maxPoint) {
                        $points = $maxPoint;
                    }
                }
                break;
        }
        return (int) $points;
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

}
