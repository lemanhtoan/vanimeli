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
class Magestore_RewardPoints_Model_Total_Quote_Point extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    protected $_hiddentBaseDiscount = 0;
    protected $_hiddentDiscount = 0;

    /**
     * Magestore_RewardPoints_Model_Total_Quote_Point constructor.
     */
    public function __construct() {
        $this->setCode('rewardpoints');
    }

    /**
     * collect reward points total
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_RewardPoints_Model_Total_Quote_Point
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        if (!$applyTaxAfterDiscount) {
            return $this;
        }
        if (!Mage::helper('rewardpoints')->isEnable($quote->getStoreId())) {
            return $this;
        }
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $session = Mage::getSingleton('checkout/session');
        if (!$session->getData('use_point')) {
            return $this;
        }
        $rewardSalesRules = $session->getRewardSalesRules();
        $rewardCheckedRules = $session->getRewardCheckedRules();
        if (!$rewardSalesRules && !$rewardCheckedRules) {
            return $this;
        }

        /** @var $helper Magestore_RewardPoints_Helper_Calculation_Spending */
        $helper = Mage::helper('rewardpoints/calculation_spending');

        $baseTotal = $helper->getQuoteBaseTotal($quote, $address);
        $maxPoints = Mage::helper('rewardpoints/customer')->getBalance();
        if ($maxPointsPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
            $maxPoints = min($maxPointsPerOrder, $maxPoints);
        }
        $maxPoints -= $helper->getPointItemSpent();
        if ($maxPoints <= 0) {
            $session->setRewardCheckedRules(array());
            $session->setRewardSalesRules(array());
            return $this;
        }

        $baseDiscount = 0;
        $pointUsed = 0;

        // Checked Rules Discount First
        if (is_array($rewardCheckedRules)) {
            $newRewardCheckedRules = array();
            foreach ($rewardCheckedRules as $ruleData) {
                if ($baseTotal < 0.0001)
                    break;
                $rule = $helper->getQuoteRule($ruleData['rule_id']);
                if (!$rule || !$rule->getId() || $rule->getSimpleAction() != 'fixed') {
                    continue;
                }
                if ($maxPoints < $rule->getPointsSpended()) {
                    $session->addNotice($helper->__('You cannot spend more than %s points per order', $helper->getMaxPointsPerOrder($quote->getStoreId())));
                    continue;
                }
                $points = $rule->getPointsSpended();
                $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                if ($ruleDiscount < 0.0001) {
                    continue;
                }

                $baseTotal -= $ruleDiscount;
                $maxPoints -= $points;

                $baseDiscount += $ruleDiscount;
                $pointUsed += $points;

                $newRewardCheckedRules[$rule->getId()] = array(
                    'rule_id' => $rule->getId(),
                    'use_point' => $points,
                    'base_discount' => $ruleDiscount,
                );
                $this->_prepareDiscountForTaxAmount($address, $ruleDiscount, $points, $rule);
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
            $session->setRewardCheckedRules($newRewardCheckedRules);
        }

        // Sales Rule (slider) discount Last
        if (is_array($rewardSalesRules)) {
            $newRewardSalesRules = array();
            if ($baseTotal > 0.0 && isset($rewardSalesRules['rule_id'])) {
                $rule = $helper->getQuoteRule($rewardSalesRules['rule_id']);
                if ($rule && $rule->getId() && $rule->getSimpleAction() == 'by_price') {
                    $points = min($rewardSalesRules['use_point'], $maxPoints);
                    $ruleDiscount = $helper->getQuoteRuleDiscount($quote, $rule, $points);
                    if ($ruleDiscount > 0.0) {
                        $baseTotal -= $ruleDiscount;
                        $maxPoints -= $points;

                        $baseDiscount += $ruleDiscount;
                        $pointUsed += $points;

                        $newRewardSalesRules = array(
                            'rule_id' => $rule->getId(),
                            'use_point' => $points,
                            'base_discount' => $ruleDiscount,
                        );
                        if($rule->getId() == 'rate'){
                            $this->_prepareDiscountForTaxAmount($address, $ruleDiscount, $points);
                        } else {
                            $this->_prepareDiscountForTaxAmount($address, $ruleDiscount, $points, $rule);
                        }
                    }
                }
            }
            $session->setRewardSalesRules($newRewardSalesRules);
        }

        // verify quote total data
        if ($baseTotal < 0.0001) {
            $baseTotal = 0.0;
            $baseDiscount = $helper->getQuoteBaseTotal($quote, $address);
        }

        if ($baseDiscount) {
            // Prepare reward points discount and point spent for each item
            //$this->_prepareDiscountForTaxAmount($address, $baseDiscount, $pointUsed);

            $discount = Mage::app()->getStore()->convertPrice($baseDiscount);

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);

            $address->setRewardpointsSpent($address->getRewardpointsSpent() + $pointUsed);
            $address->setRewardpointsBaseDiscount($address->getRewardpointsBaseDiscount() + $baseDiscount);
            $address->setRewardpointsDiscount($address->getRewardpointsDiscount() + $discount);

            $quote->setRewardpointsBaseDiscount($address->getRewardpointsBaseDiscount());
            $quote->setRewardpointsDiscount($address->getRewardpointsDiscount());

            $address->setMagestoreBaseDiscount($address->getMagestoreBaseDiscount() + $baseDiscount);
        }

        return $this;
    }

    /**
     * add spending points row into quote total
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_RewardPoints_Model_Total_Quote_Point
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        if (!$applyTaxAfterDiscount) {
            return $this;
        }
        $productPoints = $address->getPointSpentForProducts();
        if ($amount = $address->getRewardpointsDiscount()) {
            if ($points = $address->getRewardpointsSpent() - $productPoints) {
                $title = Mage::helper('rewardpoints')->__('Use point (%s)', Mage::helper('rewardpoints/point')->format($points, $address->getQuote()->getStoreId())
                );
            } else {
                $title = Mage::helper('rewardpoints')->__('Use point on spend');
            }
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => -$amount,
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
    public function _prepareDiscountForTaxAmount(Mage_Sales_Model_Quote_Address $address, $baseDiscount, $points, $rule = null) {
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
                    if($rule!==null && !$rule->getActions()->validate($child)) continue;
                    $baseParentItemsPrice[$item->getId()] += $item->getQty() * ($child->getQty() * $spendHelper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                }
                $baseItemsPrice += $baseParentItemsPrice[$item->getId()];
            } elseif ($item->getProduct()) {
                if($rule!==null && !$rule->getActions()->validate($item)) continue;
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
                    if($rule!==null && !$rule->getActions()->validate($child)) continue;
                    $baseItemPrice = $item->getQty() * ($child->getQty() * $spendHelper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    $itemBaseDiscount = min($baseItemPrice, $parentItemBaseDiscount); //$baseDiscount * $baseItemPrice / $baseItemsPrice;
                    $parentItemBaseDiscount -= $itemBaseDiscount;
                    $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                    $pointSpent = round($points * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_DOWN);
                    $child->setRewardpointsBaseDiscount($child->getRewardpointsBaseDiscount() + $itemBaseDiscount)
                            ->setRewardpointsDiscount($child->getRewardpointsDiscount() + $itemDiscount)
                            ->setMagestoreBaseDiscount($child->getMagestoreBaseDiscount() + $itemBaseDiscount)
                            ->setRewardpointsSpent($child->getRewardpointsSpent() + $pointSpent);
                    $baseTaxableAmount = $child->getBaseTaxableAmount();
                    $taxableAmount = $child->getTaxableAmount();
                    $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $itemBaseDiscount));
                    $child->setTaxableAmount(max(0, $taxableAmount - $itemDiscount));

                    if (Mage::helper('tax')->priceIncludesTax()) {
                        $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                        if ($rate > 0) {
                            $child->setRewardpointsBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate) + $child->getRewardpointsBaseHiddenTaxAmount());
                            $child->setRewardpointsHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate) + $child->getRewardpointsHiddenTaxAmount());
                        }
                    }
                }
            } elseif ($item->getProduct()) {
                if($rule!==null && !$rule->getActions()->validate($item)) continue;
                $baseItemPrice = $item->getQty() * $spendHelper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                $itemBaseDiscount = $baseDiscount * $baseItemPrice / $baseItemsPrice;
                $itemDiscount = Mage::app()->getStore()->convertPrice($itemBaseDiscount);
                $pointSpent = round($points * $baseItemPrice / $baseItemsPrice, 0, PHP_ROUND_HALF_DOWN);
                $item->setRewardpointsBaseDiscount($item->getRewardpointsBaseDiscount() + $itemBaseDiscount)
                        ->setRewardpointsDiscount($item->getRewardpointsDiscount() + $itemDiscount)
                        ->setMagestoreBaseDiscount($item->getMagestoreBaseDiscount() + $itemBaseDiscount)
                        ->setRewardpointsSpent($item->getRewardpointsSpent() + $pointSpent);

                $baseTaxableAmount = $item->getBaseTaxableAmount();
                $taxableAmount = $item->getTaxableAmount();
                $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $itemBaseDiscount));
                $item->setTaxableAmount(max(0, $taxableAmount - $itemDiscount));

                if (Mage::helper('tax')->priceIncludesTax()) {
                    $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                    if ($rate > 0) {
                        $item->setRewardpointsBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate) +  + $item->getRewardpointsBaseHiddenTaxAmount());
                        $item->setRewardpointsHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate) + $item->getRewardpointsHiddenTaxAmount());
                    }
                }
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
            $baseTaxableAmount = $address->getBaseShippingTaxable();
            $taxableAmount = $address->getShippingTaxable();
            $address->setBaseShippingTaxable(max(0, $baseTaxableAmount - $itemBaseDiscount));
            $address->setShippingTaxable(max(0, $taxableAmount - $itemDiscount));

            if (Mage::helper('tax')->shippingPriceIncludesTax()) {
                $rate = $this->getShipingTaxRate($address, $store);
                if ($rate > 0) {
                    $address->setRewardpointsBaseShippingHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($address->getBaseShippingTaxable(), $rate) + $address->getRewardpointsBaseShippingHiddenTaxAmount());
                    $address->setRewardpointsShippingHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($address->getShippingTaxable(), $rate) + $address->getRewardpointsShippingHiddenTaxAmount());
                }
            }
        }

        return $this;
    }

    //get Rate
    /**
     * @param $address
     * @param $product
     * @param $store
     * @return int
     */
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

    /**
     * @param $address
     * @param $store
     * @return mixed
     */
    public function getShipingTaxRate($address, $store) {
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
        );
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));
        $rate = Mage::getSingleton('tax/calculation')->getRate($request);
        return $rate;
    }

    /**
     * @param $price
     * @param $rate
     * @return mixed
     */
    public function calTax($price, $rate) {
        return $this->round(Mage::getSingleton('tax/calculation')->calcTaxAmount($price, $rate, true, false));
    }

    /**
     * @param $price
     * @return mixed
     */
    public function round($price) {
        return Mage::getSingleton('tax/calculation')->round($price);
    }

}
