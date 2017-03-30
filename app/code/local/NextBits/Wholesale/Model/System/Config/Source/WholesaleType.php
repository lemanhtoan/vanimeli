<?php
class NextBits_Wholesale_Model_System_Config_Source_WholesaleType
{
    public function toOptionArray()
    {
        $helper = Mage::helper('wholesale');
        return array(
            array('value' => 'none', 'label' => $helper->__('Select Store or Website')),
            array('value' => 'w-store', 'label' => $helper->__('Wholesale Store')),
            array('value' => 'w-webs', 'label' => $helper->__('Wholesale Website')),
        );
    }
}
