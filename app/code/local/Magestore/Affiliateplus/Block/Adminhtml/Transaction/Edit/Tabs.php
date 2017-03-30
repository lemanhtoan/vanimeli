<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('transaction_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('affiliateplus')->__('Transaction Information'));
	}

	protected function _beforeToHtml()
	{
		// Changed By Adam 27/08/2016: create transaction from existed order
		$transaction = $this->getTransaction();
		$id = $this->getRequest()->getParam('id');
		if(!$id)
		{
			$this->addTab('form_listorder', array(
				'label'     => Mage::helper('affiliateplus')->__('Select Order'),
				'title'     => Mage::helper('affiliateplus')->__('Select Order'),
				'class'     => 'ajax',
				'url'   => $this->getUrl('*/*/listorder',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
			));
		}
		// End code

		$this->addTab('form_section', array(
			'label'     => Mage::helper('affiliateplus')->__('Transaction Information'),
			'title'     => Mage::helper('affiliateplus')->__('Transaction Information'),
			'content'   => $this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit_tab_form')->toHtml(),
		));
		
		//event to add more tab
		Mage::dispatchEvent('affiliateplus_adminhtml_add_transaction_tab', array('form' => $this));
		
		return parent::_beforeToHtml();
	}
	
	public function addTabAfter($tabId, $tab, $afterTabId = '')
    {
        $this->addTab($tabId, $tab);
        $this->_tabs[$tabId]->setAfter($afterTabId);
    }
}