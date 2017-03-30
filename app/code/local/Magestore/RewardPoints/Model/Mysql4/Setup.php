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
 * Rewardpoints Setup Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Mysql4_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * update reward points config from old version
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsConfig()
    {
        // Check old system configuration is existed or not
        $configRow = $this->getTableRow('core/config_data', 'path', 'customerreward/earn/initialize');
        if (!$configRow) {
            return $this;
        }
        $this->copyRewardPointsConfig(array(
            'display/info'      => 'general/policy_page',
            
            'earn/expire'       => 'earning/expire',
            'earn/max'          => 'earning/max_balance',
            'earn/invoice'      => 'earning/order_invoice',
            'earn/cancel_orderstatus'   => 'earning/order_cancel_state',
            
            'earn/min'          => 'spending/redeemable_points',
            'spend/tax'         => 'spending/spend_for_tax',
            'spend/shipping'    => 'spending/spend_for_shipping',
            'spend/shipping_tax'=> 'spending/spend_for_shipping_tax',
            'earn/cancel_orderstatus'   => 'spending/order_refund_state',
            
            'display/toplink'   => 'display/toplink',
            'display/product'   => 'display/product',
            
            'email/enable'      => 'email/enable',
            'email/sender'      => 'email/sender',
            'email/day_before'  => 'email/before_expire_days',
        ));
        return $this;
    }
    
    /**
     * Copy config field from old reward points system to new
     * 
     * @param array $copyMap
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function copyRewardPointsConfig($copyMap)
    {
        $copySql = '';
        foreach ($copyMap as $_from => $_to) {
            $copySql .= "INSERT INTO {$this->getTable('core/config_data')} ";
            $copySql .= "SELECT * FROM {$this->getTable('core/config_data')} ";
            $copySql .= "WHERE `path` = 'customerreward/$_from' ";
            $copySql .= "ON DUPLICATE KEY UPDATE `path` = 'rewardpoints/$_to'; ";
        }
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    /**
     * transfer data from old rate table to new table
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsRate()
    {
        if (!$this->tableExists($this->getTable('reward_rate'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_rate')} ";
        $copySql .= "SELECT * FROM {$this->getTable('reward_rate')} ";
        $copySql .= "ON DUPLICATE KEY UPDATE `rate_id` = VALUES(`rate_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    /**
     * transafer customer reward account information to new table
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsCustomer()
    {
        if (!$this->tableExists($this->getTable('reward_customer'))) {
            return $this;
        }
        $copySql  = "INSERT INTO {$this->getTable('rewardpoints_customer')} ";
        $copySql .= "SELECT orc.reward_customer_id, orc.customer_id, orc.total_points, ";
        $copySql .= "ABS(0), ABS(0), orc.is_notification, orc.is_notification ";
        $copySql .= "FROM {$this->getTable('reward_customer')} AS orc ";
        $copySql .= "ON DUPLICATE KEY UPDATE `reward_id` = VALUES(`reward_id`);";
        try {
            $this->run($copySql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    /**
     * transfer all transactions from old reward points system
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsTransaction()
    {
        if (!$this->tableExists($this->getTable('reward_transaction'))) {
            return $this;
        }
        // Upgrade original transactions
        $orgActions = array(
            'initialize',
            'newsletter',
            'review',
            'tag',
            'poll',
            'offer',
            'visit',
            'uniqueclick',
            'refundoffer',
            'admin'
        );
        $select = $this->getConnection()->select()->reset()
            ->from(array('t' => $this->getTable('reward_transaction')), array())
            ->joinInner(array('r' => $this->getTable('rewardpoints_customer')),
                't.customer_id = r.customer_id',
                array()
            )->joinInner(array('c' => $this->getTable('customer/entity')),
                't.customer_id = c.entity_id',
                array()
            )->columns(array(
                'transaction_id'=> 't.transaction_id',
                'reward_id'     => 'r.reward_id',
                'customer_id'   => 'r.customer_id',
                'customer_email'=> 'c.email',
                'title'         => 't.title',
                'action'        => 't.action',
                'action_type'   => "IF(t.action = 'admin', 0, 1)",
                'store_id'      => 't.store_id',
                'point_amount'  => 't.points_change',
                'point_used'    => 't.points_spent',
                'real_point'    => 'IF(t.points_change > 0, t.points_change, 0)',
                'status'        => 'IF(is_expired > 0, 5, 3)',
                'created_time'  => 't.create_at',
                'updated_time'  => 't.create_at',
                'expiration_date'   => 't.expiration_date',
                'extra_content' => 't.extra_content'
            ))->where('t.action IN (?)', $orgActions);
        $insertSql = $select->insertFromSelect(
            $this->getTable('rewardpoints_transaction'),
            array(
                'transaction_id', 'reward_id', 'customer_id', 'customer_email', 'title', 'action',
                'action_type', 'store_id', 'point_amount', 'point_used', 'real_point', 'status',
                'created_time', 'updated_time', 'expiration_date', 'extra_content'
            ),
            false
        );
        $insertSql .= ' ON DUPLICATE KEY UPDATE `transaction_id` = VALUES(`transaction_id`);';
        try {
            $this->run($insertSql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        // Upgrade data for core transactions
        $hasRealPoints = $this->getConnection()
            ->tableColumnExists($this->getTable('reward_transaction'), 'real_points');
        
        // action spend, cancel, refund
        $fields = array(
            'transaction_id'=> 't.transaction_id',
            'reward_id'     => 'r.reward_id',
            'customer_id'   => 'r.customer_id',
            'customer_email'=> 'c.email',
            'title'         => 't.title',
            'action'        => "IF(t.action = 'spend', 'spending_order', IF(t.action = 'cancel', 'spending_cancel', 'earning_cancel'))",
            'action_type'   => "IF(t.action = 'refund', 1, 2)",
            'store_id'      => 't.store_id',
            'point_amount'  => 't.points_change',
            'point_used'    => 't.points_spent',
            'real_point'    => 'IF(t.points_change > 0, t.points_change, 0)',
            'status'        => 'IF(is_expired > 0, 5, 3)',
            'created_time'  => 't.create_at',
            'updated_time'  => 't.create_at',
            'expiration_date'   => 't.expiration_date',
            'extra_content' => "IF(t.action = 'spend', t.extra_content, '')"
        );
        $fields['order_id'] = "o.entity_id";
        $fields['order_increment_id'] = 'o.increment_id';
        $fields['order_base_amount'] = 'o.base_grand_total';
        $fields['order_amount'] = 'o.grand_total';
        $select = $this->getConnection()->select()->reset()
            ->from(array('t' => $this->getTable('reward_transaction')), array())
            ->joinInner(array('r' => $this->getTable('rewardpoints_customer')),
                't.customer_id = r.customer_id',
                array()
            )->joinInner(array('c' => $this->getTable('customer/entity')),
                't.customer_id = c.entity_id',
                array()
            )->joinLeft(array('o' => $this->getTable('sales/order')),
                "SUBSTRING(t.extra_content, 10, LOCATE('&', t.extra_content) - 10) = o.entity_id",
                array()
            )->columns($fields)
            ->where('t.action IN (?)', array('spend', 'cancel', 'refund'));
        $insertSql = $select->insertFromSelect(
            $this->getTable('rewardpoints_transaction'),
            array(
                'transaction_id', 'reward_id', 'customer_id', 'customer_email', 'title', 'action',
                'action_type', 'store_id', 'point_amount', 'point_used', 'real_point', 'status',
                'created_time', 'updated_time', 'expiration_date', 'extra_content', 'order_id',
                'order_increment_id', 'order_base_amount', 'order_amount'
            ),
            false
        );
        $insertSql .= ' ON DUPLICATE KEY UPDATE `transaction_id` = VALUES(`transaction_id`);';
        try {
            $this->run($insertSql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        
        // action creditmemo
        $fields['action'] = "LOWER('spending_creditmemo')";
        $fields['action_type'] = 'ABS(2)';
        $fields['real_point'] = 't.points_change';
        $fields['extra_content'] = 'm.increment_id';
        if (version_compare(Mage::getVersion(), '1.4.1.0', '>=')
            && $this->tableExists($this->getTable('sales/creditmemo'))
        ) {
            $select = $this->getConnection()->select()->reset()
                ->from(array('t' => $this->getTable('reward_transaction')), array())
                ->joinInner(array('r' => $this->getTable('rewardpoints_customer')),
                    't.customer_id = r.customer_id',
                    array()
                )->joinInner(array('c' => $this->getTable('customer/entity')),
                    't.customer_id = c.entity_id',
                    array()
                )->joinLeft(array('m' => $this->getTable('sales/creditmemo')),
                    "SUBSTRING(t.extra_content, 12, LOCATE('&', t.extra_content) - 12) = m.entity_id",
                    array()
                )->joinLeft(array('o' => $this->getTable('sales/order')),
                    "m.order_id = o.entity_id",
                    array()
                )->columns($fields)
                ->where('t.action = ?', 'creditmemo');
            $insertSql = $select->insertFromSelect(
            $this->getTable('rewardpoints_transaction'),
                array(
                    'transaction_id', 'reward_id', 'customer_id', 'customer_email', 'title', 'action',
                    'action_type', 'store_id', 'point_amount', 'point_used', 'real_point', 'status',
                    'created_time', 'updated_time', 'expiration_date', 'extra_content', 'order_id',
                    'order_increment_id', 'order_base_amount', 'order_amount'
                ),
                false
            );
            $insertSql .= ' ON DUPLICATE KEY UPDATE `transaction_id` = VALUES(`transaction_id`);';
            try {
                $this->run($insertSql);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        // action invoice, catalogrule, rule, cashback
        $fields['action'] = "LOWER('earning_invoice')";
        $fields['action_type'] = 'ABS(1)';
        if ($hasRealPoints) {
            $fields['real_point'] = 'SUM(t.real_points)';
        } else {
            $fields['real_point'] = 'SUM(t.points_change)';
        }
        $fields['extra_content'] = "LOWER('')";
        $fields['point_amount'] = 'SUM(t.points_change)';
        $fields['point_used'] = 'SUM(t.points_spent)';
        $select = $this->getConnection()->select()->reset()
            ->from(array('t' => $this->getTable('reward_transaction')), array())
            ->joinInner(array('r' => $this->getTable('rewardpoints_customer')),
                't.customer_id = r.customer_id',
                array()
            )->joinInner(array('c' => $this->getTable('customer/entity')),
                't.customer_id = c.entity_id',
                array()
            )->joinLeft(array('o' => $this->getTable('sales/order')),
                "SUBSTRING(t.extra_content, 10, LOCATE('&', t.extra_content) - 10) = o.entity_id",
                array()
            )->columns($fields)
            ->where('t.action IN (?)', array('invoice', 'catalogrule', 'rule', 'cashback'))
            ->group('o.entity_id');
        $insertSql = $select->insertFromSelect(
            $this->getTable('rewardpoints_transaction'),
            array(
                'transaction_id', 'reward_id', 'customer_id', 'customer_email', 'title', 'action',
                'action_type', 'store_id', 'point_amount', 'point_used', 'real_point', 'status',
                'created_time', 'updated_time', 'expiration_date', 'extra_content', 'order_id',
                'order_increment_id', 'order_base_amount', 'order_amount'
            ),
            false
        );
        $insertSql .= ' ON DUPLICATE KEY UPDATE `transaction_id` = VALUES(`transaction_id`);';
        try {
            $this->run($insertSql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    /**
     * update reward points order information, work with latest customer reward extension
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsOrders()
    {
        // Update order point earn
        $select = $this->getConnection()->select()->reset()
            ->from(array('t' => $this->getTable('rewardpoints_transaction')), array(
                'order_id', 'point_amount'
            ))->where('action = ?', 'earning_invoice');
        $updateOrders = $this->getConnection()->fetchAll($select);
        if (is_array($updateOrders) && count($updateOrders)) {
            $updateSql = '';
            foreach ($updateOrders as $orderData) {
                $updateSql .= "UPDATE {$this->getTable('sales/order')} SET ";
                $updateSql .= "rewardpoints_earn = '{$orderData['point_amount']}' ";
                $updateSql .= "WHERE entity_id = '{$orderData['order_id']}'; ";
            }
            try {
                $this->run($updateSql);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        // Update order point spend
        $select = $this->getConnection()->select()->reset()
            ->from(array('t' => $this->getTable('rewardpoints_transaction')), array(
                'order_id', 'point_amount', 'extra_content'
            ))->where('action = ?', 'spending_order')
            ->where('extra_content <> ?', '');
        $updateOrders = $this->getConnection()->fetchAll($select);
        if (is_array($updateOrders) && count($updateOrders)) {
            $updateSql = '';
            foreach ($updateOrders as $orderData) {
                if (!$orderData['extra_content']) {
                    continue;
                }
                $extraContent = array();
                parse_str($orderData['extra_content'], $extraContent);
                if (empty($extraContent['current_money']) || empty($extraContent['money_base'])) {
                    continue;
                }
                $updateSql .= "UPDATE {$this->getTable('sales/order')} SET ";
                $updateSql .= "rewardpoints_spent = '" . abs($orderData['point_amount']) . "', ";
                $updateSql .= "rewardpoints_base_discount = '" . abs($extraContent['money_base']) . "', ";
                $updateSql .= "rewardpoints_discount = '" . abs($extraContent['current_money']) . "' ";
                $updateSql .= "WHERE entity_id = '{$orderData['order_id']}'; ";
            }
            if (!$updateSql) {
                return $this;
            }
            try {
                $this->run($updateSql);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            return $this;
        }
        
        // Re-update transaction
        $select = $this->getConnection()->select()->reset()
            ->joinInner(array('o' => $this->getTable('sales/order')),
                'main_table.order_id = o.entity_id',
                array()
            )->columns(array(
                'base_discount' => 'o.rewardpoints_base_discount',
                'discount'      => 'o.rewardpoints_discount',
                'extra_content' => "IF(main_table.action = 'spending_order', '', main_table.extra_content)",
            ));
        $updateSql = $select->crossUpdateFromSelect(array(
            'main_table' => $this->getTable('rewardpoints_transaction')
        ));
        try {
            $this->run($updateSql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
    
    /**
     * Update order table from customer reward version 0.4.0
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Setup
     */
    public function updateRewardPointsOrderTable()
    {
        if (!$this->tableExists($this->getTable('reward_rewards'))) {
            return $this;
        }
        $select = $this->getConnection()->select()->reset()
            ->joinInner(array('r' => $this->getTable('reward_rewards')),
                'main_table.entity_id = r.order_id',
                array()
            )->columns(array(
                'rewardpoints_earn'         => new Zend_Db_Expr('r.earn_catalog + IF (r.earn_offer > 0, r.earn_offer, IF(r.earn_sales > 0, r.earn_sales, r.earn_rate))'),
                'rewardpoints_spent'        => new Zend_Db_Expr('r.spend_catalog + r.spend_sales'),
                'rewardpoints_base_discount'=> new Zend_Db_Expr('r.base_catalog_discount + r.base_sales_discount'),
                'rewardpoints_discount'     => new Zend_Db_Expr('r.catalog_discount + r.sales_discount'),
            ));
        $updateSql = $select->crossUpdateFromSelect(array(
            'main_table'    => $this->getTable('sales/order')
        ));
        try {
            $this->run($updateSql);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }
}
