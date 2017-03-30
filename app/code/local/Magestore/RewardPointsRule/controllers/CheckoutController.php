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
 * RewardPointsRule Checkout Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */

class Magestore_RewardPointsRule_CheckoutController extends Mage_Core_Controller_Front_Action
{
    /**
     * Remove catalog spending for quote item
     */
    public function removecatalogAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $catalogRules = $session->getCatalogRules();
        if (!is_array($catalogRules)) {
            $catalogRules = array();
        }
        $id = $this->getRequest()->getParam('id');
        if (isset($catalogRules[$id])) {
            if(isset($catalogRules[$id]['rule_id'])&& !$catalogRules[$id]['rule_id']){
		Mage::getSingleton('checkout/session')->getQuote()->removeItem($id)->save();
            }
            unset($catalogRules[$id]);
            $session->setCatalogRules($catalogRules);
            $session->addSuccess($this->__('The rule has been successfully removed.'));
        } else {
            $session->addError($this->__('Rule not found'));
        }
        $this->_redirect('checkout/cart/index');
    }
    //Hai.Tran 12/11/2013 fix priceoption ajax
    public function priceOptionAction(){
        $customerGroupId = null;
        $websiteId = null;
        $date = null;
        $price = $this->getRequest()->getParam('price');
        $price = str_replace(',', '',$price);
        $productId = $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);
        if (is_null($customerGroupId)) {
            if ($product->hasCustomerGroupId()) {
                $customerGroupId = $product->getCustomerGroupId();
            } else {
                $customerGroupId = $this->getEarningHelper()->getCustomerGroupId();
            }
        }
        if (is_null($websiteId)) {
            $websiteId = $this->getEarningHelper()->getWebsiteId();
        }
        $points = 0;
        $collectionKey = "catalog_earning_collection:$customerGroupId:$websiteId";
        if (!$this->getEarningHelper()->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->getEarningHelper()->saveCache($collectionKey, $rules);
        } else {
            $rules = $this->getEarningHelper()->getCache($collectionKey);
        }
        foreach ($rules as $rule) {
            if ($rule->validate($product)) {
                $points += $this->getEarningHelper()->calcCatalogPoint(
                                $rule->getSimpleAction(),
                                $rule->getPointsEarned(),
                                $price,
                                $price - $product->getCost(),
                                $rule->getMoneyStep(),
                                $rule->getMaxPointsEarned()
                            );
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        if($points > 0)
            echo Mage::helper('rewardpoints/point')->getImageHtml(true).' '.$this->__('You will earn %s for purchasing this product.', Mage::helper('rewardpoints/point')->format($points));
        else echo 'false';
        return;
    }
    function getEarningHelper(){
        return Mage::helper('rewardpointsrule/calculation_earning');
    }
}
