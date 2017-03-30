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
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Couponcode_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('couponcodesGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid
     */
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('rewardpointsreferfriends/rewardpointsrefercustomer_collection');
        $collection->getSelect()->joinLeft(array('customer_entity' => Mage::getModel('core/resource')->getTableName('customer/entity'))
                , 'main_table.customer_id = customer_entity.entity_id', array('customer_entity.*'));
        $this->setCollection($collection);
//        $collection = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->getCollection();
//        $this->setCollection($collection);
		$collection->addFieldToFilter('entity_id', array('neq' => 'NULL' ));
        parent::_prepareCollection();

        return $this;
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Coupon ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Customer ID'),
            'align' => 'left',
            'index' => 'entity_id',
            'width' => '50px',
        ));


//        $this->addColumn('name', array(
//            'header' => Mage::helper('rewardpointsreferfriends')->__('Name'),
//            'align' => 'left',
//            'width' => '200px',
//            'index' => 'name',
//        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Email'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'email',
        ));
        $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();
        $this->addColumn('group_id', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Group'),
            'width' => '150px',
            'index' => 'group_id',
            'type' => 'options',
            'options' => $groups,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header' => Mage::helper('rewardpointsreferfriends')->__('Website'),
                'align' => 'center',
                'width' => '200px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

        $this->addColumn('coupon', array(
            'header' => Mage::helper('rewardpointsreferfriends')->__('Coupon Code'),
            'align' => 'left',
//            'width' => '60px',
            'index' => 'coupon',
        ));
//        $this->addColumn('action', array(
//            'header' => Mage::helper('rewardpointsreferfriends')->__('Action'),
//            'width' => '100',
//            'type' => 'action',
//            'getter' => 'getId',
//            'actions' => array(
//                array(
//                    'caption' => Mage::helper('rewardpointsreferfriends')->__('Edit'),
//                    'url' => array('base' => '*/*/edit'),
//                    'field' => 'id'
//                )),
//            'filter' => false,
//            'sortable' => false,
//            'index' => 'stores',
//            'is_system' => true,
//        ));
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


        $this->getMassactionBlock()->addItem('print', array(
            'label' => $this->__('Print Coupon Code'),
            'url' => $this->getUrl('*/*/massPrint'),
        ));
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getEntityId()));
    }

    public function filterCallback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
    }

}