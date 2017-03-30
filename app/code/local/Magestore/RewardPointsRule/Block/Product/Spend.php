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
 * Spend point for product
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Product_Spend extends Magestore_RewardPoints_Block_Template
{
    /**
     * get spending calculation
     * 
     * @return Magestore_Customerreward_Helper_Calculation_Spending
     */
    public function getCalculation()
    {
        return Mage::helper('rewardpointsrule/calculation_spending');
    }

    /**
     * get spending rules
     * 
     * @return array
     */
    public function getSpendingRules()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return array();
        }
        $product = $this->getProduct();
        if (!$product) {
            return array();
        }
        return $this->getCalculation()->getProductSpendingRules($product);
    }

    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    public function isShowRedeemRules()
    {
        if (!$this->getProduct()) {
            return false;
        }
        if ($this->getProduct()->isGrouped()) {
            return false;
        }
        if ($this->getProduct()->getFinalPrice() < 0.0001 || $this->getProduct()->getRewardpointsSpend() >0.0001) {
            return false;
        }
        return true;
    }
    public function isShowRedeemRulesGrouped()
    {
        if(!Mage::helper('customer')->isLoggedIn()){
            return false;
        }
        if (!$this->getProduct()) {
            return false;
        }
        if ($this->getProduct()->isGrouped()) {
            return true;
        }
        return false;
    }

    public function getPriceFormatJs()
    {
        $priceFormat = Mage::app()->getLocale()->getJsPriceFormat();
        return Mage::helper('core')->jsonEncode($priceFormat);
    }

    /**
     * get JSON string used for JS
     * 
     * @param array $rules
     * @return string
     */
    public function getProductRulesJson($rules = null, $product = null, $productFinalPrice = null)
    {
        if (is_null($rules)) {
            $rules = $this->getSpendingRules();
        }
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if (is_null($productFinalPrice)) {
            $productFinalPrice = $product->getFinalPrice();
        }
        $result = array();
        $isCustomerLogin = $this->getCustomer()->getId();
        foreach ($rules as $rule) {
            $ruleOptions = array();
            if ($isCustomerLogin) {
                $minPoins = $this->getMinRulePoint($rule);
                $this->getCustomerPoint();
                $totalPoints = Mage::helper('rewardpoints/customer')->getBalance();
                $minRedeem   = (int)Mage::getStoreConfig(
                    Magestore_RewardPoints_Helper_Customer::XML_PATH_REDEEMABLE_POINTS
                );
                if ($this->getCustomerPoint() < $minPoins || ($minRedeem && $totalPoints < $minRedeem)) {
                    $ruleOptions['optionType'] = 'needPoint';
                    $ruleOptions['needPoint'] = max($minPoins - $this->getCustomerPoint(), $minRedeem - $totalPoints);
                } else {
                    $price = Mage::helper('tax')->getPrice($product, $productFinalPrice);
                    $sliderOption = array(
                        'minPoints' => $minPoins,
                        'pointStep' => $minPoins,
                    );
                    $ruleDiscount = $this->getRuleDiscount($rule);
                    
                    //Hai.Tran 12/11/2013
                    $maxPriceSpend = $rule->getMaxPriceSpendedValue()>0 ? $rule->getMaxPriceSpendedValue() : 0;
                    if($rule->getMaxPriceSpendedType() == 'by_price'){
                        $ruleOptions['maxDiscount'] = $maxPriceSpend;
                    }elseif($rule->getMaxPriceSpendedType() == 'by_percent'){
                        $ruleOptions['maxDiscount'] = $price*$maxPriceSpend/100;
                    }else
                        $ruleOptions['maxDiscount'] = 0;
                    if($ruleOptions['maxDiscount'] > 0 && $ruleOptions['maxDiscount'] < $price) $price = $ruleOptions['maxDiscount'];
                    //End Hai.Tran
                    
                    if ($ruleDiscount < 0.0001) {
                        $ruleOptions['optionType'] = 'static';
                        $ruleOptions['stepDiscount'] = 0;
                    } else {
                        $ruleOptions['stepDiscount'] = Mage::app()->getStore()->convertPrice($ruleDiscount);
                        $ruleOptions['optionType'] = 'slider';
                        $maxPoints = $this->getCustomerPoint();
                        
                        $zMaxPoints = ceil($price / $ruleDiscount) * $sliderOption['pointStep'];
                        if ($maxPoints >= $zMaxPoints) {
                            $maxPoints = $zMaxPoints;
                        } else {
                            $maxPoints = floor($maxPoints / $sliderOption['pointStep']) * $sliderOption['pointStep'];
                        }
                        if ($timeUses = (int)$rule->getUsesPerProduct()) {
                            $zMaxPoints = $timeUses * $sliderOption['pointStep'];
                            if ($maxPoints > $zMaxPoints) {
                                $maxPoints = $zMaxPoints;
                            }
                        }
                        if ($maxPoints == $sliderOption['pointStep']) {
                            $ruleOptions['optionType'] = 'static';
                        } else {
                            $sliderOption['maxPoints'] = (int) $maxPoints;
                        }
                    }
                    
                    $ruleOptions['sliderOption'] = $sliderOption;
                }
            } else {
                $ruleOptions['optionType'] = 'login';
            }
            $result[$rule->getId()] = $ruleOptions;
        }
        return Mage::helper('core')->jsonEncode($result);
    }
    public function getProductRulesJsonGrouped(){
        $product = $this->getProduct();
        if($product->isGrouped()){
            $result = array();
            $groupedProduct = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach($groupedProduct as $productGroup){
                $rule = $this->getCalculation()->getProductSpendingRules($productGroup);
                if(count($rule) > 0){
                    $ruleJson = $this->getProductRulesJson($rule, $productGroup);
                    $result[$productGroup->getId()] = Mage::helper('core')->jsonDecode($ruleJson);
                }
            }
        }
        return Mage::helper('core')->jsonEncode($result);
    }

    public function getMinRulePoint($rule)
    {
        if ($rule->getSimpleAction() == 'fixed') {
            return (int)$rule->getPointsSpended();
        }
        $price = $this->getProduct()->getFinalPrice();
        if ($rule->getMoneyStep() < 0.0001) {
            $minPoins = 0;
        } else {
            $minPoins = floor($price / $rule->getMoneyStep()) * $rule->getPointsSpended();
        }
        if ($rule->getMaxPointsSpended() && $minPoins > $rule->getMaxPointsSpended()) {
            $minPoins = $rule->getMaxPointsSpended();
        }
        return (int)$minPoins;
    }

    public function getCustomerPoint()
    {
        if ($this->hasData('customer_point')) {
            return $this->getData('customer_point'); 
        }
        $points  = Mage::helper('rewardpoints/customer')->getBalance();
        $points -= $this->getCalculation()->getCatalogSpendingPoints();
        if ($points < 0) {
            $points = 0;
        }
        
        $session = Mage::getSingleton('checkout/session');
		
        $shopingCartSpending = $session->getRewardSalesRules();
        if (is_array($shopingCartSpending)&& isset($shopingCartSpending['use_point'])) $points -= $shopingCartSpending['use_point'];
        $points -= Mage::helper('rewardpointsrule/calculation_spending')->getCheckedRulePointWithout();
		
        $catalogRules = $session->getCatalogRules();
        $id = $this->getRequest()->getParam('id');
        $pointTemp = 0;
        if (isset($catalogRules[$id])) {
            $pointTemp = $catalogRules[$id]['point_used'] * $catalogRules[$id]['item_qty'];
        }
        
        $this->setData('customer_point', $points + $pointTemp);
        return $this->getData('customer_point');
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getRewardFormData()
    {
        $request = Mage::app()->getRequest();
        $action = $request->getRequestedRouteName() . '_' . $request->getRequestedControllerName() . '_' . $request->getRequestedActionName();
        if ($action == 'checkout_cart_configure' && $request->getParam('id')) {
            $session = Mage::getSingleton('checkout/session');
            $catalogRules = $session->getCatalogRules();
            if (isset($catalogRules[$request->getParam('id')])) {
                $ruleItemData = $catalogRules[$request->getParam('id')];
                return new Varien_Object(array(
                    'reward_product_rule' => isset($ruleItemData['rule_id']) ? $ruleItemData['rule_id'] : '',
                    'reward_product_point' => isset($ruleItemData['point_used']) ? $ruleItemData['point_used'] : '',
                ));
            }
        }
        return new Varien_Object;
    }

    /**
     * get Rule discount (each step)
     * 
     * @param type $rule
     * @return float
     */
    public function getRuleDiscount($rule)
    {
        $price = $this->getProduct()->getPrice();
        return $this->getCalculation()->getCatalogRuleDiscount($rule, $price);
    }
    
    /**
     * get reward points helper
     * 
     * @return Magestore_RewardPoints_Helper_Point
     */
    public function getPointHelper()
    {
        return Mage::helper('rewardpoints/point');
    }
    
    /**
     * call method that defined from block helper
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        $helper = Mage::helper('rewardpoints/block_spend');
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
            // return call_user_method_array($method, $helper, $args);
        }
        return parent::__call($method, $args);
    }
}
