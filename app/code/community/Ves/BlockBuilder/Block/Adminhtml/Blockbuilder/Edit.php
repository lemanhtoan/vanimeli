<?php

class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        parent::__construct();
        $this->_objectId = "block_id";
        $this->_blockGroup = "ves_blockbuilder";
        $this->_controller = "adminhtml_blockbuilder";
        $this->_updateButton("save", "label", Mage::helper("ves_blockbuilder")->__("Save Item"));
        $this->_updateButton("save", "onclick", "saveForm()");
        $this->_updateButton("delete", "label", Mage::helper("ves_blockbuilder")->__("Delete Item"));


        $controller_name = $this->getRequest()->getControllerName();

        if($controller_name == "pagebuilder") { //Check controller pagebuilder
            Mage::register('is_pagebuilder', 1);
        } else {
            Mage::register('is_pagebuilder', 0);
        }

        if($controller_name == "productbuilder") { //Check controller pagebuilder
            Mage::register('is_productbuilder', 1);
        } else {
            Mage::register('is_productbuilder', 0);
        }

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
                                triggerSaveForm();
                                editForm.submit();
                            }   
							function saveAndContinueEdit(){
                                triggerSaveForm();
								editForm.submit($('edit_form').action+'back/edit/');
							}
                            function duplicateBlock() {
                                triggerSaveForm();
                                editForm.submit($('edit_form').action+'duplicate/1/');
                            }
						";
    }

    public function getHeaderText()
    {
        if (Mage::registry("block_data") && Mage::registry("block_data")->getId()) {

            return Mage::helper("ves_blockbuilder")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("block_data")->getTitle()));

        } else {

            return Mage::helper("ves_blockbuilder")->__("Add Item");

        }
    }
}