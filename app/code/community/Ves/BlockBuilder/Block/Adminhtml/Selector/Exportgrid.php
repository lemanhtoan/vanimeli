<?php

class Ves_BlockBuilder_Block_Adminhtml_Selector_Exportgrid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId("selectorGrid");
        $this->setDefaultSort("selector_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $controller_name = $this->getRequest()->getControllerName();
        $collection = Mage::getModel("ves_blockbuilder/selector")->getCollection();

        $IDList = $this->getRequest()->getParam('internal_ids');
        if($IDList) {
           $IDList = explode(",",$IDList);
           $collection->addFieldToFilter("selector_id", array('in'=>$IDList));
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("selector_id", array(
            "header" => Mage::helper("ves_blockbuilder")->__("selector_id"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "selector_id",
        ));

        $this->addColumn("element_name", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_name"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_name"
        ));

        $this->addColumn("element_tab", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_tab"),
            "align" => "right",
            "index" => "element_tab",
        ));

        $this->addColumn("element_group", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_group"),
            "align" => "right",
            "index" => "element_group"
        ));

        $this->addColumn("element_type", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_type"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_type"
        ));

        $this->addColumn("element_selector", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_selector"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_selector"
        ));

        $this->addColumn("element_attrs", array(
            "header" => Mage::helper("ves_blockbuilder")->__("element_attrs"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_attrs"
        ));

        $this->addColumn("template", array(
            "header" => Mage::helper("ves_blockbuilder")->__("template"),
            "align" => "right",
            "width" => "30%",
            "index" => "template"
        ));

        $this->addColumn("position", array(
            "header" => Mage::helper("ves_blockbuilder")->__("position"),
            "align" => "right",
            "width" => "30%",
            "index" => "position"
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('ves_blockbuilder')->__('status'),
            'index' => 'status',
            'align'     =>'left'
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }


    static public function getOptionYesNo()
    {
        $data_array = array();
        $data_array[1] = Mage::helper('ves_blockbuilder')->__('Enabled');
        $data_array[2] = Mage::helper('ves_blockbuilder')->__('Disabled');
        return ($data_array);
    }

    static public function getValueYesNo()
    {
        $data_array = array();
        foreach (Ves_BlockBuilder_Block_Adminhtml_Selector_Grid::getOptionYesNo() as $k => $v) {
            $data_array[] = array('value' => $k, 'label' => $v);
        }
        return ($data_array);

    }


}