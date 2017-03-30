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
 * Rewardpoints Spending Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Spending_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Magestore_RewardPoints_Block_Adminhtml_Spending_Grid constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setId('rewardpointsSpendingGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Spending_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('rewardpoints/rate')->getSpendingRates();
        $this->setCollection($collection);
        parent::_prepareCollection();

        // Prepare website, customer group for grid
        foreach ($this->getCollection() as $rate) {
            $rate->setData('website_ids', explode(',', $rate->getData('website_ids')));
            $rate->setData('customer_group_ids', explode(',', $rate->getData('customer_group_ids')));
        }
        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Spending_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('rate_id', array(
            'header' => Mage::helper('rewardpoints')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rate_id',
            'type' => 'number',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header' => Mage::helper('rewardpoints')->__('Website'),
                'align' => 'left',
                'width' => '200px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'index' => 'website_ids',
                'filter_condition_callback' => array($this, 'filterCallback'),
                'sortable' => false,
            ));
        }

        $this->addColumn('customer_group_ids', array(
            'header' => Mage::helper('rewardpoints')->__('Customer Groups'),
            'align' => 'left',
            'index' => 'customer_group_ids',
            'type' => 'options',
            'width' => '200px',
            'sortable' => false,
            'options' => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gt' => 0))
                    ->load()
                    ->toOptionHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));

        $this->addColumn('points', array(
            'header' => Mage::helper('rewardpoints')->__('Spending Point(s)'),
            'align' => 'left',
            'index' => 'points',
            'type' => 'number',
        ));

        $this->addColumn('direction', array(
            'header' => Mage::helper('rewardpoints')->__('Type'),
            'align' => 'left',
            'index' => 'direction',
            'type' => 'options',
            'options' => Mage::getSingleton('rewardpoints/rate')->getSpendingDirectionHash(),
        ));

        $this->addColumn('money', array(
            'header' => Mage::helper('rewardpoints')->__('Discount received'),
            'align' => 'right',
            'index' => 'money',
            'type' => 'number',
            'renderer' => 'rewardpoints/adminhtml_spending_renderer_money',
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('rewardpoints')->__('Priority'),
            'align' => 'right',
            'type' =>'number',
            'index' => 'sort_order'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('rewardpoints')->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('rewardpoints/system_status')->getOptionArray(),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('rewardpoints')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('rewardpoints')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        Mage::dispatchEvent('rewardpoints_adminhtml_spending_rate_grid', array('grid' => $this));

        return parent::_prepareColumns();
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get grid url (use for ajax load)
     * 
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Callback filter for Website/ Customer group
     * 
     * @param type $collection
     * @param type $column
     * @return type
     */
    public function filterCallback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (!is_null(@$value)) {
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
        }
    }

    /**
     * @return $this
     */
    public function _prepareMassaction() {
        $this->setMassactionIdField('rate_id');
        $this->getMassactionBlock()->setFormFieldName('rate');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rewardpoints')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rewardpoints')->__('Are you sure?')
        ));


        $this->getMassactionBlock()->addItem('change_status', array(
            'label' => Mage::helper('rewardpoints')->__('Change status'),
            'url' => $this->getUrl('*/*/massChangeStatus'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('rewardpoints')->__('Status'),
                    'values' => Mage::getSingleton('rewardpoints/system_status')->getOptionArray(),
                )
            )
        ));

        return $this;
    }

}
