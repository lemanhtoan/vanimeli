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
 * Rewardpoints Tab on Customer Edit Form Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_History
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_History constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpointsTransactionGrid');
        $this->setDefaultSort('rewardpoints_transaction_transaction_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_History
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getSingleton('rewardpoints/transaction')->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_History
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
        
        $this->addExportType('adminhtml/reward_customer/exportCsv'
            , Mage::helper('rewardpoints')->__('CSV')
        );
        $this->addExportType('adminhtml/reward_customer/exportXml'
            , Mage::helper('rewardpoints')->__('XML')
        );
        return parent::_prepareColumns();
    }
    
    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array || Varien_Object $column
     * @return  Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_History
     */
    public function addColumn($columnId, $column)
    {
        $columnId = 'rewardpoints_transaction_' . $columnId;
        return parent::addColumn($columnId, $column);
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/reward_transaction/edit', array('id' => $row->getId()));
    }
    
    /**
     * get grid url (use for ajax load)
     * 
     * @return string
     */
    public function getGridUrl()
    {
       return $this->getUrl('adminhtml/reward_customer/grid', array('_current' => true));
    }
}
