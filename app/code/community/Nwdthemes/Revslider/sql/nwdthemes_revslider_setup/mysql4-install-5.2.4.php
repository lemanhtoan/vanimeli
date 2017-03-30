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

$installSQL = "

DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/backup')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/css')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/animations')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/navigations')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/options')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/sliders')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/slides')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/static')}`;

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

CREATE TABLE `{$this->getTable('nwdrevslider/css')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `handle` text NOT NULL,
  `settings` LONGTEXT NULL,
  `hover` LONGTEXT NULL,
  `params` text NOT NULL,
  `advanced` LONGTEXT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/animations')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `handle` text NOT NULL,
  `params` text NOT NULL,
  `settings` text NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/navigations')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `handle` varchar(191) NOT NULL,
  `css` mediumtext NOT NULL,
  `markup` mediumtext NOT NULL,
  `settings` mediumtext,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/sliders')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `alias` tinytext,
  `params` LONGTEXT NOT NULL,
  `settings` text NOT NULL,
  `type` VARCHAR(191) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/slides')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `slider_id` int(9) NOT NULL,
  `slide_order` int(11) NOT NULL,
  `params` LONGTEXT NOT NULL,
  `layers` LONGTEXT NOT NULL,
  `settings` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/static')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `slider_id` int(9) NOT NULL,
  `params` LONGTEXT NOT NULL,
  `layers` LONGTEXT NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/options')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `handle` varchar(100) NOT NULL,
  `option` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

";

$installer = $this;
$installer->startSetup();
$installer->run($installSQL);
$installer->endSetup();
