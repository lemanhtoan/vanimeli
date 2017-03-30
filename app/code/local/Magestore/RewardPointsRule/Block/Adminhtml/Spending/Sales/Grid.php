<?php

class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Sales_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('salesSpendingRuleGrid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('rewardpointsrule/spending_sales_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as $offer) {
            $offer->setData('website_ids', explode(',', $offer->getData('website_ids')));
            $offer->setData('customer_group_ids', explode(',', $offer->getData('customer_group_ids')));
        }
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', array(
            'header' => Mage::helper('rewardpointsrule')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('rewardpointsrule')->__('Rule Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header' => Mage::helper('rewardpointsrule')->__('Website'),
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
            'header' => Mage::helper('rewardpointsrule')->__('Customer Groups'),
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
        /*
          $this->addColumn('description', array(
          'header'    => Mage::helper('rewardpointsrule')->__('Description'),
          'width'     => '150px',
          'index'     => 'description',
          ));
        
        $this->addColumn('points_spended', array(
            'header' => Mage::helper('rewardpointsrule')->__('Points'),
            'width' => '100px',
            'index' => 'points_spended',
        )); */

        $this->addColumn('from_date', array(
            'header' => Mage::helper('rewardpointsrule')->__('Created on'),
            'align' => 'left',
            'index' => 'from_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'date',
        ));

        $this->addColumn('to_date', array(
            'header' => Mage::helper('rewardpointsrule')->__('Expired on'),
            'align' => 'left',
            'index' => 'to_date',
            'format' => 'dd/MM/yyyy',
            'type' => 'date',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('rewardpointsrule')->__('Status'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('rewardpointsrule')->__('Active'),
                '0' => Mage::helper('rewardpointsrule')->__('Inactive'),
            ),
        ));

        $this->addColumn('sort_order', array(
            'header' => Mage::helper('rewardpointsrule')->__('Priority'),
            'align' => 'left',
            'width' => '60px',
            'index' => 'sort_order',
            'type' =>'number',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('rewardpointsrule')->__('Action'),
            'width' => '70px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('rewardpointsrule')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function filterCallback($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if (is_null(@$value))
            return;
        else
            $collection->addFieldToFilter($column->getIndex(), array('finset' => $value));
    }
    
    public function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('rewardpointsrule')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('rewardpointsrule')->__('Are you sure?')
        ));
        
        
        $this->getMassactionBlock()->addItem('change_status', array(
             'label'        => Mage::helper('rewardpointsrule')->__('Change status'),
             'url'          => $this->getUrl('*/*/massChangeStatus'),
             'additional'   => array(
                'visibility'    => array(
                     'name'     => 'status',
                     'type'     => 'select',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('rewardpointsrule')->__('Status'),
                     'values'   => Mage::getSingleton('rewardpoints/system_status')->getOptionArray(),
                 )
            )
        ));

        return $this;
    }
}
