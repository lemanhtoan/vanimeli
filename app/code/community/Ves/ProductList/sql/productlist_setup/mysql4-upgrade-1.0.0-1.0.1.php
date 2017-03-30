<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
if (!$installer->getConnection()->tableColumnExists($this->getTable('productlist/rule'), "show_timer_countdown")) {
	$installer->run("
		ALTER TABLE `{$this->getTable('productlist/rule')}` ADD COLUMN `show_timer_countdown` varchar(50) NOT NULL;
		");
}

if (!$installer->getConnection()->tableColumnExists($this->getTable('productlist/rule'), "available_sort_by")) {
	$installer->run("
		ALTER TABLE `{$this->getTable('productlist/rule')}` ADD COLUMN `available_sort_by` text NULL COMMENT 'Available Product Listing Sort By';
		");
}