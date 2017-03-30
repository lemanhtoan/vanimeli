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
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('rewardpointsrule/earning_product')};

CREATE TABLE {$this->getTable('rewardpointsrule/earning_product')} (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_group_id` SMALLINT(10) UNSIGNED NULL,
  `website_id` SMALLINT(5) UNSIGNED NULL,
  `product_id` INT(10) UNSIGNED NULL,
  `rule_ids` TEXT DEFAULT '',
  `earning_point` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE `UNIQUE_REWARDPOINTS_RULES` (`customer_group_id`, `website_id`, `product_id`),
  KEY `FK_REWARDPOINTS_RULES_GROUP_ID` (`customer_group_id`),
  KEY `FK_REWARDPOINTS_RULES_WEBSITE_ID` (`website_id`),
  KEY `FK_REWARDPOINTS_RULES_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_REWARDPOINTS_RULES_GROUP_ID` FOREIGN KEY (`customer_group_id`) REFERENCES {$this->getTable('customer/customer_group')} (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARDPOINTS_RULES_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES {$this->getTable('core/website')} (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_REWARDPOINTS_RULES_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('rewardpointsrule/earning_catalog')};
CREATE TABLE {$this->getTable('rewardpointsrule/earning_catalog')} (
    `rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) DEFAULT '',
    `description` TEXT DEFAULT '',
    `is_active` SMALLINT(6) UNSIGNED DEFAULT '0',
    `website_ids` TEXT DEFAULT '',
    `customer_group_ids` TEXT DEFAULT '',
    `from_date` DATETIME NULL,
    `to_date` DATETIME NULL,
    `sort_order` INT(11) NULL,
    `conditions_serialized` MEDIUMTEXT NULL,
    `simple_action` VARCHAR(15) NULL,
    `points_earned` INT(11) UNSIGNED DEFAULT '0',
    `money_step` DECIMAL(12,4) NULL,
    `max_points_earned` INT(11) UNSIGNED DEFAULT '0',
    `stop_rules_processing` SMALLINT(6) UNSIGNED NULL,
    PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('rewardpointsrule/earning_sales')};
CREATE TABLE {$this->getTable('rewardpointsrule/earning_sales')} (
    `rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_active` SMALLINT(6) NULL,
    `website_ids` TEXT NULL,
    `customer_group_ids` TEXT NULL,
    `from_date` DATETIME NULL,
    `to_date` DATETIME NULL,
    `sort_order` INT(11) NULL,
    `conditions_serialized` MEDIUMTEXT NULL,
    `actions_serialized` MEDIUMTEXT NULL,
    `simple_action` VARCHAR(15) NULL,
    `points_earned` INT(11) UNSIGNED DEFAULT '0',
    `money_step` DECIMAL(12,4) NULL,
    `qty_step` INT(11) UNSIGNED DEFAULT '0',
    `max_points_earned` INT(11) NULL,
    `stop_rules_processing` SMALLINT(6) NULL,
    PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('rewardpointsrule/spending_catalog')};
CREATE TABLE {$this->getTable('rewardpointsrule/spending_catalog')} (
    `rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_active` SMALLINT(6) NULL,
    `website_ids` TEXT NULL,
    `customer_group_ids` TEXT NULL,
    `from_date` DATETIME NULL,
    `to_date` DATETIME NULL,
    `sort_order` INT(11) NULL,
    `conditions_serialized` MEDIUMTEXT NULL,
    `simple_action` VARCHAR(15) NULL,
    `points_spended` INT(11) UNSIGNED DEFAULT '0',
    `money_step` DECIMAL(12,4) NULL,
    `max_points_spended` INT(11) NULL,
    `discount_style` VARCHAR(15) NULL,
    `discount_amount` DECIMAL(12,4) NULL,
    `uses_per_product` INT(11) NULL,
    `stop_rules_processing` SMALLINT(6) NULL,
    PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('rewardpointsrule/spending_sales')};
CREATE TABLE {$this->getTable('rewardpointsrule/spending_sales')} (
    `rule_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_active` SMALLINT(6) NULL,
    `website_ids` TEXT NULL,
    `customer_group_ids` TEXT NULL,
    `from_date` DATETIME NULL,
    `to_date` DATETIME NULL,
    `sort_order` INT(11) NULL,
    `conditions_serialized` MEDIUMTEXT NULL,
    `actions_serialized` MEDIUMTEXT NULL,
    `simple_action` VARCHAR(15) NULL,
    `points_spended` INT(11) UNSIGNED DEFAULT '0',
    `money_step` DECIMAL(12,4) NULL,
    `max_points_spended` INT(11) NULL,
    `discount_style` VARCHAR(15) NULL,
    `discount_amount` DECIMAL(12,4) NULL,
    `stop_rules_processing` SMALLINT(6),
    PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

/**
 * update database from customer reward
 */
$installer->updateConfiguration()
    ->updateCatalogEarningRule()
    ->updateCatalogSpendingRule()
    ->updateShoppingCartEarningRule()
    ->updateShoppingCartSpendingRule();

$installer->endSetup();
