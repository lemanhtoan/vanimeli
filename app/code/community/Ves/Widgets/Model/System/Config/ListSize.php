<?php
class Ves_Widgets_Model_System_Config_ListSize {
	public function toOptionArray(){
		return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "verysmall", 'label'=>Mage::helper('adminhtml')->__('Very Small')),
                  array('value' => "small", 'label'=>Mage::helper('adminhtml')->__('Small')),
                  array('value' => "medium", 'label'=>Mage::helper('adminhtml')->__('Medium')),
                  array('value' => "big", 'label'=>Mage::helper('adminhtml')->__('Big')),
                  array('value' => "extramedium", 'label'=>Mage::helper('adminhtml')->__('Extra Medium')),
                  array('value' => "extrabig", 'label'=>Mage::helper('adminhtml')->__('Extra Big'))
            );
	}
}