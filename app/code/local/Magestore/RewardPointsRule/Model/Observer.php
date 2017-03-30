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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Model_Observer
{
    protected $_totalEarning = 0;
    
    protected $_flag = array();
    
    /**
     * 
     * @param string $key
     * @return boolean
     */
    public function getFlag($key)
    {
        if (isset($this->_flag[$key])) {
            return (boolean)$this->_flag[$key];
        }
        return false;
    }
    
    /**
     * 
     * @param string $key
     * @param boolean $value
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function setFlag($key, $value = true)
    {
        $this->_flag[$key] = (boolean)$value;
        return $this;
    }
    
    /**
     * collect catalog earning by rules
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function collectCatalogEarning($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $address = $observer['address'];
        $items = $address->getAllItems();
        
        $points = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $itemPoint = Mage::helper('rewardpointsrule/calculation_earning')
                ->getCatalogItemEarningPoints($item);
            //Brian fix earn point for parent_child item
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                $parentItemsPrice = $item->getPrice();
                $childs = $item->getChildren();
                $childQty = count($childs);
                $pointExist = $itemPoint;
                foreach ($childs as $child) {
                    $childQty--;
                    if($childQty == 0) {
                        $child->setRewardpointsEarn($child->getRewardpointsEarn() + $pointExist);
                        break;
                    }
                    $itemsPrice = $item->getQty() * ($child->getQty() * $child->getPrice()) - $child->getDiscountAmount();
                    $itemEarning = round($itemsPrice * $itemPoint / $parentItemsPrice, 0, PHP_ROUND_HALF_DOWN);
                    $child->setRewardpointsEarn($child->getRewardpointsEarn() + $itemEarning);
                    $pointExist -= $itemEarning;
                }
            } else {
                $item->setRewardpointsEarn($item->getRewardpointsEarn() + $itemPoint);
            }
            //end
            $points += $itemPoint;
        }
        $address->setRewardpointsEarn($address->getRewardpointsEarn() + $points);
        if (!$this->getFlag('collect_catalog_earning')) {
            $this->_totalEarning += $points;
            $this->setFlag('collect_catalog_earning');
        }
        return $this;
    }
    
    /**
     * collect shopping cart earning by rule
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function collectShoppingCartEarning($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $address = $observer['address'];
        
        $points = Mage::helper('rewardpointsrule/calculation_earning')
            ->getShoppingCartPoints($address->getQuote());
        if ($points > 0) {
            $address->setRewardpointsEarn($points);
            $this->setFlag('earning_by_shoppingcart_rule');
        }
        if (!$this->getFlag('collect_shoppingcart_earning')) {
            $this->_totalEarning += $points;
            $this->setFlag('collect_shoppingcart_earning');
        }
        return $this;
    }
    
    /**
     * calculation total earning points to display on block total
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculationEarningTotalPointsBlock($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container  = $observer['container'];
        $quote      = $observer['quote'];
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        if (!$this->getFlag('collect_catalog_earning')) {
            $this->collectCatalogEarning(array('address' => $address));
        }
        if (!$this->getFlag('collect_shoppingcart_earning')) {
            $this->collectShoppingCartEarning(array('address' => $address));
        }
        if ($this->getFlag('earning_by_shoppingcart_rule')) {
            $container->setTotalPoints($this->_totalEarning);
        } else {
            $container->setTotalPoints($container->getTotalPoints() + $this->_totalEarning);
        }
        return $this;
    }
    
    /**
     * Spend for product by catalog rule
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function checkoutCartAddProduct($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $quoteItem = $observer['quote_item'];
        $product = $observer['product'];
        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }
        if (!$quoteItem->getId()) {
            try {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
                if (!$quote->getId()) {
                    $quote->save();
                }
                $quoteItem->setQuoteId($quote->getId())->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        if (!$quoteItem->getId()) {
            return $this;
        }
        if ($this->getFlag('added_for_item_' . $quoteItem->getId())) {
            return $this;
        }
        $this->setFlag('added_for_item_' . $quoteItem->getId());
        // Fix for Promotional Gift Extension
        if ($itemOptions = $quoteItem->getOptions()) {
            foreach($itemOptions as $option) {
                $codeData = $option->getData('code');
                if ($codeData == 'option_promotionalgift_catalogrule') {
                    return $this;
                }
            }
        }
        
        $request = Mage::app()->getRequest();
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        if(!$product->isGrouped()){
            if ($ruleId = $request->getParam('reward_product_rule')) {
                $catalogRules[$quoteItem->getId()] = array(
                    'item_id'   => $quoteItem->getId(),
                    'item_qty'  => $quoteItem->getQty(),
                    'rule_id'   => $ruleId,
                    'point_used'    => $request->getParam('reward_product_point'),
                    'base_point_discount'   => null,
                    'point_discount'        => null,
                    'type'      => 'catalog_spend'
                );
            } elseif (isset($catalogRules[$quoteItem->getId()])) {
                unset($catalogRules[$quoteItem->getId()]);
            }
        }
        $session->setCatalogRules($catalogRules);
        
        return $this;
    }
    public function salesQuoteAddProduct($observer){
        $items = $observer['items'];
        $request = Mage::app()->getRequest();
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        foreach($items as $item){
            if (!$item->getId()) {
                try {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    if (!$quote->getId()) {
                        $quote->save();
                    }
                    $item->setQuoteId($quote->getId())->save();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
            if (!$item->getId()) {
                continue;
            }
            if ($ruleId = $request->getParam('reward_product_rule_hi'.$item->getProductId())) {
                $catalogRules[$item->getId()] = array(
                    'item_id'   => $item->getId(),
                    'item_qty'  => $item->getQty(),
                    'rule_id'   => $ruleId,
                    'point_used'    => $request->getParam('reward_product_point'.$item->getProductId()),
                    'base_point_discount'   => null,
                    'point_discount'        => null,
                    'type'      => 'catalog_spend'
                );
            } elseif (isset($catalogRules[$item->getId()])) {
                unset($catalogRules[$item->getId()]);
            }
        }
        $session->setCatalogRules($catalogRules);
        return $this;
    }
    
    /**
     * calculate points that spent for item
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculateSpendingPointItem($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $item       = $observer['item'];
        $container  = $observer['container'];
        
        $container->setPointItemSpent(
            $container->getPointItemSpent() + 
            Mage::helper('rewardpointsrule/calculation_spending')->getCatalogSpendingPoints($item)
        );
        
        return $this;
    }
    
    public function calculateSpendingPointDiscount($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $item       = $observer['item'];
        $container  = $observer['container'];
        
        $container->setPointItemDiscount(
            $container->getPointItemDiscount() + 
            Mage::helper('rewardpointsrule/calculation_spending')->getItemDiscount($item)
        );
        
        return $this;
    }
    
    /**
     * Add spending rules for block to display on shopping cart/ checkout page
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function addBlockSpendingRules($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container = $observer['container'];
        $container->setSpendingRules(
            Mage::helper('rewardpointsrule/calculation_spending')->getQuoteSpendingRules()
        );
        return $this;
    }
    
    /**
     * event process get spending quote rule model to collect point total
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function getSpendingQuoteRuleModel($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container = $observer['container'];
        $ruleId    = $observer['rule_id'];
        $container->setQuoteRuleModel(
            Mage::getModel('rewardpointsrule/spending_sales')->load($ruleId)
        );
        return $this;
    }
    
    /**
     * Calculate max points use for a rule (slider rules)
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculateSpendingRuleMaxPoints($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $rule       = $observer['rule'];
        $quote      = $observer['quote'];
        $container  = $observer['container'];
        
        $helper     = Mage::helper('rewardpointsrule/calculation_spending');
        /* @var $helper Magestore_RewardPointsRule_Helper_Calculation_Spending */
        
        $maxPoints  = 0;
        $baseTotal  = $helper->getQuoteBaseTotal($quote) - $helper->getCheckedRuleDiscount();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$rule->getActions()->validate($child)) {
                        $baseTotal -= $item->getQty() * ($child->getQty() * $helper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$rule->getActions()->validate($item)) {
                    $baseTotal -= $item->getQty() * $helper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                }
            }
        }
        //Tinh max point cho max total
        $maxPrice = $rule->getMaxPriceSpendedValue()>0 ? $rule->getMaxPriceSpendedValue() : 0;
        if ($rule->getDiscountStyle() == 'cart_fixed') {
            if ($rule->getDiscountAmount()) {                
                if($rule->getMaxPriceSpendedType() == 'by_price'){
                    $maxPriceSpend = $maxPrice;
                }elseif($rule->getMaxPriceSpendedType() == 'by_percent'){
                    $maxPriceSpend = $baseTotal*$maxPrice/100;
                }else{
                    $maxPriceSpend = 0;
                }
                if($baseTotal > $maxPriceSpend && $maxPriceSpend > 0) $baseTotal = $maxPriceSpend;

                $maxPoints = ceil($baseTotal / $rule->getDiscountAmount()) * $rule->getPointsSpended();
            }
        } else {
            if ($rule->getDiscountAmount()) {
                $percent =100;
                if($rule->getMaxPriceSpendedType() == 'by_price'){
                    $maxPercentSpend = $maxPrice/$baseTotal*100;
                }elseif($rule->getMaxPriceSpendedType() == 'by_percent'){
                    $maxPercentSpend = $maxPrice;
                }else{
                    $maxPercentSpend = 0;
                }
                if($percent > $maxPercentSpend && $maxPercentSpend > 0) $percent = $maxPercentSpend;
                $maxPoints = ceil($percent / $rule->getDiscountAmount()) * $rule->getPointsSpended();
            }
        }
        //End Hai.Tran 13/11/2013 add limit spend theo quote total
        
        if ($maxPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
            $maxPerOrder -= $helper->getPointItemSpent();
            $maxPerOrder -= $helper->getCheckedRulePoint();
            if ($maxPerOrder > 0 && $maxPoints) {
                $maxPoints = min($maxPoints, $maxPerOrder);
                $maxPoints = floor($maxPoints / $rule->getPointsSpended()) * $rule->getPointsSpended();
            }
        }
        
        $container->setRuleMaxPoints($maxPoints);
        return $this;
    }
    
    /**
     * Calculate discount by a rule
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculateSpendingRuleDiscount($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $rule       = $observer['rule'];
        $quote      = $observer['quote'];
        $container  = $observer['container'];
        
        $helper     = Mage::helper('rewardpointsrule/calculation_spending');
        /* @var $helper Magestore_RewardPointsRule_Helper_Calculation_Spending */
        
        $points     = $container->getPoints();
        $discount   = $container->getQuoteRuleDiscount();
        
        $baseTotal  = $helper->getQuoteBaseTotal($quote);
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if (!$rule->getActions()->validate($child)) {
                        $baseTotal -= $item->getQty() * ($child->getQty() * $helper->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    }
                }
            } elseif ($item->getProduct()) {
                if (!$rule->getActions()->validate($item)) {
                    $baseTotal -= $item->getQty() * $helper->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                }
            }
        }
        //$availableTotal = $baseTotal - $helper->getCheckedRuleDiscountWithout($rule->getId());
        /** Brian limit spending discount **/
        $maxDiscountSpended = 0;
        if($maxPriceSpended = $rule->getMaxPriceSpendedValue()){
            if($rule->getMaxPriceSpendedType() == 'by_price'){
                $maxDiscountSpended = $maxPriceSpended;
            }elseif($rule->getMaxPriceSpendedType() == 'by_percent'){
                $maxDiscountSpended = $baseTotal*$maxPriceSpended/100;
            }
        }
        $availableTotal = $maxDiscountSpended > 0 ? min($maxDiscountSpended, $baseTotal) : $baseTotal;
        /** end **/
        if ($availableTotal <= 0) {
            $points     = 0;
            $discount   = 0;
        } else if ($rule->getDiscountStyle() == 'cart_fixed') {
            if ($rule->getPointsSpended() <= 0) {
                $points     = 0;
                $discount   = min($rule->getDiscountAmount(), $availableTotal);
            } else {
                if ($maxPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $helper->getPointItemSpent();
                    $maxPerOrder -= $helper->getCheckedRulePointWithout($rule->getId());
                    $points = min($points, max($maxPerOrder, 0));
                }
                if ($rule->getDiscountAmount() > 0) {
                    $points = min($points, ceil($availableTotal / $rule->getDiscountAmount()) * $rule->getPointsSpended());
                    $points = floor($points / $rule->getPointsSpended()) * $rule->getPointsSpended();
                    $discount = min($rule->getDiscountAmount() * $points / $rule->getPointsSpended(), $availableTotal);
                } else {
                    $points = 0;
                    $discount = 0;
                }
            }
        } else {
            if ($rule->getPointsSpended() <= 0) {
                $points = 0;
                $discountPer = min($rule->getDiscountAmount(), 100);
                $discount = min($discountPer * $baseTotal / 100, $availableTotal);
            } else if ($rule->getDiscountAmount() <= 0) {
                $points = 0;
                $discount = 0;
            } else {
                if ($maxPerOrder = $helper->getMaxPointsPerOrder($quote->getStoreId())) {
                    $maxPerOrder -= $helper->getPointItemSpent();
                    $maxPerOrder -= $helper->getCheckedRulePointWithout($rule->getId());
                    $points = min($points, max($maxPerOrder, 0));
                }
                $points = min($points, ceil(100 * ($availableTotal / $baseTotal) / $rule->getDiscountAmount()) * $rule->getPointsSpended());
                $points = floor($points / $rule->getPointsSpended()) * $rule->getPointsSpended();
                $discountPer = $rule->getDiscountAmount() * $points / $rule->getPointsSpended();
                $discountPer = min($discountPer, 100 * $availableTotal / $baseTotal);
                $discount = min($discountPer * $baseTotal / 100, $availableTotal);
            }
        }
        $container->setPoints($points);
        $container->setQuoteRuleDiscount($discount);
        return $this;
    }
    
    /**
     * Calculate discount for all checked rules
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculateCheckedRuleDiscount($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container  = $observer['container'];
        $helper     = Mage::helper('rewardpointsrule/calculation_spending');
        /* @var $helper Magestore_RewardPointsRule_Helper_Calculation_Spending */
        
        $container->setCheckedRuleDiscount(
            $helper->getCheckedRuleDiscountWithout()
        );
        return $this;
    }
    
    /**
     * Calculate points for all checked rules
     * 
     * @param type $observer
     * @return Magestore_RewardPointsRule_Model_Observer
     */
    public function calculateCheckedRulePoint($observer)
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return $this;
        }
        $container  = $observer['container'];
        $helper     = Mage::helper('rewardpointsrule/calculation_spending');
        /* @var $helper Magestore_RewardPointsRule_Helper_Calculation_Spending */
        
        $container->setCheckedRulePoint(
            $helper->getCheckedRulePointWithout()
        );
        return $this;
    }
    
    public function applyAllRulesOnProduct($observer){
        $product = $observer->getEvent()->getProduct();
        if ($product->getIsMassupdate()) {
            return $this;
        }

        $datas = Mage::getSingleton('rewardpointsrule/indexer_product')
                        ->getIndexProduct($product);
        if(count($datas) == 0) return $this;
        
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getModel('core/resource')->getTableName('rewardpoints_earning_product');
        $connection->beginTransaction();
        try {
            $connection->delete($table, array($connection->quoteInto('product_id=?', $product->getId())));
            foreach($datas as $data){
                $connection->insert($table, $data);
            }
            $connection->commit();            
        } catch (Exception $ex) {
            $connection->rollBack();
            //throw $e;
        }

        return $this;
    }
}
