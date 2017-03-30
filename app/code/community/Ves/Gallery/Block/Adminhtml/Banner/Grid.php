<?php

class Ves_Gallery_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
		
        parent::__construct();
	
        $this->setId('bannerGrid');
        $this->setDefaultSort('date_from');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		
    }

  //  protected function _getStore() {
   //     $storeId = (int) $this->getRequest()->getParam('store', 0);
   //     return Mage::app()->getStore($storeId);
   // }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ves_gallery/banner')->getCollection();
        //$store = $this->_getStore();
        //if ($store->getId()) {
        //    $collection->addStoreFilter($store);
       // }
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {  
        $this->addColumn('banner_id', array(
                'header'    => Mage::helper('ves_gallery')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'banner_id',
        ));
		$this->addColumn('title', array(
                'header'    => Mage::helper('ves_gallery')->__('Title'),
                'align'     =>'left',
                'index'     => 'title',
        ));
		
		
        $this->addColumn('file', array(
                'header'    => Mage::helper('ves_gallery')->__('File'),
                'align'     =>'left',
                'index'     => 'file',
                'renderer' => 'ves_gallery/adminhtml_renderer_image',
        ));		
		
        $this->addColumn('position', array(
                'header'    => Mage::helper('ves_gallery')->__('Position'),
                'align'     =>'left',
                'index'     => 'position',
        ));
		$this->addColumn('label', array(
                'header'    => Mage::helper('ves_gallery')->__('Group'),
                'align'     =>'left',
                'index'     => 'label',
        ));

        $this->addColumn('is_active', array(
                'header'    => Mage::helper('ves_gallery')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'is_active',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('ves_gallery')->__('Enabled'),
                        0 => Mage::helper('ves_gallery')->__('Disabled'),
                //3 => Mage::helper('ves_gallery')->__('Hidden'),
                ),
        ));

        $this->addColumn('action',
                array(
                'header'    =>  Mage::helper('ves_gallery')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                        array(
                                'caption'   => Mage::helper('ves_gallery')->__('Edit'),
                                'url'       => array('base'=> '*/*/edit'),
                                'field'     => 'id'
                        ),
                        array(
                                'caption'   => Mage::helper('ves_gallery')->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id'
                        )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() { 
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('banner');

        $this->getMassactionBlock()->addItem('delete', array(
                'label'    => Mage::helper('ves_gallery')->__('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('ves_gallery')->__('Are you sure?')
        ));

        $statuses = array(
                1 => Mage::helper('ves_gallery')->__('Enabled'),
                0 => Mage::helper('ves_gallery')->__('Disabled')
				);
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                'label'=> Mage::helper('ves_gallery')->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                        'visibility' => array(
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => Mage::helper('ves_gallery')->__('Status'),
                                'values' => $statuses
                        )
                )
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}