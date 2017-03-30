<?php
class Ves_Base_Model_System_Config_SizeImage{
	public function toOptionArray(){
		return array(
                  array('value' => "s", 'label'=>Mage::helper('adminhtml')->__('Small square 75x75')),
                  array('value' => "q", 'label'=>Mage::helper('adminhtml')->__('Large square 150x150')),
                  array('value' => "t", 'label'=>Mage::helper('adminhtml')->__('Thumbnail, 100 on longest side')),
                  array('value' => "m", 'label'=>Mage::helper('adminhtml')->__('Small, 240 on longest side')),
                  array('value' => "n", 'label'=>Mage::helper('adminhtml')->__('Small, 320 on longest side')),
                  array('value' => "z", 'label'=>Mage::helper('adminhtml')->__('Medium 640, 640 on longest side')),
                  array('value' => "c", 'label'=>Mage::helper('adminhtml')->__('Medium 800, 800 on longest side†')),
                  array('value' => "b", 'label'=>Mage::helper('adminhtml')->__('Large, 1024 on longest side*')),
                  );
	}
}