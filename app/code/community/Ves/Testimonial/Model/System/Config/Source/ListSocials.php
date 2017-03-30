<?php
class Ves_Testimonial_Model_System_Config_Source_ListSocials {
	
    public function toOptionArray()
    {
        return array(
            array('value' => "1", 'label'=>Mage::helper('adminhtml')->__('Facebook')),
            array('value' => "2", 'label'=>Mage::helper('adminhtml')->__('Twiter')),
            array('value' => "3", 'label'=>Mage::helper('adminhtml')->__('google')),
            array('value' => "4", 'label'=>Mage::helper('adminhtml')->__('intagram')),
        );
    }
}
