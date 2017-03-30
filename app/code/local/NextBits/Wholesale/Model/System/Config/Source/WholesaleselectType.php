<?php
class NextBits_Wholesale_Model_System_Config_Source_WholesaleselectType
{
    public function toOptionArray()
    {
        $helper = Mage::helper('wholesale');
        return array(
            array('value' => 0, 'label' => $helper->__('Select Option')),
            array('value' => 1, 'label' => $helper->__('Select Existing Website')),
            array('value' => 2, 'label' => $helper->__('Create New Website')),
        );
    }
}


