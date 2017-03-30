<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_GiftCards
 * @copyright  Copyright (c) 2016 Webtex (http://www.webtexsoftware.com/)
 * @license    http://www.webtexsoftware.com/LICENSE-1.0.html
 */

/**
 * Gift Cards extension
 *
 * @category   Webtex
 * @package    Webtex_GiftCards
 * @author     Webtex Dev Team <dev@webtexsoftware.com>
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('giftcards_log')} (
    `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `card_id` int(10) NOT NULL,
    `card_code` varchar(64) NOT NULL,
    `card_amount` decimal(12,2) NOT NULL,
    `card_balance` decimal(12,2) NOT NULL,
    `card_status` tinyint unsigned NOT NULL DEFAULT 0,
    `order_id` varchar(20) NOT NULL,
    `card_action` varchar(10) ,
    `user_name` varchar(50) ,
    `user` varchar(10),
    `card_comment` varchar(100),
    `created_time` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
