<?php
/*
 * Webtex Gift Cards
 *
 * Export Gift Cards Block
 *
 * (C) Webtex Software 2015
 */
class Webtex_Giftcards_Block_Adminhtml_Exportcards extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

		$this->_blockGroup = 'giftcards';
        $this->_controller = 'adminhtml';
		$this->_mode = 'exportcards';
		
        $this->_updateButton('save', 'label', Mage::helper('giftcards')->__('Export Gift Cards'));
        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    public function getHeaderText()
    {
        return Mage::helper('giftcards')->__('Export Gift Cards');
    }
}
