<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Lifetimecustomerform
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('lifetimecustomer_form', array('legend' => Mage::helper('affiliateplus')->__('Lifetime Customer Form')));

        if (Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales')) {
            $fieldset->addField('add_lifetime_customer', 'text', array(
                'label' => Mage::helper('affiliateplus')->__('Add lifetime customer by email'),
                'name' => 'add_lifetime_customer',
                'note'=> 'enter emails to assign to affiliate, separate by comma'
            ));
            $fieldset->addField('remove_lifetime_customer', 'text', array(
                'label' => Mage::helper('affiliateplus')->__('remove lifetime customer by email'),
                'name' => 'remove_lifetime_customer',
                'note' => 'enter email of customer who you want to remove from affiliate, separate by comma'
            ));
        }


        return parent::_prepareForm();
    }
}
