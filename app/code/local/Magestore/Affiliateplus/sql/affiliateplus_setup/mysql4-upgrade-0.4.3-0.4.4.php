<?php
/*
* Added by Adam 22/05/2015
*/

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplus/account'), 'key_shop', 'varchar(255) default ""');
$installer->run("
CREATE TABLE {$this->getTable('affiliateplus_account_product')}(
  `accountproduct_id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned  NOT NULL,
  UNIQUE(`account_id`,`product_id`),
  INDEX (`account_id`),
  INDEX (`product_id`),
  FOREIGN KEY (`account_id`) REFERENCES {$this->getTable('affiliateplus_account')} (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (`accountproduct_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();