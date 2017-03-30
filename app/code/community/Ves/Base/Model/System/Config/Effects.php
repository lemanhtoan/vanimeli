<?php

class Ves_Base_Model_System_Config_Effects
{
	public function toOptionArray()
	{
		return array(
			array('value' => '',				'label' => Mage::helper('ves_base')->__(' ')),
			array('value' => 'fade',			'label' => Mage::helper('ves_base')->__('fade')),
			array('value' => 'backSlide',		'label' => Mage::helper('ves_base')->__('backSlide')),
			array('value' => 'goDown',			'label' => Mage::helper('ves_base')->__('goDown')),
			array('value' => 'fadeUp',			'label' => Mage::helper('ves_base')->__('fadeUp')),
		);
	}
}
