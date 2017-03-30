<?php
$installer = $this;

$installer->startSetup();
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'customer_activated')
            ->setData('used_in_forms', array('adminhtml_customer'))
            ->save();
}

$installer->endSetup();
