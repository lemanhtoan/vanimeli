<?php
class Magestore_Affiliateplus_Block_Adminhtml_Payment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_payment';
    $this->_blockGroup = 'affiliateplus';
    $this->_headerText = Mage::helper('affiliateplus')->__('Withdrawal Manager');
     
	parent::__construct();
	$this->_removeButton('add');
        $this->_addButton('add_withdrawal', array(
            'label'     => Mage::helper('affiliateplus')->__('Add Withdrawal'),
            'onclick'   => 'setLocation(\''.$this->getUrl('adminhtml/affiliateplus_payment/selectAccount').'\')',          //Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
            'class'     => 'add'
        ), 0, 100, 'header', 'header');
  }
}