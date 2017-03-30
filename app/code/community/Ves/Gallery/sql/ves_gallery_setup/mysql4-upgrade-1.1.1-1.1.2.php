<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

if ($installer->getConnection()->tableColumnExists($this->getTable('ves_gallery/banner'), "classes")) {
	$installer->run("
		ALTER TABLE {$this->getTable('ves_gallery/banner')} ADD COLUMN `classes` varchar(150) DEFAULT NULL;
		");
}
if ($installer->getConnection()->tableColumnExists($this->getTable('ves_gallery/banner'), "crop_mode")) {
	$installer->run("
		ALTER TABLE {$this->getTable('ves_gallery/banner')} ADD COLUMN `crop_mode` varchar(100) DEFAULT 'bottom';
		");
}
if ($installer->getConnection()->tableColumnExists($this->getTable('ves_gallery/banner'), "extra")) {
	$installer->run("
		ALTER TABLE {$this->getTable('ves_gallery/banner')} ADD COLUMN `extra` text(0) DEFAULT NULL;
		");
}