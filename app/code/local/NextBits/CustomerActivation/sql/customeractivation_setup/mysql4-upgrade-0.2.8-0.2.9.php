<?php
$installer = $this;

$installer->startSetup();

$resource = Mage::getResourceModel('customer/customer');
$select = $installer->getConnection()->select()
    ->from($resource->getEntityTable(), $resource->getEntityIdField());
$customerIds = $installer->getConnection()->fetchCol($select);

$updatedCustomerIds = Mage::getResourceModel('customeractivation/customer')
    ->massSetActivationStatus($customerIds, 1);

$installer->endSetup();