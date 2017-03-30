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
 * Rewardpoints Report Earned Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Earned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('earnedsreportGrid');
        $this->setDefaultSort('period');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->_prepareDateTypeReport();
    }
    
    /**
     * Prepare date range type to report
     * 
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Earned_Grid
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
     * get Earning Action to show On Report
     * 
     * @return array
     */
    protected function _getEarningActions()
    {
        if (!$this->hasData('earn_actions')) {
            $earnAction = array();
            if (Mage::helper('rewardpoints/core')->isModuleEnabled('Magestore_RewardPointsBehavior')) {
                $earnAction = array(
                    'registed'      => Mage::helper('rewardpointsreport')->__('Sign-up'),
                    'newsletter'    => Mage::helper('rewardpointsreport')->__('Newsletter'),
                    'birthday'      => Mage::helper('rewardpointsreport')->__('Birthday'),
                    'review'        => Mage::helper('rewardpointsreport')->__('Review'),
                    'tag'           => Mage::helper('rewardpointsreport')->__('Product Tag'),
                    'fblike'        => Mage::helper('rewardpointsreport')->__('Facebook Like'),
                    // 'tweeting'      => Mage::helper('rewardpointsreport')->__('Tweet'),
                );
            }
            $this->setData('earn_actions', $earnAction);
            Mage::dispatchEvent('rewardpointsreport_block_get_earning_actions', array('block' => $this));
        }
        return $this->getData('earn_actions');
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Earned_Grid
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
        
        // Prepare collection columns
        $actions = array_keys($this->_getEarningActions());
        $earnedActions = implode("','", array_merge($actions, array(
            'admin', 'earning_invoice'
        )));
        $columns = array(
            'period'    => $this->getPeriodFormat(),
            'total'     => "SUM(IF(action_type = 2, 0, real_point))",
            'sales'     => "SUM(IF(action = 'earning_invoice', real_point, 0))",
            'admin'     => "SUM(IF(action = 'admin', real_point, 0))",
            'other'     => "SUM(IF(action_type = 1 AND action NOT IN ('$earnedActions'), real_point, 0))",
        );
        foreach ($actions as $action) {
            $columns[$action] = "SUM(IF(action = '$action', real_point, 0))";
        }
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns)
            ->group($this->getPeriodFormat());
        
        // Change to Flat View - Can Filter and Search
        $viewSelect = clone $collection->getSelect();
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')));
        
        $this->setCollection($collection);
        parent::_prepareCollection();
        
        // Process Row Total
        $viewSelect = clone $collection->getSelect();
        unset($columns['period']);
        foreach ($columns as $colname => &$coldata) {
            $coldata = 'SUM(' . $colname . ')';
        }
        $rowData = Mage::getResourceModel('rewardpoints/transaction')->getReadConnection()->fetchRow(
            $viewSelect->reset(Zend_Db_Select::COLUMNS)
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET)
            ->columns($columns)
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
            'type'      => 'date',
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('rewardpointsreport')->__('Total'),
            'align'     => 'right',
        ));
        
        $this->addColumn('total', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Total Earned Points'),
            'index'     => 'total',
            'type'      => 'number',
        ));
        
        $this->addColumn('sales', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Purchase Order'),
            'index'     => 'sales',
            'type'      => 'number',
        ));
        
        $this->addColumn('admin', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Admin'),
            'index'     => 'admin',
            'type'      => 'number',
        ));
        
        foreach ($this->_getEarningActions() as $action => $label) {
            $this->addColumn($action, array(
                'header'    => $label,
                'index'     => $action,
                'type'      => 'number',
            ));
        }
        
        $this->addColumn('other', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Others'),
            'index'     => 'other',
            'type'      => 'number',
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
