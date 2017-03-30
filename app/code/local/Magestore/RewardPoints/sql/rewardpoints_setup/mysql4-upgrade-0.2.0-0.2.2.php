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

/** @var $installer Magestore_RewardPointsRule_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_base_amount', 'decimal(12,4) NOT NULL default 0');
    $installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_amount', 'decimal(12,4) NOT NULL default 0');
	
$installer->endSetup();
