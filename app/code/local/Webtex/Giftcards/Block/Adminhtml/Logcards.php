<?php

class Webtex_Giftcards_Block_Adminhtml_Logcards extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'giftcards';
        $this->_controller = 'adminhtml_logcards';
        $this->_headerText = Mage::helper('giftcards')->__('Gift Cards Log');
        $this->_addButtonLabel = Mage::helper('giftcards')->__('Clear Logs');
        parent::__construct();
    } 
}
