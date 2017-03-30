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
 * @package     Magestore_RewardPointsReport
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints Report Customer Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customersreportGrid');
        $this->setDefaultSort('point_balance');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Customer_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        
        // Add Form Filter Data
        $filter = Mage::app()->getRequest()->getParam('filter', null);
        $data   = array();
        if (is_string($filter)) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
        }
        if (isset($data['report_from'])) {
            $collection->addFieldToFilter('main_table.created_time', array(
                'from'  => $data['report_from'],
                'date'  => true
            ));
        }
        if (isset($data['report_to'])) {
            $collection->addFieldToFilter('main_table.created_time', array(
                'to'    => $data['report_to'],
                'date'  => true
            ));
        }
        
        // Add Store Filter Data
        if ($this->_getStore()->getId()) {
            $collection->addFieldToFilter('main_table.store_id', $this->_getStore()->getId());
        }
        
        // Add SUM columns
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'customer_id'   => 'customer_id',
                'customer_email'=> 'customer_email',
                'earned_points' => 'SUM(IF(action_type = 2, 0, real_point))',
                'spent_points'  => '-SUM(IF(action_type = 2 OR (action_type = 0 AND point_amount < 0), point_amount, 0))',
                'base_discount' => 'base_discount',
                'order_base_amount' => 'order_base_amount',
            ))->group(array('main_table.customer_id', 'main_table.order_id'));
        
        // Fix for Refer Friend report
        $collection->addFieldToFilter('main_table.action', array('nin' => array(
            'referfriends', 'referfriends_cancel'
        )));
        
        /**
         * @var string $viewSelect Use to Storeage a View of Report
         */
        $viewSelect = clone $collection->getSelect();
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')), array(
                'customer_id', 'customer_email'
            ))
            ->columns(array(
                'earned_points'     => 'SUM(earned_points)',
                'spent_points'      => 'SUM(spent_points)',
                'total_discount'    => 'SUM(base_discount)',
                'order_grand_total' => 'SUM(order_base_amount)'
            ))->group('main_table.customer_id');
        $viewSelect = clone $collection->getSelect();
        
        // Change to Flat View - Can Filter and Search
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')))
            ->joinLeft(array('c' => $collection->getTable('rewardpoints/customer')),
                'main_table.customer_id = c.customer_id',
                array('point_balance', 'holding_balance')
            );
        
        $this->setCollection($collection);
        parent::_prepareCollection();
        
        // Process Row Total
        $viewSelect = clone $collection->getSelect();
        $rowData = Mage::getResourceModel('rewardpoints/transaction')->getReadConnection()->fetchRow(
            $viewSelect->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET)
            ->columns(array(
                'customer_email'    => 'COUNT(main_table.customer_id)',
                'point_balance'     => 'SUM(point_balance)',
                'holding_balance'   => 'SUM(holding_balance)',
                'earned_points'     => 'SUM(earned_points)',
                'spent_points'      => 'SUM(spent_points)',
                'total_discount'    => 'SUM(total_discount)',
                'order_grand_total' => 'SUM(order_grand_total)',
            ))
        );
        // hiep fix 181114
        $rowData['customer_id'] = 'Total';
        if ($this->_isExport) {
            $this->setCountTotals(true); 
            $this->setTotals(new Varien_Object($rowData));
        } else {
            $rowTotal = new Varien_Object($rowData);
            foreach ($this->_columns as $_column) {
                if (is_array($rowData) && isset($rowData[$_column->getIndex()])) {
                    $_column->setHeader($_column->getHeader() . '<br/>('
                        . $_column->getRowFieldExport($rowTotal) . ')');
                } else {
                    $_column->setHeader($_column->getHeader() . '<br/>&nbsp;');
                }
            }
        }
        
        return $this;
    }
    
    /**
     * prepare columns for this grid
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Customer_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('rewardpointsreport')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'customer_id',
            'type'      => 'text',
            'totals_label'  => Mage::helper('rewardpointsreport')->__('Total'),
            'filter_index'  => 'main_table.customer_id',
        ));

        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('rewardpointsreport')->__("Customer's Email"),
            'align'     => 'left',
            'index'     => 'customer_email',
            'filter_index'  => 'main_table.customer_email',
        ));

        $this->addColumn('point_balance', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Point Balance'),
            'index'     => 'point_balance',
            'type'      => 'number'
        ));

        $this->addColumn('holding_balance', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Held-back Balance'),
            'index'     => 'holding_balance',
            'type'      => 'number'
        ));
        
        $this->addColumn('earned_points', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Earned Points'),
            'index'     => 'earned_points',
            'type'      => 'number',
        ));
        
        $this->addColumn('spent_points', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Spent Points'),
            'index'     => 'spent_points',
            'type'      => 'number',
        ));
        
        $this->addColumn('total_discount', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Discount for Using Points'),
            'index'     => 'total_discount',
            'type'      => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
        ));
        
        $this->addColumn('order_grand_total', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Total Of Orders Using Points'),
            'index'     => 'order_grand_total',
            'type'      => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('rewardpointsreport')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('rewardpointsreport')->__('XML'));
        $this->addExportType('*/*/exportExcel', Mage::helper('adminhtml')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
    /**
     * get url for each row in grid
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $row->getCustomerId()));
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
