<?php

class Ves_BlockBuilder_Block_Adminhtml_Selector_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("selector_id", array(
            "header" => Mage::helper("ves_blockbuilder")->__("ID"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "selector_id",
        ));


        $this->addColumn("element_tab", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Tab"),
            "align" => "right",
            "index" => "element_tab",
            "width" => "10%",
            "type"  => "options",
            'options'   => array(
                'general' => Mage::helper("ves_blockbuilder")->__('General'),
                'elements' => Mage::helper('ves_blockbuilder')->__('Products'),
                'custom' => Mage::helper('ves_blockbuilder')->__('Custom')
            )
        ));

        $this->addColumn("element_group", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Element Group"),
            "align" => "right",
            "index" => "element_group",
            "type"  => "options",
            "options" => Mage::helper("ves_blockbuilder")->getSelectorGroups(),
            "renderer" => "ves_blockbuilder/adminhtml_renderer_elementGroup",
        ));

        $this->addColumn("element_name", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Element Label"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_name"
        ));

        $this->addColumn("element_type", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Type"),
            "align" => "right",
            "width" => "20%",
            "index" => "element_type",
            "type"  => "options",
            "options" => Mage::helper("ves_blockbuilder")->getSelectorTypes(),
            "renderer" => "ves_blockbuilder/adminhtml_renderer_elementType"
        ));

        $this->addColumn("element_selector", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Css Selector"),
            "align" => "right",
            "width" => "30%",
            "index" => "element_selector"
        ));

        $this->addColumn("position", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Position"),
            "align" => "right",
            "width" => "10%",
            "index" => "position"
        ));


        $this->addColumn('status', array(
            'header' => Mage::helper('ves_blockbuilder')->__('Active'),
            'index' => 'status',
            'type' => 'options',
            'options' => Ves_BlockBuilder_Block_Adminhtml_Selector_Grid::getOptionYesNo(),
        ));
        

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl("*/*/edit", array("id" => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $statuses = array(
                1 => Mage::helper('ves_blockbuilder')->__('Enabled'),
                2 => Mage::helper('ves_blockbuilder')->__('Disabled')
                );
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                'label'=> Mage::helper('ves_blockbuilder')->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                'visibility' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('ves_blockbuilder')->__('Status'),
                        'values' => $statuses
                        )
                )
        ));
        $this->getMassactionBlock()->addItem('remove_block', array(
            'label' => Mage::helper('ves_blockbuilder')->__('Delete Selectors'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('ves_blockbuilder')->__('Are you sure?')
        ));
        return $this;
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

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }




}