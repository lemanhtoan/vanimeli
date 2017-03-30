<?php
class  NextBits_HidePrice_Model_System_Config_Source_Category extends Mage_Adminhtml_Model_System_Config_Source_Category
{
    public function toOptionArray($bAddEmpty = true)
    {
        $oCategoryCollection = Mage::getResourceModel('catalog/category_collection');
        $oCategoryCollection->addAttributeToSelect('name')->load();

        $aCategoryOptions = array();
        if ($bAddEmpty) {
            $aCategoryOptions[] = array(
                'label' => Mage::helper('adminhtml')->__('-- Please select at least one category --'),
                'value' => ''
            );
        }
        foreach ($oCategoryCollection as $oCategory) {
            $aCategoryOptions[] = array(
                'label' => $oCategory->getName(),
                'value' => $oCategory->getId()
            );
        }
        return $aCategoryOptions;
    }
}