<?php

$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$table = $installer->getConnection()
    ->newTable($installer->getTable('klarna/klarnacheckout_semaphore'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Id')
    ->addColumn('klarna_checkout_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'Klarna Checkout Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 40, array(), 'Status of semaphore')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Quote ID')
    ->addColumn('timestamp', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Semaphore Timestamp')
    ->addColumn('retry_attempts', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Retry attempts')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
    ), 'Created At')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Possible error message')
    ->addIndex(
        'UNQ_KLARNA_KLARNACHECKOUT_SEMAPHORE_ID',
        array('klarna_checkout_id', 'status'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Klarna Semaphore');

$installer->getConnection()->createTable($table);
$table = $installer->getConnection()
    ->newTable($installer->getTable('klarna/klarnacheckout_history'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
        ), 'Id')
    ->addColumn('klarna_checkout_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(), 'Klarna Checkout Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Quote ID')
    ->addColumn('reservation_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Reservation ID for non Rest API')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
    ), 'Order ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable' => false,
        ), 'Updated At')
    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Information')
    ->setComment('Klarna History');

$installer->getConnection()->createTable($table);

$installer->endSetup();
