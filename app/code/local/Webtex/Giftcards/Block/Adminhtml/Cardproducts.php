<?php

class Webtex_Giftcards_Block_Adminhtml_Cardproducts extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'giftcards';
        $this->_controller = 'adminhtml_cardproducts';
        $this->_headerText = Mage::helper('giftcards')->__('Gift Card Products');
        parent::__construct();
        //$this->setTemplate('catalog/product.phtml');
    }
    
    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        
        $attributeSetId = Mage::getStoreConfig('giftcards/default/attribute_set');

        $this->_addButton('add_new', array(
            'label'   => Mage::helper('catalog')->__('Add Gift Card Product'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/catalog_product/new', array('type' => 'giftcards', 'set' => $attributeSetId))}')",
            'class'   => 'add'
        ));

        return parent::_prepareLayout();
    } 

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }

}
