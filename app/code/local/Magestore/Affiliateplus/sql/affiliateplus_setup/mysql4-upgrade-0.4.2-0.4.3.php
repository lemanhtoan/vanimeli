<?php
/*
* Added by Adam 22/05/2015
*/

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'affiliateplus_credit', 'decimal(12,4) default 0.0000');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'base_affiliateplus_credit', 'decimal(12,4) default 0.0000');

$installer->endSetup();