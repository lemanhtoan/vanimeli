<?php

class NWT_Languagepack_Block_Adminhtml_Languagepack_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'languagepack';
        $this->_controller = 'adminhtml_languagepack';
        
        $this->_updateButton('save', 'label', Mage::helper('languagepack')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('languagepack')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('languagepack_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'languagepack_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'languagepack_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('languagepack_data') && Mage::registry('languagepack_data')->getId() ) {
            return Mage::helper('languagepack')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('languagepack_data')->getTitle()));
        } else {
            return Mage::helper('languagepack')->__('Add Item');
        }
    }
}