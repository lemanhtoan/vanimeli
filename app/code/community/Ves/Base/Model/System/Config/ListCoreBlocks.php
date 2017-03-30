<?php
class Ves_Base_Model_System_Config_ListCoreBlocks{
	public function toOptionArray(){
		return array(
                  array('value' => "header", 'label'=>Mage::helper('adminhtml')->__('Header')),
                  array('value' => "breadcrumbs", 'label'=>Mage::helper('adminhtml')->__('Breadcrumbs')),
                  array('value' => "left_first", 'label'=>Mage::helper('adminhtml')->__('Left First')),
                  array('value' => "left", 'label'=>Mage::helper('adminhtml')->__('Left')),
                  array('value' => "right", 'label'=>Mage::helper('adminhtml')->__('Right')),
                  array('value' => "footer_before", 'label'=>Mage::helper('adminhtml')->__('Footer Before')),
                  array('value' => "footer", 'label'=>Mage::helper('adminhtml')->__('Footer')),
                  array('value' => "category.products", 'label'=>Mage::helper('adminhtml')->__('Main Content - Category Products(grid/list)')),
                  array('value' => "product.info", 'label'=>Mage::helper('adminhtml')->__('Main Content - Product Detail'))
                );
	}
}