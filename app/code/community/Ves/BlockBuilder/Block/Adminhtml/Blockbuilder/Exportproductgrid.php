<?php

class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Exportproductgrid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId("blockbuilderGrid");
        $this->setDefaultSort("block_id");
        $this->setDefaultDir("DESC");
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel("ves_blockbuilder/block")->getCollection();
        $collection->addFieldToFilter("block_type", "product");
        $IDList = $this->getRequest()->getParam('internal_ids');
        if($IDList) {
           $IDList = explode(",",$IDList);
           $collection->addFieldToFilter("block_id", array('in'=>$IDList));
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
       $this->addColumn("block_id", array(
            "header" => Mage::helper("ves_blockbuilder")->__("block_id"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "block_id",
        ));

        $this->addColumn("title", array(
            "header" => Mage::helper("ves_blockbuilder")->__("title"),
            "align" => "right",
            "index" => "title",
        ));

        $this->addColumn("alias", array(
            "header" => Mage::helper("ves_blockbuilder")->__("alias"),
            "align" => "right",
            "index" => "alias"
        ));

        $this->addColumn("shortcode", array(
            "header" => Mage::helper("ves_blockbuilder")->__("shortcode"),
            "align" => "right",
            "width" => "30%",
            "index" => "shortcode",
        ));

        $this->addColumn("created", array(
            "header" => Mage::helper("ves_blockbuilder")->__("created"),
            "align" => "right",
            "width" => "30%",
            "index" => "created",
        ));

        $this->addColumn("modified", array(
            "header" => Mage::helper("ves_blockbuilder")->__("modified"),
            "align" => "right",
            "width" => "30%",
            "index" => "modified",
        ));

        $this->addColumn("customer_group", array(
            "header" => Mage::helper("ves_blockbuilder")->__("customer_group"),
            "align" => "right",
            "width" => "30%",
            "index" => "customer_group",
        ));

        $this->addColumn("prefix_class", array(
            "header" => Mage::helper("ves_blockbuilder")->__("prefix_class"),
            "align" => "right",
            "width" => "30%",
            "index" => "prefix_class",
        ));

        $this->addColumn("position", array(
            "header" => Mage::helper("ves_blockbuilder")->__("position"),
            "align" => "right",
            "width" => "30%",
            "index" => "position",
        ));

        $this->addColumn("params", array(
            "header" => Mage::helper("ves_blockbuilder")->__("params"),
            "align" => "right",
            "width" => "30%",
            "index" => "params",
        ));

        $this->addColumn("block_type", array(
            "header" => Mage::helper("ves_blockbuilder")->__("block_type"),
            "align" => "right",
            "width" => "30%",
            "index" => "block_type",
        ));

        $this->addColumn("container", array(
            "header" => Mage::helper("ves_blockbuilder")->__("container"),
            "align" => "right",
            "width" => "30%",
            "index" => "container",
        ));

        $this->addColumn('show_from', array(
                'header'    => Mage::helper('ves_blockbuilder')->__('show_from'),
                'align'     =>'left',
                'index'     => 'show_from',
        ));
        
        
        
        $this->addColumn('show_to', array(
                'header'    => Mage::helper('ves_blockbuilder')->__('show_to'),
                'align'     =>'left',
                'index'     => 'show_to',
        ));
        $this->addColumn('settings', array(
                'header'    => Mage::helper('ves_blockbuilder')->__('settings'),
                'align'     =>'left',
                'index'     => 'settings',
        ));
        $this->addColumn('layout_html', array(
                'header'    => Mage::helper('ves_blockbuilder')->__('layout_html'),
                'align'     =>'left',
                'index'     => 'layout_html',
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
            'label' => Mage::helper('ves_blockbuilder')->__('Remove Block'),
            'url' => $this->getUrl('*/adminhtml_blockbuilder/massDelete'),
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
        foreach (Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Grid::getOptionYesNo() as $k => $v) {
            $data_array[] = array('value' => $k, 'label' => $v);
        }
        return ($data_array);

    }


}