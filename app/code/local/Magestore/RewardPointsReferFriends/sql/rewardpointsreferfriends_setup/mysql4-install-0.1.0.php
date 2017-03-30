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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create rewardpointsreferfriends table
 */
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('rewardpointsreferfriends/rewardpointsspecialrefer')};

CREATE TABLE {$this->getTable('rewardpointsreferfriends/rewardpointsspecialrefer')}(
  `special_refer_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) default '',
  `description_referal` text default '',
  `description_invited` text default '',
  `website_ids` text default '',
  `customer_group_ids` text default '',
  `conditions_serialized` mediumtext default '',
  `actions_serialized` mediumtext default '',
  `status` smallint(6) default '2',
  `from_date` datetime NULL,
  `to_date` datetime NULL,
  `priority` smallint(6) unsigned NOT NULL default '0',
  `commission_action` smallint(6) default '0',
  `money_step` decimal(12,4) default NULL,
  `qty_step` int(11) NOT NULL default '0',
  `commission_point` int(11) default '0',
  `discount_type` smallint(6) default '0',
  `discount_value` decimal(12,4) default '0',
  `stop_rules_processing` smallint(6) default '0',
   PRIMARY KEY (`special_refer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- Create table product_label_value cho multistore
 DROP TABLE IF EXISTS {$this->getTable('rewardpointsreferfriends/rewardpointsspecialrefervalue')};
   CREATE TABLE {$this->getTable('rewardpointsreferfriends/rewardpointsspecialrefervalue')} (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `special_refer_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) unsigned  NOT NULL,
  `attribute_code` varchar(63) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE(`special_refer_id`,`store_id`,`attribute_code`),
  INDEX (`special_refer_id`),
  INDEX (`store_id`),
  FOREIGN KEY (`special_refer_id`) REFERENCES {$this->getTable('rewardpointsreferfriends/rewardpointsspecialrefer')} (`special_refer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- Create tabel rewardpoints_refer_customer
  DROP TABLE IF EXISTS {$this->getTable('rewardpointsreferfriends/rewardpointsrefercustomer')};
  CREATE TABLE {$this->getTable('rewardpointsreferfriends/rewardpointsrefercustomer')}(
  `id` int(11) unsigned NOT NULL auto_increment,
  `key` varchar(255) default '',
  `coupon` varchar(255) default '',
  `customer_id` int(11) unsigned NOT NULL default '0',
  `email_sent` int(11) NOT NULL default '0',
  `date_sent` datetime NULL,
  
  PRIMARY KEY (`id`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

   UPDATE {$this->getTable('rewardpoints/transaction')} set `action`='referfriends' where `action`='offer' ;
        
");
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_referal_earn', 'int(11) NOT NULL default 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_invited_discount', 'decimal(12,4) NOT NULL default 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_invited_base_discount', 'decimal(12,4) NOT NULL default 0');
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'rewardpoints_refer_customer_id', 'int(11) NOT NULL default 0');

$installer->copyRewardPointsConfig(array(
    'refer/pattern' => 'referfriendplugin/pattern',
    'refer/yahoo_app_id' => 'referfriendplugin/yahoo_app_id',
    'refer/yahoo_consumer_key' => 'referfriendplugin/yahoo_consumer_key',
    'refer/yahoo_consumer_secret' => 'referfriendplugin/yahoo_consumer_secret',
    'refer/google_consumer_key' => 'referfriendplugin/google_consumer_key',
    'refer/google_consumer_secret' => 'referfriendplugin/google_consumer_secret',
    'refer/fbapp_id' => 'referfriendplugin/fbapp_id',
    'refer/fbapp_secret' => 'referfriendplugin/fbapp_secret',
));

$installer->endSetup();

