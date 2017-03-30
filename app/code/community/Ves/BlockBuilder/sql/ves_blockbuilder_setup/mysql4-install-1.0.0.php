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
$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('ves_blockbuilder/block')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_blockbuilder/block')}`(
	`block_id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(200) DEFAULT NULL,
	`alias` varchar(200) DEFAULT NULL,
	`shortcode` varchar(100) DEFAULT NULL,
	`show_from` date DEFAULT NULL,
	`show_to` date DEFAULT NULL,
	`created` datetime DEFAULT NULL,
	`modified` datetime DEFAULT NULL,
	`customer_group` varchar(100) DEFAULT NULL,
	`prefix_class` varchar(100) DEFAULT NULL,
	`block_type` varchar(100) DEFAULT NULL,
	`container` tinyint(1) NOT NULL DEFAULT '0',
	`status` tinyint(1) NOT NULL DEFAULT '1',
	`position` tinyint(4) NOT NULL DEFAULT '0',
	`params` text(0) DEFAULT NULL,
	`layout_html` text(0) DEFAULT NULL,
	PRIMARY KEY (`block_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
");

$installer->endSetup();

