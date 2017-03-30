<?php
/*
 * Webtex Gift Cards
 *
 * (C) Webtex Software 2016
 *
 */
class Webtex_Giftcards_Adminhtml_Giftcards_LogcardsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Gift Cards Log'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards/log_cards');
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_logcards'));
        $this->renderLayout();
    }
    
    public function newAction()
    {
        Mage::getModel('giftcards/logger')->clearLogs();
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return true;
    }
}