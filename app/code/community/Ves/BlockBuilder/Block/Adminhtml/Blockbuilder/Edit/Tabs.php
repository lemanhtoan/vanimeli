<?php
class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId("blockbuilder_tabs");
        $this->setDestElementId("edit_form");
        if( 1 ==  Mage::registry('is_pagebuilder')){
            $this->setTitle(Mage::helper("ves_blockbuilder")->__("Page Information"));
        } elseif(1 ==  Mage::registry('is_productbuilder')) {
            $this->setTitle(Mage::helper("ves_blockbuilder")->__("Product Information"));
        } else {
            $this->setTitle(Mage::helper("ves_blockbuilder")->__("Block Information"));
        }
        
    }

    protected function _beforeToHtml()
    {
        $builder_type_label = "Block";

        if( 1 ==  Mage::registry('is_pagebuilder')){
            $builder_type_label = "Page";
        }

        if( 1 ==  Mage::registry('is_productbuilder')){
            $builder_type_label = "Layout";
        }
        $this->addTab("form_section", array(
            "label" => Mage::helper("ves_blockbuilder")->__($builder_type_label." Information"),
            "title" => Mage::helper("ves_blockbuilder")->__($builder_type_label." Information"),
            "content" => $this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tab_form")->toHtml(),
        ));

        $this->addTab("design_section", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Design ".$builder_type_label),
            "title" => Mage::helper("ves_blockbuilder")->__("Design ".$builder_type_label),
            "content" => $this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tab_design")->toHtml(),
        ));

        if( 1 ==  Mage::registry('is_pagebuilder')){
            $this->addTab("cmspage_section", array(
                "label" => Mage::helper("ves_blockbuilder")->__("CMS Page Information"),
                "title" => Mage::helper("ves_blockbuilder")->__("CMS Page Information"),
                "content" => $this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tab_cms")->toHtml(),
            ));
        }

        if( 1 ==  Mage::registry('is_pagebuilder') || 1 ==  Mage::registry('is_productbuilder')){
            $this->addTab("settings_section", array(
                "label" => Mage::helper("ves_blockbuilder")->__("Settings"),
                "title" => Mage::helper("ves_blockbuilder")->__("Settings"),
                "content" => $this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tab_settings")->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
    }

}
