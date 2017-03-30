<?php
class Ves_Testimonial_Model_System_Config_Source_ListLayouts
{
	
    public function toOptionArray()
    {
        return array(
            array('value' => "list", 'label'=>Mage::helper('adminhtml')->__('List')),
            array('value' => "carousel", 'label'=>Mage::helper('adminhtml')->__('Carousel'))
        );
    }
}
