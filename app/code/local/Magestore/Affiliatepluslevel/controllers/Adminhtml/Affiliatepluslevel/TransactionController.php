<?php
/**
 * Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
 */
class Magestore_Affiliatepluslevel_Adminhtml_Affiliatepluslevel_TransactionController extends Mage_Adminhtml_Controller_Action {

    public function tierAction() {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliatepluslevel')->isPluginEnabled()) {
            return;
        }
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function tierGridAction() {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliatepluslevel')->isPluginEnabled()) {
            return;
        }
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('affiliateplus/transaction');
    }

}
