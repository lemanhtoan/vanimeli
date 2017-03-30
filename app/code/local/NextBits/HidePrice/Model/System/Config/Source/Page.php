<?php
class NextBits_HidePrice_Model_System_Config_Source_Page extends Mage_Adminhtml_Model_System_Config_Source_Cms_Page
{
    public function toOptionArray()
    {
        if (!$this->_options) {
            parent::toOptionArray();
            $aNewCmsOption = array(
                'value' => '',
                'label' => Mage::helper('hideprice')->__('-- Please Select --')
            );

            $aCustomerLogin = array(
                'value' => 'customer/account/login',
                'label' => Mage::helper('hideprice')->__('Customer Login')
            );
            array_unshift($this->_options, $aNewCmsOption, $aCustomerLogin);
        }
        return $this->_options;
    }
}