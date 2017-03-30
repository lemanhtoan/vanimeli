<?php
class Ves_Widgets_Model_System_Config_Source_ListGroup {

    public function toOptionArray() {
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $tmp = array();
        foreach($rules as $rule) {
            if ($rule->getIsActive()) {
                $tmp["value"] = $rule->getId();
                $tmp["label"] = $rule->getName();
                $arr[] = $tmp;
            }
        }
        return $arr;
    }
}