<?php
class Ves_Base_Model_System_Config_ListButtonType{
      public function toOptionArray(){
            return array(
                  array('value' => "btn-default", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "btn-primary", 'label'=>Mage::helper('adminhtml')->__('Primary')),
                  array('value' => "btn-success", 'label'=>Mage::helper('adminhtml')->__('Success')),
                  array('value' => "btn-info", 'label'=>Mage::helper('adminhtml')->__('Info')),
                  array('value' => "btn-warning", 'label'=>Mage::helper('adminhtml')->__('Warning')),
                  array('value' => "btn-danger", 'label'=>Mage::helper('adminhtml')->__('Danger')),
                  array('value' => "btn-link", 'label'=>Mage::helper('adminhtml')->__('Link')),
                  );
      }
}