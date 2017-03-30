<?php

/**
 * @author Adam
 * 22/08/2014
 * Create this file to show order's status in configuration page
 */
class Magestore_Affiliateplus_Model_System_Config_Source_Orderstatus
{

    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label'=>Mage::helper('affiliateplus')->__('-- Please Select --')),
            array('value' => 'processing', 'label'=>Mage::helper('affiliateplus')->__('Processing')),
            array('value' => 'complete', 'label' => Mage::helper('affiliateplus')->__('Complete')),
        );
    }
}
