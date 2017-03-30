<?php

class Ves_Testimonial_Model_System_Config_Source_ListSourceType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'latest', 'label'=>Mage::helper('ves_testimonial')->__('Latest testimonials') ),
            array('value'=>'hit', 'label'=>Mage::helper('ves_testimonial')->__('Most Read') ),

        );
    }    
}
