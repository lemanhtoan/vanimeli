<?php

require "Mage/Adminhtml/controllers/Catalog/ProductController.php";

class Webtex_Giftcards_Adminhtml_Giftcards_CardproductsController extends Mage_Adminhtml_Catalog_ProductController
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addBreadcrumb($this->__('Gift Card Products List'), $this->__('Gift Card Products List'));
        $this->renderLayout();
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
