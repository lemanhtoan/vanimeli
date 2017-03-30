<?php

class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $controller_name = $this->getRequest()->getControllerName();
        $collection = Mage::getModel("ves_blockbuilder/block")->getCollection();

        if($controller_name == "pagebuilder") { //Check controller pagebuilder
            $collection->addFieldToFilter("block_type", "page");
        } elseif($controller_name == "blockbuilder") { //Check controller blockbuilder
            $collection->addFieldToFilter("block_type",
                                            array(
                                                array('null' => true),

                                                array("nin" => array("page","product"))
                                            ));

        } elseif($controller_name == "productbuilder") { //Check controller pagebuilder
            $collection->addFieldToFilter("block_type", "product");
        }
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn("block_id", array(
            "header" => Mage::helper("ves_blockbuilder")->__("ID"),
            "align" => "right",
            "width" => "50px",
            "type" => "number",
            "index" => "block_id",
        ));

        $this->addColumn("title", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Block Title"),
            "align" => "right",
            "index" => "title",
        ));

        $this->addColumn("alias", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Block Code"),
            "align" => "right",
            "index" => "alias"
        ));

        $this->addColumn("shortcode", array(
            "header" => Mage::helper("ves_blockbuilder")->__("Block ShortCode"),
            "align" => "right",
            "width" => "30%",
            "index" => "block_id",
            'type'      => 'text',
            'renderer'  => 'Ves_BlockBuilder_Block_Adminhtml_Renderer_Shortcode'
        ));
        

        if($this->_isPageBuilder() || $this->_isProductBuilder()) {
            /**
             * Check is single store mode
             */
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('store_id', array(
                    'header'        => Mage::helper('cms')->__('Store View'),
                    'index'         => 'store_id',
                    'type'          => 'store',
                    'store_all'     => true,
                    'store_view'    => true,
                    'sortable'      => false,
                    'filter' => false,
                    'filter_condition_callback'
                                    => array($this, '_filterStoreCondition'),
                ));
            }
        } 

        if(!$this->_isPageBuilder()) {
            $this->addColumn('show_from', array(
                'header'    => Mage::helper('ves_blockbuilder')->__('Date From'),
                'align'     =>'left',
                'index'     => 'show_from',
            ));
            
            
            
            $this->addColumn('show_to', array(
                    'header'    => Mage::helper('ves_blockbuilder')->__('Date To'),
                    'align'     =>'left',
                    'index'     => 'show_to',
            ));
        }

        $this->addColumn('status', array(
            'header' => Mage::helper('ves_blockbuilder')->__('Active'),
            'index' => 'status',
            'type' => 'options',
            'options' => Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Grid::getOptionYesNo(),
        ));

        if($this->_isPageBuilder()) {
            $this->addColumn('page_actions', array(
                'header'    => Mage::helper('cms')->__('Action'),
                'width'     => 10,
                'sortable'  => false,
                'filter'    => false,
                'renderer'  => 'Ves_BlockBuilder_Block_Adminhtml_Renderer_Action',
            ));
        }
        

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

    protected function _isPageBuilder() {
        $controller_name = $this->getRequest()->getControllerName();
        
        if($controller_name == "pagebuilder") { //Check controller pagebuilder
            return true;
        }
        return false;
    }
    protected function _isProductBuilder() {
        $controller_name = $this->getRequest()->getControllerName();

        if($controller_name == "productbuilder") { //Check controller pagebuilder
            return true;
        }
        return false;
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
        foreach (Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Grid::getOptionYesNo() as $k => $v) {
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