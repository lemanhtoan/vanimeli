<?php

class Ves_BlockBuilder_Block_Adminhtml_Selector_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        parent::__construct();
        $this->_objectId = "selector_id";
        $this->_blockGroup = "ves_blockbuilder";
        $this->_controller = "adminhtml_selector";
        $this->_updateButton("save", "label", Mage::helper("ves_blockbuilder")->__("Save Item"));
        $this->_updateButton("save", "onclick", "saveForm()");
        $this->_updateButton("delete", "label", Mage::helper("ves_blockbuilder")->__("Delete Item"));


        $this->_addButton("saveandcontinue", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Save And Continue Edit"),
            "onclick" => "saveAndContinueEdit()",
            "class" => "save",
        ), -100);

        $this->_addButton("duplicate", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Duplicate"),
            "onclick" => "duplicateBlock()",
            "class" => "save",
        ), -100);


        $this->_formScripts[] = "
                            function saveForm(){
                                editForm.submit();
                            }   
							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
                            function duplicateBlock() {
                                editForm.submit($('edit_form').action+'duplicate/1/');
                            }
						";
    }

    public function getHeaderText()
    {
        if (Mage::registry("selector_data") && Mage::registry("selector_data")->getId()) {

            return Mage::helper("ves_blockbuilder")->__("Edit Selector Item '%s'", $this->htmlEscape(Mage::registry("selector_data")->getElementName()));

        } else {

            return Mage::helper("ves_blockbuilder")->__("Add Selector Item");

        }
    }
}