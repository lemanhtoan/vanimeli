<?php
class Ves_Base_Model_System_Config_ListIconPosition{
	public function toOptionArray(){
		return array(
			array('value' => "left", 'label'=>Mage::helper('adminhtml')->__('Left')),
			array('value' => "right", 'label'=>Mage::helper('adminhtml')->__('Right')),
			array('value' => "top", 'label'=>Mage::helper('adminhtml')->__('Top')),
			array('value' => "bottom", 'label'=>Mage::helper('adminhtml')->__('Bottom'))
			);
	}
}