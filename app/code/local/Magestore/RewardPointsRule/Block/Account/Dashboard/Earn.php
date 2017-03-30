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
 * Dashboard Earning Points By Rule
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Account_Dashboard_Earn extends Magestore_RewardPoints_Block_Template
{
    /**
     * get customer group id
     * 
     * @return int
     */
    public function getCustomerGroupId()
    {
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }
    
     /**
     * get website group id
     * 
     * @return int
     */
    public function getWebsiteId()
    {
        return Mage::app()->getStore()->getWebsiteId();
    }
    
    /**
     * get catalog earning rule
     * 
     * @return Magestore_RewardPointsRule_Model_Earning_Catalog_Collection
     */
    public function getCatalogRules()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return array();
        }
        return Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
            ->setAvailableFilter(
                $this->getCustomerGroupId(),
                $this->getWebsiteId(),
                now(true)
            );
    }
    
    /**
     * get shippongcart earning rules
     * 
     * @return Magestore_RewardPointsRule_Model_Earning_Sales_Collection
     */
    public function getShoppingCartRules()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return array();
        }
        return Mage::getResourceModel('rewardpointsrule/earning_sales_collection')
            ->setAvailableFilter(
                $this->getCustomerGroupId(),
                $this->getWebsiteId(),
                now(true)
            );
    }
    
    /**
     * get earning rate
     * 
     * @return Magestore_RewardPoints_Model_Rate
     */
    public function getRate()
    {
        if (!Mage::helper('rewardpointsrule')->isEnabled()) {
            return array();
        }
        return Mage::getBlockSingleton('rewardpoints/account_dashboard_earn')->getEarningRate();
    }
    
    /**
     * get current money formated of rate
     * 
     * @param Magestore_RewardPoints_Model_Rate $rate
     * @return string
     */
    public function getCurrentMoney($rate)
    {
        if ($rate && $rate->getId()) {
            $money = $rate->getMoney();
            return Mage::app()->getStore()->convertPrice($money, true);
        }
        return '';
    }
}
