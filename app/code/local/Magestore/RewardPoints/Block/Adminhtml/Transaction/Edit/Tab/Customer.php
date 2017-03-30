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
 * Rewardpoints Customer Grid Select Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('rewardpointsCustomerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        if ($this->_getSelectedCustomer()) {
            $this->setDefaultFilter(array('in_customers' => 1));
        }
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group_id');
        
        // Join to get Points
        $collection->getSelect()
            ->joinLeft(array('rp' => $collection->getTable('rewardpoints/customer')),
                'e.entity_id = rp.customer_id',
                array('point_balance')
            );
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @param $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_customers') {
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', $customerId);
            } elseif ($this->_getSelectedCustomer()) {
                $this->getCollection()->addFieldToFilter('entity_id', array(
                    'neq'   => $this->_getSelectedCustomer()
                ));
            }
        } elseif ($column->getId() == 'point_balance') {
            $cond = $column->getFilter()->getCondition();
            if (isset($cond['from'])) {
                $this->getCollection()->getSelect()
                    ->where('rp.point_balance >= ?', $cond['from']);
            }
            if (isset($cond['to'])) {
                $this->getCollection()->getSelect()
                    ->where('rp.point_balance <= ?', $cond['to']);
            }
        } else {
            return parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Customer
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_customers', array(
            'header'            => Mage::helper('rewardpoints')->__('Select'),
            'header_css_class'  => 'a-center',
			'type'              => 'radio',
			'html_name'         => 'in_customers',
			'align'             => 'center',
			'index'             => 'entity_id',
			'values'            => array($this->_getSelectedCustomer()),
            'filter'            => false,
            'sortable'          => false,
        ));
        
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('rewardpoints')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'      => 'number',
        ));
        
        $this->addColumn('name', array(
            'header'    => Mage::helper('rewardpoints')->__('Name'),
            'index'     => 'name',
        ));
        
        $this->addColumn('email', array(
            'header'    => Mage::helper('rewardpoints')->__('Email'),
            'index'     => 'email',
            'renderer'  => 'rewardpoints/adminhtml_transaction_edit_tab_renderer_customer',
        ));
        
        $this->addColumn('point_balance', array(
            'header'    => Mage::helper('rewardpoints')->__('Point Balance'),
            'align'     => 'right',
            'index'     => 'point_balance',
            'type'      => 'number',
        ));
        
        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt'=> 0))
                ->load()
                ->toOptionHash(),
        ));
        
        return parent::_prepareColumns();
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getId()));
    }
    
    /**
     * get grid url (use for ajax load)
     * 
     * @return string
     */
    public function getGridUrl()
    {
       return $this->getUrl('*/*/customerGrid', array('_current' => true));
    }

    /**
     * @return mixed
     */
    protected function _getSelectedCustomer()
    {
        return $this->getRequest()->getParam('selected_customer_id', '0');
    }

    /**
     * @return mixed
     */
    public function getSelectedCustomer()
    {
        return $this->_getSelectedCustomer();
    }
}
