<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('ruleId');
		$this->setDefaultSort('rule_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('productlist/rule')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('rule_id', array(
			'header'    => Mage::helper('productlist')->__('ID'),
			'align'     =>'right',
			'width'     => '50px',
			'index'     => 'rule_id',
			));

		$this->addColumn('title', array(
			'header'    => Mage::helper('productlist')->__('Rule Title'),
			'align'     =>'left',
			'index'     => 'title',
			));

		$this->addColumn('identifier', array(
			'header'    => Mage::helper('productlist')->__('Identifier'),
			'index'     => 'identifier',
			));

		$this->addColumn('date_from', array(
			'header' => Mage::helper('productlist')->__('Date Start'),
			'align' => 'center',
			'width' => '120',
			'index' => 'date_from',
			'type' => 'date',
			));

		$this->addColumn('date_to', array(
			'header' => Mage::helper('productlist')->__('Date Expire'),
			'align' => 'center',
			'width' => '120',
			'index' => 'date_to',
			'type' => 'date',
			));

		$this->addColumn('status', array(
			'header'    => Mage::helper('productlist')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
				1 => 'Enabled',
				2 => 'Disabled',
				),
			));

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('stores',
				array (
					'header' => Mage::helper('productlist')->__('Store view'),
					'index' => 'stores',
					'type' => 'store',
					'width' => '200px',
					'store_all' => true,
					'store_view' => true,
					'sortable' => false,
					'filter_condition_callback' => array (
						$this,'_filterStoreCondition' ) ));
		}

		$this->addColumn('action',
			array(
				'header'    =>  Mage::helper('productlist')->__('Action'),
				'width'     => '150',
				'type'      => 'action',
				'getter'    => 'getId',
				'actions'   => array(
					array(
						'caption'   => Mage::helper('productlist')->__('Manage Products'),
						'url'       => array('base'=> '*/ruleproducts/index'),
						'field'     => 'id'
						)
					),
				'filter'    => false,
				'sortable'  => false,
				'index'     => 'stores',
				'is_system' => true,
				));

		$this->addExportType('*/*/exportCsv', Mage::helper('productlist')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('productlist')->__('XML'));

		return parent::_prepareColumns();
	}

	/**
     * Helper function to add store filter condition
     *
     * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection Data collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column Column information to be filtered
     */
	protected function _filterStoreCondition($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
		$this->getCollection()->addStoreFilter($value);
	}

	/**
     * Helper function to do after load modifications
     *
     */
	protected function _afterLoadCollection()
	{
		$this->getCollection()->walk('afterLoad');
		parent::_afterLoadCollection();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('productlist');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('productlist')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('productlist')->__('Are you sure?')
			));

		$statuses = Mage::getSingleton('productlist/status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('productlist')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('productlist')->__('Status'),
					'values' => $statuses
					)
				)
			));
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
	}

}