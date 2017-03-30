<?php

class Magestore_AffiliateplusReferFriend_Model_System_Config_Source_Parametervalue {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('affiliateplus')->__('Identify Code')),
            array('value' => 2, 'label' => Mage::helper('affiliateplus')->__('Affiliate ID')),
        );
    }

}
