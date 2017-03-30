<?php
class NWT_Languagepack_Block_Adminhtml_Languagepack extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_languagepack';
    $this->_blockGroup = 'languagepack';
    $this->_headerText = Mage::helper('languagepack')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('languagepack')->__('Add Item');
    parent::__construct();
  }
}