<?php

class Ves_Landingpage_Block_Adminhtml_Slider_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
		
        parent::__construct();
 
        $this->setId('postGrid');
        $this->setDefaultSort('date_from');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		
    }

    /**
     * get value of the extension's configuration
     *
     * @return string
     */
    function getConfig( $key, $panel='ves_landingpage' ){
        if(isset($this->_config[$key])) {
            return $this->_config[$key];
        } else {
            return Mage::getStoreConfig("ves_landingpage/$panel/$key");
        }
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ves_landingpage/slider')->getCollection();
        $collection->setPageSize( $this->getConfig('limit'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {  
        $this->addColumn('caption_1', array(
                'header'    => Mage::helper('ves_landingpage')->__('Caption'),
                'align'     =>'center',
                //'width'     => '150px',
                'index'     => 'caption_1',
        ));

		$this->addColumn('class1', array(
                'header'    => Mage::helper('ves_landingpage')->__('Class'),
                'align'     =>'left',
                'index'     => 'class1',
        ));
		
		
		
        $this->addColumn('effect_1', array(
                'header'    => Mage::helper('ves_landingpage')->__('Effect'),
                'align'     =>'left',
                'index'     => 'effect_1',
        ));		

        $this->addColumn('status', array(
                'header'    => Mage::helper('ves_landingpage')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('ves_landingpage')->__('Enabled'),
                        0 => Mage::helper('ves_landingpage')->__('Disabled'),
                //3 => Mage::helper('ves_landingpage')->__('Hidden'),
                ),
        ));
        $this->addColumn('action',
                array(
                'header'    =>  Mage::helper('ves_landingpage')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                        array(
                                'caption'   => Mage::helper('ves_landingpage')->__('Edit'),
                                'url'       => array('base'=> '*/*/edit'),
                                'field'     => 'id'
                        ),
                        array(
                                'caption'   => Mage::helper('ves_landingpage')->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id',
                                'confirm'  => Mage::helper('ves_landingpage')->__('Are you sure?')
                        )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        return parent::_prepareColumns();
    }

     /**
     * Helper function to do after load modifications
     *
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    
    /**
     * Helper function to add store filter condition
     *
     * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        
        $this->getCollection()->addStoreFilter($value);
    }
    
 

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}