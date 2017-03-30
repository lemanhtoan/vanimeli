<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

$installer = $this;
$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/css')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/animations')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/options')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/settings')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/sliders')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/slides')}`;
DROP TABLE IF EXISTS `{$this->getTable('nwdrevslider/static')}`;

CREATE TABLE `{$this->getTable('nwdrevslider/css')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `handle` text NOT NULL,
  `settings` text,
  `hover` text,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/animations')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `handle` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/options')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `handle` varchar(100) NOT NULL,
  `option` text NOT NULL
) ENGINE='InnoDB' COLLATE 'utf8_general_ci';

CREATE TABLE `{$this->getTable('nwdrevslider/settings')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `general` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/sliders')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `alias` tinytext,
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/slides')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `slider_id` int(9) NOT NULL,
  `slide_order` int(11) NOT NULL,
  `params` text NOT NULL,
  `layers` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('nwdrevslider/static')}` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `slider_id` int(9) NOT NULL,
  `params` text NOT NULL,
  `layers` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
