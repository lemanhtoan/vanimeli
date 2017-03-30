<?php
class NextBits_Wholesale_Model_Store {
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false);
    }
}