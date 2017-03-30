<?php
class Ves_Base_Model_System_Config_ListAnnotation{
	public function toOptionArray(){
		return array(
			array('value' => "inline", 'label'=>Mage::helper('adminhtml')->__('Inline')),
			array('value' => "buble", 'label'=>Mage::helper('adminhtml')->__('Bubble')),
			array('value' => "none", 'label'=>Mage::helper('adminhtml')->__('None')),
			);
	}
}