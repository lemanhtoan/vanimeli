<?php
class NextBits_LoginCatalog_Model_System_Config_Source_RedirectType
{
    public function toOptionArray()
    {
        $helper = Mage::helper('logincatalog');
        return array(
            array('value' => 0, 'label' => $helper->__('Login Page')),
            array('value' => 1, 'label' => $helper->__('CMS Page')),
        );
    }
}
