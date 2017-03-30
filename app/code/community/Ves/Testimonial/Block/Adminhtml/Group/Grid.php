<?php

class Ves_Testimonial_Block_Adminhtml_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
		
        parent::__construct();
 
        $this->setId('postGrid');
        $this->setDefaultSort('date_from');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		
    }

  //  protected function _getStore() {
   //     $storeId = (int) $this->getRequest()->getParam('store', 0);
   //     return Mage::app()->getStore($storeId);
   // }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ves_testimonial/group')->getCollection();
        //$store = $this->_getStore();
        //if ($store->getId()) {
        //    $collection->addStoreFilter($store);
       // }
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {  
        $this->addColumn('group_id', array(
                'header'    => Mage::helper('ves_testimonial')->__('ID'),
                'align'     =>'center',
                'width'     => '50px',
                'index'     => 'group_id',
        ));

       $this->addColumn('name', array(
                'header'    => Mage::helper('ves_testimonial')->__('Group Tabs Name'),
                'align'     =>'center',
                'width'     => '50px',
                'index'     => 'name',
        ));

        $this->addColumn('status', array(
                'header'    => Mage::helper('ves_testimonial')->__('Status'),
                'align'     => 'center',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('ves_testimonial')->__('Enabled'),
                        0 => Mage::helper('ves_testimonial')->__('Disabled'),
                //3 => Mage::helper('ves_testimonial')->__('Hidden'),
                ),
        ));

        $this->addColumn('action',
                array(
                'header'    =>  Mage::helper('ves_testimonial')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                        array(
                                'caption'   => Mage::helper('ves_testimonial')->__('Edit'),
                                'url'       => array('base'=> '*/*/edit'),
                                'field'     => 'id'
                        ),
                        array(
                                'caption'   => Mage::helper('ves_testimonial')->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id',
                                'confirm'  => Mage::helper('ves_testimonial')->__('Are you sure?')
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