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
 * Rewardpoints Report Order Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ordersreportGrid');
        $this->setDefaultSort('period');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->_prepareDateTypeReport();
    }
    
    /**
     * Prepare date range type to report
     * 
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Order_Grid
     */
    public function _prepareDateTypeReport()
    {
        $filter = Mage::app()->getRequest()->getParam('filter', null);
        if (is_string($filter)) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
            if (isset($data['report_period'])) {
                $this->setPeriodType($data['report_period']);
            }
        }
        switch ($this->getPeriodType()) {
            case 'month':
                $this->setPeriodFormat("DATE_FORMAT(created_time, '%Y-%m')");
                break;
            case 'year':
                $this->setPeriodFormat("DATE_FORMAT(created_time, '%Y')");
                break;
            default :
                $this->setPeriodFormat("DATE_FORMAT(created_time, '%Y-%m-%d')");
                break;
        }
        return $this;
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Order_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        
        // Add Store Filter Data
        if ($this->_getStore()->getId()) {
            $collection->addFieldToFilter('main_table.store_id', $this->_getStore()->getId());
        }
        
        // Remove duplicate order data
        $collection->addFieldToFilter('order_id', array('notnull' => true))
            ->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'period'    => $this->getPeriodFormat(),
                'order_id'  => 'order_id',
                'earned_points' => 'SUM(IF(action_type = 2, 0, real_point))',
                'spent_points'  => '-SUM(IF(action_type = 2, point_amount, 0))',
                'base_discount' => 'SUM(IF((action_type = 2 AND point_amount < 0 ), -1 * base_discount  , IF(action_type = 1 , 0 , base_discount)))', // hiepdd fix 
                'order_base_amount' => 'SUM(IF((action_type=2 AND point_amount <0 ), order_base_amount , IF(action_type = 1 , 0 , -1 * order_base_amount)))',// hiepdd fix
            ))->group('order_id');
        $viewSelect = clone $collection->getSelect();
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')), array())
            ->columns(array(
                'period'        => 'period',
                'total_order'   => 'SUM(IF((earned_points = 0 AND spent_points = 0 AND base_discount = 0 AND order_base_amount= 0),0,1))',// hiepdd fix  
                'earned_points' => 'SUM(earned_points)',
                'spent_points'  => 'SUM(spent_points)',
                'total_discount'=> 'SUM(base_discount)',
                'order_grand_total' => 'SUM(order_base_amount)',
            ))->group('period');
        $viewSelect = clone $collection->getSelect();
        
        // Change to Flat View - Can Filter and Search
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')));
        
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
                'total_order'   => 'SUM(total_order)',
                'earned_points' => 'SUM(earned_points)',
                'spent_points'  => 'SUM(spent_points)',
                'total_discount'=> 'SUM(total_discount)',
                'order_grand_total' => 'SUM(order_grand_total)',
            ))
        );
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
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Order_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Time'),
            'index'     => 'period',
            'type'      => 'datetime',
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('rewardpointsreport')->__('Total'),
            'align'     => 'right',
        ));
        
        $this->addColumn('total_order', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Number of Orders'),
            'index'     => 'total_order',
            'type'      => 'number',
        ));
        
        $this->addColumn('earned_points', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Total Earned Points'),
            'index'     => 'earned_points',
            'type'      => 'number',
        ));
        
        $this->addColumn('spent_points', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Total Spent Points'),
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
        return '';
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
