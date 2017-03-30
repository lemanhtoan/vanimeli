<?php
class Ves_Base_Model_System_Config_ListButtonSize{
	public function toOptionArray(){
		return array(
			array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Standard')),
			array('value' => "small", 'label'=>Mage::helper('adminhtml')->__('Small')),
			array('value' => "medium", 'label'=>Mage::helper('adminhtml')->__('Medium')),
			array('value' => "tall", 'label'=>Mage::helper('adminhtml')->__('Tall')),
			);
	}
}