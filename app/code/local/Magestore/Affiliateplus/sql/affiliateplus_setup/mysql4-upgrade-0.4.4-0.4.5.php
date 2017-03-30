<?php
/*
 hainh add upgrade for adding Refefrring website
22-04-2014
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplus_account'), 'customize_commission_by', 'tinyint(1) NOT NULL default "1"');
$installer->getConnection()->addColumn($installer->getTable('affiliateplus_account'), 'customize_commission_proportion', 'decimal(12,4) default 1.0000');

$installer->endSetup();