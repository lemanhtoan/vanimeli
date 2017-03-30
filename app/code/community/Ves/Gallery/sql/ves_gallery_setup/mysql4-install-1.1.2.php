<?php
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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->run("
CREATE TABLE `{$this->getTable('ves_gallery/banner')}` (
  `banner_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Price',
  `created_at` date DEFAULT '0000-00-00',
  `position` smallint(5) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `links` varchar(255) DEFAULT NULL,
  `classes` varchar(150) DEFAULT NULL,
  `crop_mode` varchar(100) DEFAULT 'bottom',
  `extra` text(0) DEFAULT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Ves Gallery';
");
$installer->endSetup();

