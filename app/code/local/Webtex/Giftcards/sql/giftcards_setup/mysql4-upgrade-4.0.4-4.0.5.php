<?php
$installer = $this;
$installer->startSetup();

if (!$installer->getConnection()->tableColumnExists($installer->getTable('salesrule'), 'is_use_giftcard')) {
    $installer->getConnection()->addColumn(
        $installer->getTable('salesrule'),
        'is_use_giftcard',
        "smallint NOT NULL DEFAULT 0"
    );
}

$this->endSetup();
