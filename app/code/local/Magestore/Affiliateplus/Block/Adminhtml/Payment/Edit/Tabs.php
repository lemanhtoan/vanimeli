<?php

class Magestore_Affiliateplus_Block_Adminhtml_Payment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    /**
     * 
     */
    public function __construct() {
        parent::__construct();
        $this->setId('payment_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('affiliateplus')->__('Withdrawal Information'));
    }

    /**
     * 
     * @return type
     */
    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('affiliateplus')->__('Withdrawal Information'),
            'title' => Mage::helper('affiliateplus')->__('Withdrawal Information'),
            'content' => $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit_tab_form')->toHtml(),
        ));

        // Changed By Adam (05/08/2015): fix issue "Item (Magestore_Affiliateplus_Model_Payment_History) with the same id "0" already exist" when admin try to edit a complete withdrawal
        if ($paymentId = $this->getRequest()->getParam('id')) {
            $payment = Mage::getModel('affiliateplus/payment')->load($paymentId);
            if ($payment->getStatus() != 3) {
                $this->addTab('history_tab', array(
                    'label' => Mage::helper('affiliateplus')->__('Status History'),
                    'title' => Mage::helper('affiliateplus')->__('Status History'),
                    'content' => $this->getLayout()->createBlock('affiliateplus/adminhtml_payment_edit_tab_history')->toHtml(),
                ));
            }
        }

        return parent::_beforeToHtml();
    }

}
