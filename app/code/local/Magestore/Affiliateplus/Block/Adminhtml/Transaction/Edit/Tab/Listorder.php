<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Edit_Tab_Listorder extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_orderIds;

	public function __construct()
	{
		parent::__construct();
		$this->setId('list_product_grid');
		$this->setDefaultSort('real_order_id');
		$this->setUseAjax(true);
		$this->setDefaultDir('DESC');
		if ($this->getTransaction() && $this->getTransaction()->getId()) {
			$this->setDefaultFilter(array('in_orders'=>1));
		}
	}


	protected function _addColumnFilterToCollection($column)
	{
		if ($column->getId() == 'in_orders') {
			$orderIds = $this->_getSelectedOrders();
			if (empty($orderIds)) {
				$orderIds = 0;
			}
			if ($column->getFilter()->getValue()) {
				$this->getCollection()->addFieldToFilter('entity_id', array('in'=>$orderIds));
			} else {
				if($orderIds) {
					$this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$orderIds));
				}
			}
		} else {
			parent::_addColumnFilterToCollection($column);
		}
		return $this;
	}

	protected function _getCollectionClass()
	{
		return 'sales/order_grid_collection';
	}

	protected function _prepareCollection()
	{
		$orderIds = $this->getOrderIds();
		$collection = Mage::getResourceModel($this->_getCollectionClass())
			// ->addAttributeToSelect('*')
			->addFieldToFilter('status',array('nin'=>array(Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::STATE_CLOSED)))
		;

		if(is_array($orderIds) && $orderIds){
			$collection->addFieldToFilter('entity_id',array('nin'=>$orderIds));
		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{

		$this->addColumn('in_orders', array(
			'header_css_class' => 'a-center',
			'type'      => 'radio',
			'html_name'      => 'aorders[]',
			'align'     => 'center',
			'index'     => 'entity_id',
			'width' => '50px',
			'values'    => $this->_getSelectedOrders(),
		));

		$this->addColumn('real_order_id', array(
			'header'=> Mage::helper('sales')->__('Order #'),
			'width' => '80px',
			'type'  => 'text',
			'index' => 'increment_id',
		));

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
				'index'     => 'store_id',
				'type'      => 'store',
				'store_view'=> true,
				'display_deleted' => true,
			));
		}

		$this->addColumn('created_at', array(
			'header' => Mage::helper('sales')->__('Purchased On'),
			'index' => 'created_at',
			'type' => 'datetime',
			'width' => '100px',
		));

		$this->addColumn('billing_name', array(
			'header' => Mage::helper('sales')->__('Bill to Name'),
			'index' => 'billing_name',
		));

		$this->addColumn('shipping_name', array(
			'header' => Mage::helper('sales')->__('Ship to Name'),
			'index' => 'shipping_name',
		));

		$this->addColumn('base_grand_total', array(
			'header' => Mage::helper('sales')->__('G.T. (Base)'),
			'index' => 'base_grand_total',
			'type'  => 'currency',
			'currency' => 'base_currency_code',
		));

		$this->addColumn('grand_total', array(
			'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
			'index' => 'grand_total',
			'type'  => 'currency',
			'currency' => 'order_currency_code',
		));

		$this->addColumn('status', array(
			'header' => Mage::helper('sales')->__('Status'),
			'index' => 'status',
			'type'  => 'options',
			'width' => '70px',
			'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
		));

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			$this->addColumn('action',
				array(
					'header'    => Mage::helper('sales')->__('Action'),
					'width'     => '50px',
					'type'      => 'action',
					'getter'     => 'getId',
					'actions'   => array(
						array(
							'caption' => Mage::helper('sales')->__('View'),
							'url'     => array('base'=>'adminhtml/sales_order/view'),
							'field'   => 'order_id'
						)
					),
					'filter'    => false,
					'sortable'  => false,
					'index'     => 'stores',
					'is_system' => true,
				));
		}


		return parent::_prepareColumns();
	}

	protected function _getSelectedOrders()
	{
		$orders = array($this->getTransaction()->getOrderId());
		return $orders;
	}

	public function getRowUrl($row)
	{
		return false;
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
		}
		return false;
	}

	public function getGridUrl()
	{
		return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/listordergrid', array('_current'=>true));
	}

	public function getTransaction()
	{
		return Mage::getModel('affiliateplus/transaction')
			->load($this->getRequest()->getParam('id'))
			;
	}

	public function getOrderIds()
	{
		if(is_null($this->_orderIds)){
			$this->_orderIds = array();
			$collection = Mage::getModel('affiliateplus/transaction')->getCollection()
				->addFieldToFilter('status',array('nin'=>array('3')));
			foreach ($collection as $transaction){
				$this->_orderIds[] = $transaction->getOrderId();
			}
		}
		return $this->_orderIds;
	}

}