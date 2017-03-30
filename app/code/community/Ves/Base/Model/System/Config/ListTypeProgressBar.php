<?php
class Ves_Base_Model_System_Config_ListTypeProgressBar{
	public function toOptionArray(){
		return array(
                  array('value' => "progress", 'label'=>Mage::helper('adminhtml')->__('Contextual alternatives')),
                  array('value' => "progress progress-striped", 'label'=>Mage::helper('adminhtml')->__('Striped')),
                  array('value' => "progress progress-striped active", 'label'=>Mage::helper('adminhtml')->__('Animated')),
                  array('value' => "stacked", 'label'=>Mage::helper('adminhtml')->__('Stacked'))
                  );
	}
}