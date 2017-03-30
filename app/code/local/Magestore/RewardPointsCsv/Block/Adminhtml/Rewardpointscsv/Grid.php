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
 * Rewardpointscsv Grid Block
 *
 * @category    Magestore
 * @package     Magestore_RewardPointsCsv
 * @author      Magestore Developer
 */
class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Grid constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setId('rewardpointscsvGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Grid
     */
    protected function _prepareCollection() {
//        $collection = Mage::getModel('rewardpointscsv/rewardpointscsv')->getCollection();
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
        $collection->getSelect()->joinLeft(array('customer_reward' => Mage::getModel('core/resource')->getTableName('rewardpoints/customer'))
            , 'e.entity_id = customer_reward.customer_id', array('customer_reward.*'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Grid
     */
    protected function _prepareColumns() {
        if ($this->getIsCSV()) {
            // column for export CSV
            $this->addColumn('email', array(
                'header' => Mage::helper('rewardpointscsv')->__('Email'),
                'index' => 'email',
            ));
            $this->addColumn('website_id', array(
                'header' => Mage::helper('rewardpointscsv')->__('Website'),
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
            $this->addColumn('point_balance', array(
                'header' => Mage::helper('rewardpointscsv')->__('Point Change'),
                'index' => 'point_balance',
            ));
            $this->addColumn('expiration_date', array(
                'header' => Mage::helper('rewardpointscsv')->__('Points expire after'),
                'value' => '0'
            ));
            
        } else {
            $this->addColumn('entity_id', array(
                'header' => Mage::helper('rewardpointscsv')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'entity_id',
                'type' => 'number'
            ));
            $this->addColumn('name', array(
                'header' => Mage::helper('rewardpointscsv')->__('Name'),
                'align' => 'left',
                'index' => 'name',
            ));
            $this->addColumn('email', array(
                'header' => Mage::helper('rewardpointscsv')->__('Email'),
                'align' => 'left',
                'index' => 'email',
            ));
            $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();
            $this->addColumn('group_id', array(
                'header' => Mage::helper('rewardpointscsv')->__('Group'),
                'width' => '150px',
                'index' => 'group_id',
                'type' => 'options',
                'options' => $groups,
            ));
            $this->addColumn('Telephone', array(
                'header' => Mage::helper('rewardpointscsv')->__('Telephone'),
                'width' => '100',
                'index' => 'billing_telephone'
            ));

            $this->addColumn('billing_postcode', array(
                'header' => Mage::helper('rewardpointscsv')->__('ZIP'),
                'width' => '90',
                'index' => 'billing_postcode',
            ));

            $this->addColumn('billing_country_id', array(
                'header' => Mage::helper('rewardpointscsv')->__('Country'),
                'width' => '100',
                'type' => 'country',
                'index' => 'billing_country_id',
            ));

            $this->addColumn('billing_region', array(
                'header' => Mage::helper('rewardpointscsv')->__('State/Province'),
                'width' => '100',
                'index' => 'billing_region',
            ));
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('website_id', array(
                    'header' => Mage::helper('rewardpointscsv')->__('Website'),
                    'align' => 'center',
                    'width' => '80px',
                    'type' => 'options',
                    'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                    'index' => 'website_id',
                ));
            }
            $this->addColumn('point_balance', array(
                'header' => Mage::helper('rewardpointscsv')->__('Point Balance'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'point_balance',
                'filter_condition_callback' => array($this, 'filterCallback'),
//            'filter_index' => 'customer_reward.point_balance',
                'order_callback' => 'sort_balance',
                'type' => 'number',
//            'filter'    => false,
                'renderer' => 'rewardpointscsv/adminhtml_rewardpointscsv_renderer_point',
            ));
            $this->addColumn('action', array(
                'header' => Mage::helper('customer')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('rewardpointscsv')->__('View'),
                        'url' => array('base' => 'adminhtml/customer/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        if (!function_exists('sort_balance')) {

            /**
             * @param Mage_Customer_Model_Resource_Customer_Collection $collection
             * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
             */
            function sort_balance($collection, $column) {
                $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
            }

        }
        $this->addExportType('*/*/exportCsv', Mage::helper('rewardpointscsv')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('rewardpointscsv')->__('XML'));

        return parent::_prepareColumns();
    }

    /**
     * prepare mass action for this grid
     *
     * @return Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Grid
     */
    protected function _prepareMassaction() {
        $this->setMassactionIdField('rewardpointscsv_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpointscsv');
        return $this;
    }

    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getId()));
    }

    /**
     *
     * @param type $collection
     * @param type $column
     * @return type
     */
    public function filterCallback($collection, $column) {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else {
            if ($value['from'] != null) {
                if ($value['from'] == 0) {
                    $collection->getSelect()->where('customer_reward.point_balance IS NULL OR customer_reward.point_balance >=?', $value['from']);
                } else
                    $collection->getSelect()->where('customer_reward.point_balance >=?', $value['from']);
            }
            if ($value['to'] != null) {
                if ($value['from'] == 0) {
                    $collection->getSelect()->where('customer_reward.point_balance IS NULL OR customer_reward.point_balance <=?', $value['to']);
                } else
                    $collection->getSelect()->where('customer_reward.point_balance <=?', $value['to']);
            }
        }
    }

    /**
     * Sets sorting order by some column
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _setCollectionOrder($column) {
        if ($column->getOrderCallback()) {
            call_user_func($column->getOrderCallback(), $this->getCollection(), $column);

            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

}