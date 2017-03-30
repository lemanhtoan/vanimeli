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
//update rewardpoints_earning_catalog table
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_earning_catalog'), 'level_id', "int(11) NULL");
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_earning_catalog'), 'use_level', "smallint(5) NOT NULL default 0");
//    update rewardpoints_earning_sales table
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_earning_sales'), 'level_id', "int(11) NULL");
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_earning_sales'), 'use_level', "smallint(5) NOT NULL default 0");
//    update rewardpoints_spending_catalog table
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_spending_catalog'), 'level_id', "int(11) NULL");
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_spending_catalog'), 'use_level', "smallint(5) NOT NULL default 0");
//    update rewardpoints_spending_sales table
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_spending_sales'), 'level_id', "int(11) NULL");
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_spending_sales'), 'use_level', "smallint(5) NOT NULL default 0");
//    
//    $installer->getConnection()->addColumn($installer->getTable('rewardpoints_earning_product'), 'level_id', "int(11) NULL");
//    $installer->run("
//        ALTER TABLE {$installer->getTable('rewardpoints_earning_product')}  
//            DROP INDEX `UNIQUE_REWARDPOINTS_RULES`,
//            ADD  UNIQUE INDEX `UNIQUE_REWARDPOINTS_RULES` (`customer_group_id`, `website_id`, `product_id`, `level_id`);
//    ");

$installer->endSetup();
