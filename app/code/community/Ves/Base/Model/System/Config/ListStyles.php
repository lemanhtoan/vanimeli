<?php
class Ves_Base_Model_System_Config_ListStyles{
	public function toOptionArray(){
		return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "primary", 'label'=>Mage::helper('adminhtml')->__('Primary')),
                  array('value' => "danger", 'label'=>Mage::helper('adminhtml')->__('Danger')),
                  array('value' => "info", 'label'=>Mage::helper('adminhtml')->__('Info')),
                  array('value' => "warning", 'label'=>Mage::helper('adminhtml')->__('Warning')),
                  array('value' => "highlighted", 'label'=>Mage::helper('adminhtml')->__('Highlighted')),
                  array('value' => "nopadding", 'label'=>Mage::helper('adminhtml')->__('Nopadding'))
                  );
	}
}