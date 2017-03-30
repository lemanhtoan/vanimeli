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
 * @package     Ves_Testiamonial
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
//die('adsjkfgadjksfasdjkfgasjkdfgd');
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('ves_testimonial/group')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_testimonial/group')}`(
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(225),
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('ves_testimonial/group_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_testimonial/group_store')}` (
  `group_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`group_id`,`store_id`),
  CONSTRAINT `FK_TESTIMONIAL_TESTIMONIAL_STORE_GROUP` FOREIGN KEY (`group_id`) REFERENCES `{$this->getTable('ves_testimonial/group')}` (`group_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_TESTIMONIALS_TESTIMONIAL_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tabs items to Stores';

ALTER TABLE `{$this->getTable('ves_testimonial/testimonial')}` ADD COLUMN `group_testimonial_id` int(11) DEFAULT '1' AFTER `testimonial_id`;
-- ALTER TABLE `{$this->getTable('ves_testimonial/testimonial')}` DROP COLUMN `label`;
");

$installer->endSetup();