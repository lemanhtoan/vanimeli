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
 * Rewardpoints Rule Setup Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Model_Mysql4_Setup extends Magestore_RewardPoints_Model_Mysql4_Setup
{
    public function updateConfiguration()
    {
        $this->copyRewardPointsConfig(array(
            'earn/by_shipping'  => 'earning/by_shipping'
        ));
        return $this;
    }
    
    public function updateCatalogEarningRule()
    {
        if (!$this->tableExists($this->getTable('reward_earning_catalog'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_earning_catalog')} ";
        $copySql .= "SELECT * FROM {$this->getTable('reward_earning_catalog')} ";
        $copySql .= "ON DUPLICATE KEY UPDATE `rule_id` = VALUES(`rule_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    public function updateCatalogSpendingRule()
    {
        if (!$this->tableExists($this->getTable('reward_spending_catalog'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_spending_catalog')} ";
        $copySql .= "SELECT * FROM {$this->getTable('reward_spending_catalog')} ";
        $copySql .= "ON DUPLICATE KEY UPDATE `rule_id` = VALUES(`rule_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    public function updateShoppingCartEarningRule()
    {
        if (!$this->tableExists($this->getTable('reward_earning_sales'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_earning_sales')} ";
        $copySql .= "SELECT * FROM {$this->getTable('reward_earning_sales')} ";
        $copySql .= "ON DUPLICATE KEY UPDATE `rule_id` = VALUES(`rule_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    public function updateShoppingCartSpendingRule()
    {
        if (!$this->tableExists($this->getTable('reward_spending_sales'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_spending_sales')} ";
        $copySql .= "SELECT * FROM {$this->getTable('reward_spending_sales')} ";
        $copySql .= "ON DUPLICATE KEY UPDATE `rule_id` = VALUES(`rule_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
}
