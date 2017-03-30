<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Transaction Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Magestore_RewardPoints_Block_Adminhtml_Transaction_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('rewardpoints/transaction')->getCollection()
			->addFieldToFilter('customer_id',array('neq'=>null));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header'    => Mage::helper('rewardpoints')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'transaction_id',
            'type'      => 'number',
        ));
        
        $this->addColumn('title', array(
            'header'    => Mage::helper('rewardpoints')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));
        
        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('rewardpoints')->__('Customer'),
            'align'     =>'left',
            'index'     => 'customer_email',
            'renderer'  => 'rewardpoints/adminhtml_transaction_renderer_customer',
        ));
        
        $this->addColumn('action', array(
            'header'    => Mage::helper('rewardpoints')->__('Action'),
            'align'     => 'left',
            'index'     => 'action',
            'type'      => 'options',
            'options'   => Mage::helper('rewardpoints/action')->getActionsHash(),
        ));
        
        $this->addColumn('point_amount', array(
            'header'    => Mage::helper('rewardpoints')->__('Points'),
            'align'     => 'right',
            'index'     => 'point_amount',
            'type'      => 'number',
        ));
        
        $this->addColumn('point_used', array(
            'header'    => Mage::helper('rewardpoints')->__('Points Used'),
            'align'     => 'right',
            'index'     => 'point_used',
            'type'      => 'number',
        ));
        
        $this->addColumn('created_time', array(
            'header'    => Mage::helper('rewardpoints')->__('Created On'),
            'index'     => 'created_time',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('expiration_date', array(
            'header'    => Mage::helper('rewardpoints')->__('Expires On'),
            'index'     => 'expiration_date',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('rewardpoints')->__('Status'),
            'align'     => 'left',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('rewardpoints/transaction')->getStatusHash(),
        ));
        
        $this->addColumn('store_id', array(
            'header'    => Mage::helper('rewardpoints')->__('Store View'),
            'align'     => 'left',
            'index'     => 'store_id',
            'type'      => 'options',
            'options'   => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true),
        ));
        
        $this->addColumn('view_action', array(
            'header'    => Mage::helper('rewardpoints')->__('View'),
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('rewardpoints')->__('View'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                )),
            'filter'    => false,
            'sortable'    => false,
            'index'        => 'stores',
            'is_system'    => true,
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('rewardpoints')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('rewardpoints')->__('XML'));
        
        return parent::_prepareColumns();
    }
    
    /**
     * prepare mass action for this grid
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('transaction_id');
        $this->getMassactionBlock()->setFormFieldName('transactions');

        $this->getMassactionBlock()->addItem('complete', array(
            'label'        => Mage::helper('rewardpoints')->__('Complete'),
            'url'        => $this->getUrl('*/*/massComplete'),
            'confirm'    => Mage::helper('rewardpoints')->__('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('cancel', array(
            'label'        => Mage::helper('rewardpoints')->__('Cancel'),
            'url'        => $this->getUrl('*/*/massCancel'),
            'confirm'    => Mage::helper('rewardpoints')->__('Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('expire', array(
            'label'        => Mage::helper('rewardpoints')->__('Expire'),
            'url'        => $this->getUrl('*/*/massExpire'),
            'confirm'    => Mage::helper('rewardpoints')->__('Are you sure?')
        ));
        
        return $this;
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    /**
     * get grid url (use for ajax load)
     * 
     * @return string
     */
    public function getGridUrl()
    {
       return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
