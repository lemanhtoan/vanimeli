<?php
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_Adminhtml_Testimonial_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
		
        parent::__construct();
		
	
        $this->setId('testimonialGrid');
        $this->setDefaultSort('testimonial_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
		
    }

  //  protected function _getStore() {
   //     $storeId = (int) $this->getRequest()->getParam('store', 0);
   //     return Mage::app()->getStore($storeId);
   // }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ves_testimonial/testimonial')->getCollection();
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
	
	
    protected function _prepareColumns() {  
	 
        $this->addColumn('testimonial_id', array(
                'header'    => Mage::helper('ves_testimonial')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'testimonial_id',
        ));
		$this->addColumn('avatar', array(
                'header'    => Mage::helper('ves_testimonial')->__('Avatar'),
                'align'     =>'center',
                'width'     => '120px',
                'index'     => 'avatar',
                'renderer'  => 'Ves_Testimonial_Block_Adminhtml_Renderer_Image'
        ));     
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', 
                    array (
                            'header' => Mage::helper('cms')->__('Store view'), 
                            'index' => 'store_id', 
                            'type' => 'store', 
                            'store_all' => true, 
                            'store_view' => true, 
                            'sortable' => false, 
                            'filter_condition_callback' => array (
                                    $this, 
                                    '_filterStoreCondition' ) ));
        }

		$this->addColumn('profile', array(
                'header'    => Mage::helper('ves_testimonial')->__('Profile'),
                'align'     =>'left',
                'index'     => 'profile',
        ));

        $this->addColumn('group_testimonial_id', array(
                'header'    => Mage::helper('ves_testimonial')->__('Group'),
                'align'     =>'left',
                'index'     => 'group_testimonial_id',
        ));

		$this->addColumn('position', array(
                'header'    => Mage::helper('ves_testimonial')->__('Sort Order'),
                'align'     =>'left',
                'index'     => 'position',
				 'width'     => '80px',
        ));
		
		$this->addColumn('is_active', array(
                'header'    => Mage::helper('ves_testimonial')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'is_active',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('ves_testimonial')->__('Enabled'),
                        0 => Mage::helper('ves_testimonial')->__('Disabled'),
                ),
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

    protected function _prepareMassaction() { 
        $this->setMassactionIdField('testimonial_id');
        $this->getMassactionBlock()->setFormFieldName('testimonial');

        $this->getMassactionBlock()->addItem('delete', array(
                'label'    => Mage::helper('ves_testimonial')->__('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('ves_testimonial')->__('Are you sure?')
        ));

        $statuses = array(
                1 => Mage::helper('ves_testimonial')->__('Enabled'),
                0 => Mage::helper('ves_testimonial')->__('Disabled')
				);
        //array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
                'label'=> Mage::helper('ves_testimonial')->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                        'visibility' => array(
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => Mage::helper('ves_testimonial')->__('Status'),
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