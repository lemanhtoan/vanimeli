<?php
class Ves_BlockBuilder_Block_Adminhtml_Selector_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("selector_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("ves_blockbuilder")->__("General Information"));
        
    }

    protected function _beforeToHtml()
    {
        $builder_type_label = "Selector";

      
        $this->addTab("form_section", array(
            "label" => Mage::helper("ves_blockbuilder")->__($builder_type_label." Information"),
            "title" => Mage::helper("ves_blockbuilder")->__($builder_type_label." Information"),
            "content" => $this->getLayout()->createBlock("ves_blockbuilder/adminhtml_selector_edit_tab_form")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
