<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
  -- DROP TABLE IF EXISTS `{$this->getTable('productlist/rule')}`;
  CREATE TABLE IF NOT EXISTS `{$this->getTable('productlist/rule')}`(
    `rule_id` int(10) unsigned NOT NULL auto_increment,
    `identifier` varchar(255) NOT NULL DEFAULT '',
    `title` tinytext NOT NULL,
    `thumbnail` varchar(150) NULL,
    `image` varchar(150) NULL,
    `conditions_serialized` text NULL,
    `description` text NULL,
    `short_description` text NULL,
    `status` tinyint(4) NOT NULL default '1',
    `date_from` date default NULL,
    `date_to` date default NULL,
    `product_number` int(10) NULL,
    `product_order` varchar(10) NULL,
    `product_direction` varchar(10) NULL,
    `created` datetime DEFAULT NULL,
    `modified` datetime DEFAULT NULL,
    `source_type` varchar(50) NULL,
    `show_outofstock` tinyint(4) NOT NULL default '1',
    `custom_design_from` date default NULL,
    `custom_design_to` date default NULL,
    `page_layout` varchar(50) NULL,
    `custom_layout_update` text NULL,
    `options` text NULL,
    `page_title` varchar(255) NULL,
    `meta_keywords` varchar(255) NULL,
    `meta_description` varchar(255) NULL,
    PRIMARY KEY  (`rule_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('productlist/rule_product')}`;
CREATE TABLE `{$this->getTable('productlist/rule_product')}` (
  `rule_id` int(10) unsigned NOT NULL,
  `rule_product_id` smallint(5) unsigned NOT NULL,
  `position` int(10) NOT NULL default '0',
  PRIMARY KEY (`rule_id`,`rule_product_id`),
  CONSTRAINT `FK_PRODUCTLIST_RULE_PRODUCT_EX` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('productlist/rule')}` (`rule_id`) ON UPDATE CASCADE ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('productlist/rule_store')}`;
CREATE TABLE `{$this->getTable('productlist/rule_store')}` (
  `rule_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`rule_id`,`store_id`),
  CONSTRAINT `FK_PRODUCTLIST_RULE_EX` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('productlist/rule')}` (`rule_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_PRODUCTLIST_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Form items to Stores';

-- DROP TABLE IF EXISTS `{$this->getTable('productlist/rule_customer')}`;
CREATE TABLE `{$this->getTable('productlist/rule_customer')}` (
  `rule_id` int(10) unsigned NOT NULL,
  `customer_group_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`rule_id`,`customer_group_id`),
  CONSTRAINT `FK_PRODUCTLIST_RULE_IDEX` FOREIGN KEY (`rule_id`) REFERENCES `{$this->getTable('productlist/rule')}` (`rule_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_PRODUCTLIST_CUSTOMER` FOREIGN KEY (`customer_group_id`) REFERENCES `{$this->getTable('customer_group')}` (`customer_group_id`) ON UPDATE CASCADE ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Form items to Stores';
");

$installer->endSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'featured', array(
  'label' => 'Featured',
  'type' => 'int',
  'input' => 'select',
  'source' => 'eav/entity_attribute_source_boolean',
  'visible' => true,
  'required' => false,
  'position' => 10,
  ));