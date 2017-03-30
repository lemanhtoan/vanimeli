<?php

class Ves_Gallery_Block_Adminhtml_Banner_Quickadd_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('slider_form', array('legend'=>Mage::helper('ves_gallery')->__('General Information')));
       
		$fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
		
	 
        
        return parent::_prepareForm();
    }
}
