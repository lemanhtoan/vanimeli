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
 * @package     Ves_Tempcp
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("

ALTER TABLE `{$this->getTable('ves_blockbuilder/block')}` ADD COLUMN `settings` text AFTER `params`;

-- DROP TABLE IF EXISTS `{$this->getTable('ves_blockbuilder/template')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_blockbuilder/template')}`(
	`template_id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(200) DEFAULT NULL,
	`icon` varchar(200) DEFAULT NULL,
	`created` datetime DEFAULT NULL,
	`modified` datetime DEFAULT NULL,
	`template_type` varchar(100) DEFAULT NULL,
	`author` varchar(100) DEFAULT NULL,
	`is_base` tinyint(4) NOT NULL DEFAULT '0',
	`params` text(0) DEFAULT NULL,
	PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

");

$installer->endSetup();