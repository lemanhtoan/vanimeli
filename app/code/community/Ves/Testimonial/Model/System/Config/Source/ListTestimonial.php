<?php

class Ves_Testimonial_Model_System_Config_Source_ListTestimonial
{

    public function toOptionArray() {
        $Collection = Mage::getModel('ves_testimonial/group')->getCollection();
        $arr = array();
        foreach($Collection as $cat) {
            $tmp = array();
            $tmp["value"] = $cat->getId();
            $tmp["label"] = $cat->getName();
            $arr[] = $tmp;
        }
        return $arr;
    }
}