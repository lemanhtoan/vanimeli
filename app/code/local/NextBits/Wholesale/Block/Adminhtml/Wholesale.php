<?php
class NextBits_Wholesale_Block_Adminhtml_Wholesale extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_wholesale';
    $this->_blockGroup = 'wholesale';
    $this->_headerText = Mage::helper('wholesale')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('wholesale')->__('Add Item');
    parent::__construct();
  }
}