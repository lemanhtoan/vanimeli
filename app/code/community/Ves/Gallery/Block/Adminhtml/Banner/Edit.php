<?php

class Ves_Gallery_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'id';
        $this->_blockGroup  = 'ves_gallery';
        $this->_controller  = 'adminhtml_banner';

        $this->_updateButton('save', 'label', Mage::helper('ves_gallery')->__('Save Record'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_gallery')->__('Delete Record'));

        //$this->_addButton('saveandcontinue', array(
        //    'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
        //    'onclick'   => 'saveAndContinueEdit()',
        //    'class'     => 'save',
        //), -100);

        //$this->_formScripts[] = "
       //     function saveAndContinueEdit(){
       //         editForm.submit($('edit_form').action+'back/edit/');
       //     }
       // ";
    }

    public function getHeaderText()
    {
        return Mage::helper('ves_gallery')->__("Edit Record '%s'", $this->htmlEscape(Mage::registry('banner_data')->getLabel()));
    }
}