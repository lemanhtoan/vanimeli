<?php
/*
* Added by Adam 14/08/2014
*/

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'affiliateplus_commission_item', 'VARCHAR(255) NULL DEFAULT NULL');
$installer->getConnection()->addColumn($installer->getTable('sales/invoice_item'), 'affiliateplus_commission_flag', 'smallint(2) NULL DEFAULT 0');
$installer->getConnection()->addColumn($installer->getTable('sales/creditmemo_item'), 'affiliateplus_commission_flag', 'smallint(2) NULL DEFAULT 0');

/* Changed By Adam 25/08/2014: dua config rel_nofollow ve ban standard*/
$installer->getConnection()->addColumn($installer->getTable('affiliateplus/banner'), 'rel_nofollow', 'smallint(6) NOT NULL default 0');

/* Changed By Adam 11/09/2014: to store refer by email into database*/
$installer->getConnection()->addColumn($installer->getTable('affiliateplus/account'), 'referred_by', 'varchar(255) default ""');

$installer->endSetup();