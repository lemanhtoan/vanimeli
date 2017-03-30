<?php
class Ves_Base_Model_System_Config_ListAlertType{
	public function toOptionArray(){
		return array(
                  array('value' => "alert-success", 'label'=>Mage::helper('adminhtml')->__('Alert Success')),
                  array('value' => "alert-info", 'label'=>Mage::helper('adminhtml')->__('Alert Info')),
                  array('value' => "alert-warning", 'label'=>Mage::helper('adminhtml')->__('Alert Warning')),
                  array('value' => "alert-danger", 'label'=>Mage::helper('adminhtml')->__('Alert Danger'))
                  );
	}
}