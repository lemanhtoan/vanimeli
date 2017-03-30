<?php

class Magestore_Affiliateplus_Block_Account_Lifetimecustomer extends Mage_Core_Block_Template {

	/**
	 * get Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	public function _getHelper() {
		return Mage::helper('affiliateplus/config');
	}

	protected function _construct() {

		parent::_construct();
		$account = Mage::getSingleton('affiliateplus/session')->getAccount();
		$collection = Mage::getResourceModel('affiliateplus/tracking_collection')
			->addFieldToFilter('account_id', $account->getId());

		$this->setCollection($collection);
	}

	public function _prepareLayout() {
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('page/html_pager', 'lifetimecustomer_pager')
			->setTemplate('affiliateplus/html/pager.phtml')
			->setCollection($this->getCollection());
		$this->setChild('lifetimecustomer_pager', $pager);

		$grid = $this->getLayout()->createBlock('affiliateplus/grid', 'lifetimecustomer_grid');

		// prepare column
		$grid->addColumn('tracking_id', array(
			'header' => $this->__('No.'),
			'align' => 'left',
			'index' => 'tracking_id',
			'type' => 'number',
			'render' => 'getNoNumber',
			'searchable' => true,
		));

		$grid->addColumn('customer_name', array(
			'header' => $this->__('Customer Name'),
			'index' => 'customer_name',
			'type' => 'text',
			'searchable' => true,
			'filter_index'  =>  'if (main_table.customer_name IS NULL, "N/A", main_table.customer_name)',
			'render'  => 'getCustomerName'
		));

		$grid->addColumn('customer_email', array(
			'header' => $this->__('Customer Email'),
			'index' => 'customer_email',
			'type' => 'text',
			'searchable' => true,
		));

		$this->setChild('lifetimecustomer_grid', $grid);
		return $this;
	}

	public function getNoNumber($row) {
		return sprintf('#%d', $row->getId());
	}

	public function getCustomerName($row) {
//		return sprintf('#%d', $row->getId());
		if($row->getCustomerName()){
			return sprintf('%s', $row->getCustomerName());
		}  else {
			/*Changed By Adam 08/10/2014*/
//            return sprintf('%s', $row->getOrderItemNames());
			return sprintf('%s', 'N/A');
		}
	}




	/* Magic 28/11/2012 */

	public function getPaymentAction($row) {
		$confirmText = Mage::helper('adminhtml')->__('Are you sure?');
		$cancelurl=$this->getUrl('affiliateplus/index/cancelPayment', array('id' => $row->getPaymentId()));
		$action = '<a href="' . $this->getUrl('affiliateplus/index/viewPayment', array('id' => $row->getPaymentId())) . '">' . $this->__('View') . '</a>';

		$limitDays = intval($this->_getHelper()->getPaymentConfig('cancel_days'));
		$canCancel = $limitDays ? (time() - strtotime($row->getRequestTime()) <= $limitDays * 86400) : true;
		if ($row->getStatus() <= 2 && $canCancel)
			$action .=' | <a href="javascript:void(0)" onclick="cancelPayment'.$row->getPaymentId().'()">' . $this->__('Cancel') . '</a>
                <script type="text/javascript">
                    //<![CDATA[
                        function cancelPayment'.$row->getPaymentId().'(){
                            if (confirm(\''.$confirmText.'\')){
                                setLocation(\''.$cancelurl.'\');
                            }
                        }
                    //]]>
                </script>';
		return $action;
	}

	/* End */

	public function getPagerHtml() {
		return $this->getChildHtml('lifetimecustomer_pager');
	}

	public function getGridHtml() {
		return $this->getChildHtml('lifetimecustomer_grid');
	}

	protected function _toHtml() {
		$this->getChild('lifetimecustomer_grid')->setCollection($this->getCollection());
		return parent::_toHtml();
	}

}