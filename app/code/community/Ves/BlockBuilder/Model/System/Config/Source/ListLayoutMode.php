<?php

class Ves_BlockBuilder_Model_System_Config_Source_ListLayoutMode
{

    public function toOptionArray()
    {
        return array(   array("value"=>"0", "label"=>Mage::helper("ves_blockbuilder")->__("Don't Use")),
                        array("value"=>"auto", "label"=>Mage::helper("ves_blockbuilder")->__("Auto")),
                        array("value"=>"manual", "label"=>Mage::helper("ves_blockbuilder")->__("Manual"))
                    );
    }
}