<?php

class Ves_BlockBuilder_Model_System_Config_Source_ListAttributes
{
    public function toOptionArray()
    {
        $attributes = Mage::getModel('catalog/product')->getAttributes();
        $attributeArray = array();

        foreach($attributes as $a){

            foreach ($a->getEntityType()->getAttributeCodes() as $attributeName) {

                //$attributeArray[$attributeName] = $attributeName;
                $attributeArray[] = array(
                    'label' => $attributeName,
                    'value' => $attributeName
                );
            }
            break;
        }
        return $attributeArray; 
    }
}