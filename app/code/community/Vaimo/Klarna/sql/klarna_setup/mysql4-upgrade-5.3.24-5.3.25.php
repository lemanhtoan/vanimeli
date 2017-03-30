<?php

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();
$table = $installer->getTable('sales_flat_creditmemo');
$connection->addColumn($table, 'vaimo_klarna_fee',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Klarna Payment Fee'
    )
);

$connection->addColumn($table, 'vaimo_klarna_fee_tax',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Klarna Payment Fee Tax'
    )
);

$connection->addColumn($table, 'vaimo_klarna_base_fee',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Base Klarna Payment Fee'
    )
);

$connection->addColumn($table, 'vaimo_klarna_base_fee_tax',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'length' => '12,4',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Base Klarna Payment Fee Tax'
    )
);

$installer->endSetup();
