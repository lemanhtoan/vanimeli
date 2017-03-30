<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();
if ($installer->getConnection()->isTableExists($installer->getTable('ves_layerslider/banner'))) {
    $installer->getConnection()->dropForeignKey(
        $installer->getTable('ves_layerslider/banner_store'),
        'FK_LAYERSLIDER_BANNER_BANNER_STORE_THEME'
    );

    $installer->getConnection()->dropForeignKey(
        $installer->getTable('ves_layerslider/banner_store'),
        'FK_LAYERSLIDER_BANNER_BANNER_STORE_STORE'
    );

    $installer->run("
    DROP TABLE IF EXISTS `{$this->getTable('ves_layerslider/banner')}`;
    DROP TABLE IF EXISTS `{$this->getTable('ves_layerslider/banner_store')}`;
    ");

} 

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('ves_layerslider/banner')}` (
      `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      `alias` varchar(255) NOT NULL DEFAULT '',
      `params` text NULL,
      `options` text,
      `is_flexslider` tinyint(1) NOT NULL DEFAULT '0',
      `is_active` tinyint(1) NOT NULL DEFAULT '0',
      `position` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`banner_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

    -- DROP TABLE IF EXISTS `{$this->getTable('ves_layerslider/banner_store')}`;
    CREATE TABLE `{$this->getTable('ves_layerslider/banner_store')}` (
      `banner_id` int(10) unsigned NOT NULL,
      `store_id` smallint(5) unsigned NOT NULL,
      PRIMARY KEY (`banner_id`,`store_id`),
      CONSTRAINT `FK_LAYERSLIDER_BANNER_BANNER_STORE_THEME` FOREIGN KEY (`banner_id`) REFERENCES `{$this->getTable('ves_layerslider/banner')}` (`banner_id`) ON UPDATE CASCADE ON DELETE CASCADE,
      CONSTRAINT `FK_LAYERSLIDER_BANNER_BANNER_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core/store')}` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Banner items to Stores';

    ");

$installer->endSetup();

