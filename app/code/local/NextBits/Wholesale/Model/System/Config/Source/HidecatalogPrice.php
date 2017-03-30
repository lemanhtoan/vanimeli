<?php
class NextBits_Wholesale_Model_System_Config_Source_HidecatalogPrice
{
    public function toOptionArray()
    {
        $helper = Mage::helper('wholesale');
        return array(
            array('value' => 0, 'label' => $helper->__('Select Option')),
            array('value' => 1, 'label' => $helper->__('Hide Price')),
            array('value' => 2, 'label' => $helper->__('Hide Catalog')),
        );
    }
}
