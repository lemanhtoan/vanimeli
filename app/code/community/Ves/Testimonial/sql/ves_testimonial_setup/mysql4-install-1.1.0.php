<?php
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$installer->run("
 
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_testimonial/testimonial')}` (
  `testimonial_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `profile` text NOT NULL,
  `description` text NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `video_link` varchar(155) NOT NULL,
  `label` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`testimonial_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- DROP TABLE IF EXISTS `{$this->getTable('ves_testimonial/testimonial_store')}`;
CREATE TABLE `{$this->getTable('ves_testimonial/testimonial_store')}` (
  `testimonial_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`testimonial_id`,`store_id`),
  CONSTRAINT `FK_TESTIMONIAL_TESTIMONIAL_STORE_THEME` FOREIGN KEY (`testimonial_id`) REFERENCES `{$this->getTable('ves_testimonial/testimonial')}` (`testimonial_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_TESTIMONIAL_TESTIMONIAL_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Testimonial items to Stores';

");

 


$installer->endSetup();

