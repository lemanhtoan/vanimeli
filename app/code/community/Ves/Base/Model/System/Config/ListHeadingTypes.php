<?php
class Ves_Base_Model_System_Config_ListHeadingTypes{
	public function toOptionArray(){
		return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "1", 'label'=>Mage::helper('adminhtml')->__('H1')),
                  array('value' => "2", 'label'=>Mage::helper('adminhtml')->__('H2')),
                  array('value' => "3", 'label'=>Mage::helper('adminhtml')->__('H3')),
                  array('value' => "4", 'label'=>Mage::helper('adminhtml')->__('H4')),
                  array('value' => "5", 'label'=>Mage::helper('adminhtml')->__('H5')),
                  array('value' => "6", 'label'=>Mage::helper('adminhtml')->__('H6'))
                  );
	}
}