<?php
/**
 * Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
 */
class Magestore_Affiliatepluscoupon_Adminhtml_Affiliatepluscoupon_LinkController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/transaction')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Link Transactions Manager'), Mage::helper('adminhtml')->__('Link Transaction Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Transactions from Link'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->getResponse()->setBody($this->getLayout()->createBlock('affiliatepluscoupon/adminhtml_link_grid')->toHtml());
    }
    
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('affiliateplus/transaction/link');
    }
}