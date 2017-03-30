<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

$upgradeSQL = "

DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/backup')}`;
CREATE TABLE `{$this->getTable('nwdrevslider/backup')}` (
    `id` int(9) NOT NULL AUTO_INCREMENT,
    `slide_id` int(9) NOT NULL,
    `slider_id` int(9) NOT NULL,
    `slide_order` int not NULL,
    `params` LONGTEXT NOT NULL,
    `layers` LONGTEXT NOT NULL,
    `settings` TEXT NOT NULL,
    `created` DATETIME NOT NULL,
    `session` VARCHAR(100) NOT NULL,
    `static` VARCHAR(20) NOT NULL,
    UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('nwdrevslider/css')}`
CHANGE `settings` `settings` longtext COLLATE 'utf8_general_ci' NULL AFTER `handle`,
CHANGE `hover` `hover` longtext COLLATE 'utf8_general_ci' NULL AFTER `settings`,
CHANGE `advanced` `advanced` longtext COLLATE 'utf8_general_ci' NULL AFTER `params`;

ALTER TABLE `{$this->getTable('nwdrevslider/animations')}`
CHANGE `settings` `settings` text COLLATE 'utf8_general_ci' NULL AFTER `params`;

ALTER TABLE `{$this->getTable('nwdrevslider/sliders')}`
CHANGE `params` `params` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `alias`,
CHANGE `settings` `settings` text COLLATE 'utf8_general_ci' NOT NULL AFTER `params`,
CHANGE `type` `type` varchar(191) COLLATE 'utf8_general_ci' NOT NULL DEFAULT '' AFTER `settings`;

ALTER TABLE `{$this->getTable('nwdrevslider/slides')}`
CHANGE `params` `params` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `slide_order`,
CHANGE `layers` `layers` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `params`;

ALTER TABLE `{$this->getTable('nwdrevslider/static')}`
CHANGE `params` `params` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `slider_id`,
CHANGE `layers` `layers` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `params`;

ALTER TABLE `{$this->getTable('nwdrevslider/options')}`
CHANGE `option` `option` longtext COLLATE 'utf8_general_ci' NOT NULL AFTER `handle`;

";

$installer = $this;
$installer->startSetup();
$installer->run($upgradeSQL);
$installer->endSetup();
