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
 * RewardPointsRule Spending Sales Mysql4 Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Model_Mysql4_Spending_Sales_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpointsrule/spending_sales');
    }
    
    /**
     * 
     * @param type $customerGroupId
     * @param type $websiteId
     * @param type $date
     * @return Magestore_RewardPointsRule_Model_Mysql4_Spending_Sales_Collection
     */
    public function setAvailableFilter($customerGroupId, $websiteId, $date = null)
    {
        if (is_null($date)) {
            $date = now(true);
        }
        $this->addFieldToFilter('website_ids', array('finset' => $websiteId))
            ->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
            ->addFieldToFilter('is_active', 1);
        
        $this->getSelect()->where("(from_date IS NULL) OR (DATE(from_date) <= ?)", $date)
            ->where("(to_date IS NULL) OR (DATE(to_date) >= ?)", $date)
            ->order('sort_order DESC');
        return $this;
    }
    public function getRulesForWebsite($websiteId,$date = null){
        if (is_null($date)) {
            $date = now(true);
        }
        $this->addFieldToFilter('website_ids', array('finset' => $websiteId))
            ->addFieldToFilter('is_active', 1);
        
        $this->getSelect()->where("(from_date IS NULL) OR (DATE(from_date) <= ?)", $date)
            ->where("(to_date IS NULL) OR (DATE(to_date) >= ?)", $date)
            ->order('sort_order DESC');
        return $this;
    }
}
