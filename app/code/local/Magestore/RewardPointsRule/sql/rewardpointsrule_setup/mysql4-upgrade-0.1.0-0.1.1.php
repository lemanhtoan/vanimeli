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

/** @var $installer Magestore_RewardPointsRule_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create rewardpointsrule table
 */
$installer->getConnection()->addColumn($this->getTable('rewardpointsrule/spending_catalog'), 'max_price_spended_type', 'VARCHAR(15) NULL');
$installer->getConnection()->addColumn($this->getTable('rewardpointsrule/spending_catalog'), 'max_price_spended_value', 'DECIMAL(12,4) NULL');
$installer->getConnection()->addColumn($this->getTable('rewardpointsrule/spending_sales'), 'max_price_spended_type', 'VARCHAR(15) NULL');
$installer->getConnection()->addColumn($this->getTable('rewardpointsrule/spending_sales'), 'max_price_spended_value', 'DECIMAL(12,4) NULL');
//$installer->run("
//ALTER TABLE {$this->getTable('rewardpoints_spending_catalog')}
//  ADD COLUMN `max_price_spended_type` VARCHAR(15) NULL,
//  ADD COLUMN `max_price_spended_value` DECIMAL(12,4) NULL;
//
//ALTER TABLE {$this->getTable('rewardpoints_spending_sales')}
//  ADD COLUMN `max_price_spended_type` VARCHAR(15) NULL,
//  ADD COLUMN `max_price_spended_value` DECIMAL(12,4) NULL;
//");

$installer->endSetup();
