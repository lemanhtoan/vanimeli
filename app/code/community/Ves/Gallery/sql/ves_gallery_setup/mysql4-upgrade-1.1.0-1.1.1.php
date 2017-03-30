<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(" 
	ALTER TABLE {$this->getTable('ves_gallery/banner')} ADD COLUMN `links` varchar(255) DEFAULT NULL;
");