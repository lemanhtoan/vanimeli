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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($this->getTable('rewardpoints/customer'), 'referal_id', 'int(10) unsigned NOT NULL');
$installer->getConnection()->addColumn($this->getTable('rewardpoints/customer'), 'ip_adress', 'varchar(255) default ""');
$installer->getConnection()->addColumn($this->getTable('rewardpoints/customer'), 'created_time', 'datetime NULL');
$installer->endSetup();

