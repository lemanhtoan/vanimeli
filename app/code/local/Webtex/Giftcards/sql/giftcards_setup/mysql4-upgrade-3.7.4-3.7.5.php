<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/order'),'shipping_discount','decimal(12,4)');
$installer->getConnection()->addColumn($this->getTable('giftcards/order'),'order_item_id','int(10)');

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('giftcards_order_items')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `giftcard_id` int(10) NOT NULL,
    `order_id` int(10) NOT NULL,
    `order_item_id` int(10) NOT NULL,
    `quote_id` int(10) NOT NULL,
    `quote_item_id` int(10) NOT NULL,
    `discount` decimal(12,4) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();
