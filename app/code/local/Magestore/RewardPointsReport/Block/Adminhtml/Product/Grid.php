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
 * Rewardpoints Report Product Grid Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productsreportGrid');
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * prepare collection for block to display
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Product_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_item_collection');
        
        // Add Form Filter Data
        $filter = Mage::app()->getRequest()->getParam('filter', null);
        $data   = array();
        if (is_string($filter)) {
            $data = Mage::helper('adminhtml')->prepareFilterString($filter);
        }
        if (isset($data['report_from'])) {
            $collection->addFieldToFilter('main_table.created_at', array(
                'from'  => $data['report_from'],
                'date'  => true
            ));
        }
        if (isset($data['report_to'])) {
            $collection->addFieldToFilter('main_table.created_at', array(
                'to'    => $data['report_to'],
                'date'  => true
            ));
        }
        
        // Add Store Filter Data
        if ($this->_getStore()->getId()) {
            $collection->addFieldToFilter('main_table.store_id', $this->_getStore()->getId());
        }
        
        // Filter by main columns and add some columns
        $collection->addFieldToFilter('main_table.parent_item_id', array('null' => true));
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'product_id'    => 'main_table.product_id',
                'sku'           => 'main_table.sku',
                'name'          => 'main_table.name',
                'base_price'    => 'main_table.base_price',
                'qty_invoiced'  => new Zend_Db_Expr('main_table.qty_invoiced - main_table.qty_refunded'),
            ));
        // Join and calculate points
        $collection->getSelect()
            ->joinLeft(array('i' => $collection->getTable('sales/order_item')),
                'main_table.item_id = i.item_id OR main_table.item_id = i.parent_item_id',
                array(
                    'earned_points'     => 'SUM(i.rewardpoints_earn)',
                    'spent_points'      => 'SUM(i.rewardpoints_spent)',
                    'total_discount'    => 'SUM(i.rewardpoints_base_discount)'
                )
            )->group('main_table.item_id')
            ->having('qty_invoiced > 0')
            ->having('earned_points > 0 OR spent_points > 0');
        
        // Calculate SUM columns
        $viewSelect = clone $collection->getSelect();
        $collection->getSelect()->reset()
            ->from(array('main_table' => new Zend_Db_Expr('(' . $viewSelect->__toString() . ')')), array())
            ->columns(array(
                'product_id'    => 'product_id',
                'sku'           => 'main_table.sku',
                'name'          => 'main_table.name',
                'base_price'    => 'main_table.base_price',
                'qty_invoiced'  => 'SUM(qty_invoiced)',
                'earned_points' => 'SUM(earned_points)',
                'spent_points'  => 'SUM(spent_points)',
                'total_discount'=> 'SUM(total_discount)'
            ))->group('product_id');
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
                'name'          => 'COUNT(product_id)',
                'base_price'    => 'SUM(base_price * qty_invoiced) / SUM(qty_invoiced)',
                'qty_invoiced'  => 'SUM(qty_invoiced)',
                'earned_points' => 'SUM(earned_points)',
                'spent_points'  => 'SUM(spent_points)',
                'total_discount'=> 'SUM(total_discount)'
            ))
        );
        $rowData['product_id'] = 'Total';
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
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Product_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('rewardpointsreport')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'product_id',
            'type'      => 'text',
            'totals_label'  => Mage::helper('rewardpointsreport')->__('Total'),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('rewardpointsreport')->__('SKU'),
            'align'     => 'left',
            'index'     => 'sku',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Product Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('base_price', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Price'),
            'index'     => 'base_price',
            'type'      => 'price',
            'currency_code' => $this->_getStore()->getBaseCurrency()->getCode(),
        ));
        
        $this->addColumn('qty_invoiced', array(
            'header'    => Mage::helper('rewardpointsreport')->__('Qty'),
            'index'     => 'qty_invoiced',
            'type'      => 'number',
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
        return $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getProductId()));
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
