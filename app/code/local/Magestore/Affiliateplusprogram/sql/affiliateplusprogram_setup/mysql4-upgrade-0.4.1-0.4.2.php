<?php
/*
 Adam add upgrade for adding Priority for Program
22/07/2014
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'priority', 'int(11) NULL DEFAULT 0');

$installer->endSetup();