<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Ves 
 * @package     ves_landingpage
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
$prefix = Mage::getConfig()->getTablePrefix();
$installer->run("
-- DROP TABLE IF EXISTS `".$prefix."{$this->getTable('ves_landingpage/slider')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_landingpage/slider')}`(
	`slider_id` int(11) NOT NULL AUTO_INCREMENT,
	`caption_1` varchar(200) DEFAULT NULL,
	`class_1` varchar(200) DEFAULT NULL,
	`effect_1` varchar(225) DEFAULT 'slideUp',
	`caption_2` varchar(200) DEFAULT NULL,
	`class_2` varchar(200) DEFAULT NULL,
	`effect_2` varchar(225) DEFAULT 'slideUp',
	`caption_3` varchar(200) DEFAULT NULL,
	`class_3` varchar(200) DEFAULT NULL,
	`effect_3` varchar(225) DEFAULT 'slideUp',
	`status` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`slider_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- DROP TABLE IF EXISTS `".$prefix."{$this->getTable('ves_landingpage/slider_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_landingpage/slider_store')}` (
  `slider_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`slider_id`,`store_id`),
  CONSTRAINT `FK_CONTENTTAB_SLIDER_STORE_SLIDER` FOREIGN KEY (`slider_id`) REFERENCES `{$this->getTable('ves_landingpage/slider')}` (`slider_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_CONTENTTAB_SLIDER_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='slider items to Stores';
");

$installer->endSetup();

