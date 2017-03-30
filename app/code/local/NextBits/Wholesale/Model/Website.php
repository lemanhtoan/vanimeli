<?php
class NextBits_Wholesale_Model_Website {
    public function toOptionArray() {
        return Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(false, false);
    }
}