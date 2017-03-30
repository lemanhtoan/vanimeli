<?php
class Ves_ContentTab_Model_System_Config_Source_ListGroup {

    public function toOptionArray() {
        $Collection = Mage::getModel('ves_contenttab/group')->getCollection();;
        $tmp = array();
        foreach($Collection as $cat) {
            $tmp["value"] = $cat->getId();
            $tmp["label"] = $cat->getName();
            $arr[] = $tmp;
        }
        return $arr;
    }
}