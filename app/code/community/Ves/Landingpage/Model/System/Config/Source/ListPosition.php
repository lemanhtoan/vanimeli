<?php

class Ves_ContentTab_Model_System_Config_Source_ListPosition
{
 public function toOptionArray()
    {
        return array(
        	array('value' => "root", 'label'=>Mage::helper('adminhtml')->__('Root')),
            array('value' => "content", 'label'=>Mage::helper('adminhtml')->__('Content')),
            array('value' => "left", 'label'=>Mage::helper('adminhtml')->__('Left')),
            array('value' => "right", 'label'=>Mage::helper('adminhtml')->__('Right')),
            array('value' => "top.menu", 'label'=>Mage::helper('adminhtml')->__('Top Menu')),
            array('value' => "product.info", 'label'=>Mage::helper('adminhtml')->__('Product Info')),
            array('value' => "top.links", 'label'=>Mage::helper('adminhtml')->__('Top Links')),
            array('value' => "my.account.wrapper", 'label'=>Mage::helper('adminhtml')->__('My Account Wrapper')),
            array('value' => "footer", 'label'=>Mage::helper('adminhtml')->__('Footer')),
            array('value' => "footer_links", 'label'=>Mage::helper('adminhtml')->__('Footer Links'))
        );
    }
}
