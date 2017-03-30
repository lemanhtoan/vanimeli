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
 * RewardPoints Checkout Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_CheckoutController extends Mage_Core_Controller_Front_Action {

    public function checkUseDefault(){
	$session = Mage::getSingleton('checkout/session');
        $rewardSalesRules = $session->getRewardSalesRules();
        $arrayRules=Mage::helper('rewardpoints/block_spend')->getRulesArray();
        if(Mage::helper('rewardpoints/calculation_spending')->isUseMaxPointsDefault()){
            if(isset($rewardSalesRules['use_point'])&& isset($rewardSalesRules['rule_id'])&&isset($arrayRules[$rewardSalesRules['rule_id']])&& isset($arrayRules[$rewardSalesRules['rule_id']]['sliderOption'])&& isset($arrayRules[$rewardSalesRules['rule_id']]['sliderOption']['maxPoints']) && ($rewardSalesRules['use_point'] < $arrayRules[$rewardSalesRules['rule_id']]['sliderOption']['maxPoints'])){
                 $session->setData('use_max', 0);
            }else{
                 $session->setData('use_max', 1);
            }
        }
    }
	
	/**
     * Checkout Page
     */
    public function indexAction() {
        if (!Mage::helper('rewardpoints')->isEnable()) {
            return $this->_redirectUrl(Mage::getBaseUrl());
        }

        $session = Mage::getSingleton('checkout/session');
        if ($usePoint = $this->getRequest()->getParam('use_point')) {
            $session->setData('use_point', $usePoint);
            $session->setData('point_amount', $this->getRequest()->getParam('point_amount'));
        } else {
            $session->unsetData('use_point');
        }
        $quote = $session->getQuote()->collectTotals()->save();

        $paymentBlock = $this->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods');
        $this->getResponse()->setBody($paymentBlock->toHtml());
    }

    /**
     * Fix for One Step Checkout
     */
    public function onestepcheckoutAction() {
        $this->indexAction();
    }

    /**
     * checkout page behaviors
     */
    public function changeUsePointAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('use_point', $this->getRequest()->getParam('use_point'));
        if(!$this->getRequest()->getParam('use_point'))
            $session->setData('use_max', 0);
        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock = $this->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods');
            $result['html'] = $paymentBlock->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function changePointAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setRewardSalesRules(array(
            'rule_id' => $this->getRequest()->getParam('reward_sales_rule'),
            'use_point' => $this->getRequest()->getParam('reward_sales_point'),
        ));
	
        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        $this->checkUseDefault();
        if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
//            $paymentBlock = $this->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods');
//            $result['html'] = $paymentBlock->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Update Total for shopping cart Page
     */
    public function updateTotalAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('use_point', true);
        $session->setRewardSalesRules(array(
            'rule_id' => $this->getRequest()->getParam('reward_sales_rule'),
            'use_point' => $this->getRequest()->getParam('reward_sales_point'),
        ));
	    
        
        $cart = Mage::getSingleton('checkout/cart');
        $result = array();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            $this->checkUseDefault();    
            $block = $this->getLayout()->createBlock('checkout/cart_totals')
                    ->setTemplate('checkout/cart/totals.phtml');
            $result['total'] = $block->toHtml();
        } else {
            $result['refresh'] = true;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Check using spending point for shopping cart Page
     */
    public function checkboxRuleAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setData('use_point', true); //Hai.Tran 25/11
        $rewardCheckedRules = $session->getRewardCheckedRules();
        if (!is_array($rewardCheckedRules))
            $rewardCheckedRules = array();
        if ($ruleId = $this->getRequest()->getParam('rule_id')) {
            if ($this->getRequest()->getParam('is_used')) {
                $rewardCheckedRules[$ruleId] = array(
                    'rule_id' => $ruleId,
                    'use_point' => null,
                );
            } elseif (isset($rewardCheckedRules[$ruleId])) {
                unset($rewardCheckedRules[$ruleId]);
            }
            $session->setRewardCheckedRules($rewardCheckedRules);
        }
    }

    /**
     * One Step Checkout page behaviors
     */
    public function changeUsePointOscAction() {
        $session = Mage::getSingleton('checkout/session');
        if (!$this->getRequest()->getParam('use_point')) {
            $session->setData('use_max', 0);
            $session->setData('use_point', 0); // hiepdd
        } else {
            $session->setData('use_point', $this->getRequest()->getParam('use_point'));
        }

        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock = $this->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods');
            $paymentBlock->setOneStepCheckout(true);
            $result['html'] = $paymentBlock->toHtml();
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Change point used for Onestepcheckout Page
     */
    public function changePointOscAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setRewardSalesRules(array(
            'rule_id' => $this->getRequest()->getParam('reward_sales_rule'),
            'use_point' => $this->getRequest()->getParam('reward_sales_point'),
        ));
        
	
        
        $result = array();
        $updatepayment = ($session->getQuote()->getGrandTotal() < 0.001);
        $session->getQuote()->collectTotals()->save();
        $this->checkUseDefault();
        if ($updatepayment xor ( $session->getQuote()->getGrandTotal() < 0.001)) {
            $result['updatepayment'] = 1;
            $paymentBlock = $this->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods');
            $paymentBlock->setOneStepCheckout(true);
            $result['html'] = $paymentBlock->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

}
