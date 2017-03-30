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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('rewardpointsreferfriendsGrid');
        $this->setDefaultSort('special_refer_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as $offer) {
            $offer->setData('website_ids', explode(',', $offer->getData('website_ids')));
            $offer->setData('customer_group_ids', explode(',', $offer->getData('customer_group_ids')));
        }
        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('special_refer_id', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'type'=>'number',
            'index' => 'special_refer_id',
        ));

        $this->addColumn('title', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Title'),
            'align' => 'left',
            'index' => 'title',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header' => Mage::helper('rewardpointsreferfriends')->__('Website'),
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
            'header' => Mage::helper('rewardpointsreferfriends')->__('Customer Group IDs'),
            'align' => 'left',
            'index' => 'customer_group_ids',
            'type' => 'options',
            'width' => '200px',
            'sortable' => false,
            'options' => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gteq' => 0))
                    ->load()
                    ->toOptionHash(),
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));

        $this->addColumn('from_date', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Starting Date'),
            'align' => 'left',
            'index' => 'from_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'datetime',
        ));

        $this->addColumn('to_date', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Expired Date'),
            'align' => 'left',
            'index' => 'to_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'datetime',
        ));
        $this->addColumn('status', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => Mage::helper('rewardpointsreferfriends')->__('Active'),
                2 => Mage::helper('rewardpointsreferfriends')->__('InActive'),
            ),
        ));
        $this->addColumn('priority', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Priority'),
            'align' => 'left',
            'width' => '60px',
            'index' => 'priority',
            'type'=>'number',
        ));
        $this->addColumn('action', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('rewardpointsreferfriends')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

//        $this->addExportType('*/*/exportCsv', Mage::helper('rewardpointsreferfriends')->__('CSV'));
//        $this->addExportType('*/*/exportXml', Mage::helper('rewardpointsreferfriends')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('special_refer_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpointsreferfriends');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('rewardpointsreferfriends')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('rewardpointsreferfriends/status')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('rewardpointsreferfriends')->__('Status'),
                    'values' => $statuses
                ))
        ));
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function filterCallback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
    }

}