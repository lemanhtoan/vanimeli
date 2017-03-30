<?php


class Ves_BlockBuilder_Block_Adminhtml_Selector extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {

        $this->_controller = "adminhtml_selector";
        $this->_blockGroup = "ves_blockbuilder";
        $this->_headerText = Mage::helper("ves_blockbuilder")->__("Manage Css Selector Elements");
        $this->_addButtonLabel = Mage::helper("ves_blockbuilder")->__("Add New Item");
        parent::__construct();

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

        $this->setChild('quick_create_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_blockbuilder')->__('Quick Create Items'),
                'onclick'   => "openQuickCreatePopup()",
                'class'   => 'add'
                ))
        );


        return parent::_prepareLayout();
    }
    public function getImportButtonHtml() {
        return $this->getChildHtml('import_button');
    }

    public function getQuickCreateButtonHtml() {
        return $this->getChildHtml('quick_create_button');
    }
}