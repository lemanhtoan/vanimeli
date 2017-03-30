<?php
class Ves_Base_Model_System_Config_ListSizeButton{
	public function toOptionArray(){
		return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "btn-lg", 'label'=>Mage::helper('adminhtml')->__('Large')),
                  array('value' => "btn-sm", 'label'=>Mage::helper('adminhtml')->__('Small')),
                  array('value' => "btn-xs", 'label'=>Mage::helper('adminhtml')->__('Extra small'))
                  );
	}
}