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
class Ves_ProductList_Block_Adminhtml_Rule_ExportGrid extends Mage_Adminhtml_Block_Widget_Grid
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
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		foreach ($collection as $_rule) {
			$results = $query = '';
			$query = 'SELECT store_id FROM ' . $resource->getTableName('productlist/rule_store').' WHERE rule_id = '.$_rule->getRuleId();
			$results = $readConnection->fetchCol($query);
			$_rule->setData('stores', implode('-', $results));

			$query = 'SELECT customer_group_id FROM ' . $resource->getTableName('productlist/rule_customer').' WHERE rule_id = '.$_rule->getRuleId();
			$results = $readConnection->fetchCol($query);
			$_rule->setData('customer_group', implode('-', $results));

			//$product_list_rule = $_rule->getData('product_list_rule');
			//$rule_condition = $product_list_rule->getData();
			//if($rule_condition){
				//$_rule->setData('product_list_rule',serialize($rule_condition));
			//}
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('rule_id', array(
			'header'    => Mage::helper('productlist')->__('rule_id'),
			'index'     => 'rule_id',
			));

		$this->addColumn('identifier', array(
			'header'    => Mage::helper('productlist')->__('identifier'),
			'index'     => 'identifier',
			));

		$this->addColumn('title', array(
			'header'    => Mage::helper('productlist')->__('title'),
			'index'     => 'title',
			));

		$this->addColumn('thumbnail', array(
			'header'    => Mage::helper('productlist')->__('thumbnail'),
			'index'     => 'thumbnail',
			));

		$this->addColumn('image', array(
			'header'    => Mage::helper('productlist')->__('image'),
			'index'     => 'image',
			));

		$this->addColumn('description', array(
			'header'    => Mage::helper('productlist')->__('description'),
			'index'     => 'description',
			));

		$this->addColumn('status', array(
			'header'    => Mage::helper('productlist')->__('status'),
			'index'     => 'status',
			));

		$this->addColumn('date_from', array(
			'header'    => Mage::helper('productlist')->__('date_from'),
			'index'     => 'date_from',
			));

		$this->addColumn('date_to', array(
			'header'    => Mage::helper('productlist')->__('date_to'),
			'index'     => 'date_to',
			));

		$this->addColumn('product_number', array(
			'header'    => Mage::helper('productlist')->__('product_number'),
			'index'     => 'product_number',
			));

		$this->addColumn('product_order', array(
			'header'    => Mage::helper('productlist')->__('product_order'),
			'index'     => 'product_order',
			));

		$this->addColumn('product_direction', array(
			'header'    => Mage::helper('productlist')->__('product_direction'),
			'index'     => 'product_direction',
			));

		$this->addColumn('created', array(
			'header'    => Mage::helper('productlist')->__('created'),
			'index'     => 'created',
			));

		$this->addColumn('modified', array(
			'header'    => Mage::helper('productlist')->__('modified'),
			'index'     => 'modified',
			));

		$this->addColumn('product_list_rule', array(
			'header'    => Mage::helper('productlist')->__('product_list_rule'),
			'index'     => 'product_list_rule',
			));

		$this->addColumn('show_outofstock', array(
			'header'    => Mage::helper('productlist')->__('show_outofstock'),
			'index'     => 'show_outofstock',
			));
		$this->addColumn('custom_design_from', array(
			'header'    => Mage::helper('productlist')->__('custom_design_from'),
			'index'     => 'custom_design_from',
			));

		$this->addColumn('custom_design_to', array(
			'header'    => Mage::helper('productlist')->__('custom_design_to'),
			'index'     => 'custom_design_to',
			));

		$this->addColumn('page_layout', array(
			'header'    => Mage::helper('productlist')->__('page_layout'),
			'index'     => 'page_layout',
			));

		$this->addColumn('custom_layout_update', array(
			'header'    => Mage::helper('productlist')->__('custom_layout_update'),
			'index'     => 'custom_layout_update',
			));

		$this->addColumn('options', array(
			'header'    => Mage::helper('productlist')->__('options'),
			'index'     => 'options',
			));

		$this->addColumn('page_title', array(
			'header'    => Mage::helper('productlist')->__('page_title'),
			'index'     => 'page_title',
			));

		$this->addColumn('meta_keywords', array(
			'header'    => Mage::helper('productlist')->__('meta_keywords'),
			'index'     => 'meta_keywords',
			));

		$this->addColumn('meta_description', array(
			'header'    => Mage::helper('productlist')->__('meta_description'),
			'index'     => 'meta_description',
			));

		$this->addColumn('stores', array(
			'header'    => Mage::helper('productlist')->__('stores'),
			'index'     => 'stores',
			));

		$this->addColumn('customer_group', array(
			'header'    => Mage::helper('productlist')->__('customer_group'),
			'index'     => 'customer_group',
			));

		return parent::_prepareColumns();
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
		return $this;
	}
}