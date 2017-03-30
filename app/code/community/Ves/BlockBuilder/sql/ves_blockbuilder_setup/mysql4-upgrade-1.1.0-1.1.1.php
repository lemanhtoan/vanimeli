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
if (!$installer->getConnection()->isTableExists($installer->getTable('ves_blockbuilder/block_product'))) {
	$installer->run("

	-- DROP TABLE IF EXISTS `{$this->getTable('ves_blockbuilder/block_product')}`;
	CREATE TABLE `{$this->getTable('ves_blockbuilder/block_product')}` (
	  `block_id` int(11) unsigned NOT NULL,
	  `product_id` int(10) unsigned NOT NULL,
	  `store_id` int(10) unsigned NOT NULL,
	  PRIMARY KEY (`block_id`,`product_id`,`store_id`),
	  CONSTRAINT `FK_BLOCKBUILDER_BLOCK_PRODUCT_EX` FOREIGN KEY (`block_id`) REFERENCES `{$this->getTable('ves_blockbuilder/block')}` (`block_id`) ON UPDATE CASCADE ON DELETE CASCADE
	  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	");
}
$installer->endSetup();