<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Base
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

$installer = Mage::getModel('eav/entity_setup', 'core_setup');
//$installer->startSetup();
$installer->run("
	CREATE TABLE IF NOT EXISTS `{$installer->getTable('plumbase_product')}` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `signature` char(32) NOT NULL,
	  `status` int(11) NOT NULL,
	  `date` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `id` (`id`)
	) ENGINE=MyISAM;
");
//$installer->endSetup();
