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
 * @package     Ves_Megamenu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('ves_megamenu/megamenu')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_megamenu/megamenu')}` (
          `megamenu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `image` varchar(255) NOT NULL DEFAULT '',
          `parent_id` int(11) NOT NULL DEFAULT '0',
          `is_group` smallint(6) NOT NULL DEFAULT '0',
          `width` varchar(255) DEFAULT NULL,
          `submenu_width` varchar(255) DEFAULT NULL,
          `colum_width` varchar(255) DEFAULT NULL,
          `submenu_colum_width` varchar(255) DEFAULT NULL,
          `item` varchar(255) DEFAULT NULL,
          `colums` varchar(255) DEFAULT '1',
          `type` varchar(255) NOT NULL,
          `is_content` smallint(6) NOT NULL DEFAULT '2',
          `show_title` smallint(6) NOT NULL DEFAULT '1',
          `type_submenu` varchar(10) NOT NULL DEFAULT '1',
          `level_depth` smallint(6) NOT NULL DEFAULT '0',
          `published` smallint(6) NOT NULL DEFAULT '1',
          `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
          `position` int(11) unsigned NOT NULL DEFAULT '0',
          `show_sub` smallint(6) NOT NULL DEFAULT '0',
          `url` varchar(255) DEFAULT NULL,
          `target` varchar(25) DEFAULT NULL,
          `privacy` smallint(5) unsigned NOT NULL DEFAULT '0',
          `position_type` varchar(25) DEFAULT 'top',
          `menu_class` varchar(25) DEFAULT NULL,
          `title` varchar(255) NOT NULL,
          `description` text,
          `content_text` text,
          `submenu_content` text,
          `level` int(11) NOT NULL,
          `left` int(11) NOT NULL,
          `right` int(11) NOT NULL,
          `widget_id` int(11) DEFAULT '0',
          `options` text,
          PRIMARY KEY (`megamenu_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- DROP TABLE IF EXISTS `{$this->getTable('ves_megamenu/megamenu_widget')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_megamenu/megamenu_widget')}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(250) NOT NULL,
          `type` varchar(255) NOT NULL,
          `params` text NOT NULL,
          `store_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- DROP TABLE IF EXISTS `{$this->getTable('ves_megamenu/megamenu_store')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_megamenu/megamenu_store')}` (
  `megamenu_id` int(10) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`megamenu_id`,`store_id`),
  CONSTRAINT `FK_MEGAMEMU__MEGAMEMU_STORE_THEME` FOREIGN KEY (`megamenu_id`) REFERENCES `{$this->getTable('ves_megamenu/megamenu')}` (`megamenu_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_MEGAMEMU_MEGAMEMU_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Megamenu items to Stores';

");

$installer->endSetup();

