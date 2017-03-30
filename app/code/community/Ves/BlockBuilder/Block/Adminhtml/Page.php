<?php


class Ves_BlockBuilder_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {

        $this->_controller = "adminhtml_blockbuilder";
        $this->_blockGroup = "ves_blockbuilder";
        $this->_headerText = Mage::helper("ves_blockbuilder")->__("Page Profile Manager");
        $this->_addButtonLabel = Mage::helper("ves_blockbuilder")->__("Add New Item");
        parent::__construct();

        if($this->hasData("template") && $template = $this->getData("template")) {
            $this->setTemplate($template);
        } else {
            $this->setTemplate('ves_blockbuilder/page/grid.phtml');
        }
    }
    protected function _prepareLayout() {
	
        $this->setChild('import_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_blockbuilder')->__('Import CSV'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/uploadCsv')."')",
                'class'   => 'add'
                ))
        );

        $this->setChild('sample_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_blockbuilder')->__('Install Sample Profile'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/sample')."')",
                'class'   => 'add'
                ))
        );

        return parent::_prepareLayout();
    }
    public function getSampleButtonHtml() {
        return $this->getChildHtml('sample_button');
    }
    public function getImportButtonHtml() {
        return $this->getChildHtml('import_button');
    }
}