<?php

class Magestore_Affiliatepluscoupon_Block_Adminhtml_Account_Renderer_Program extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row){
		if ($row->getProgramId()){
			return sprintf("<a href='%s' title='%s'>%s</a>"
				,$this->getUrl('adminhtml/affiliateplusprogram_program/edit',array('id' => $row->getProgramId())) 		//Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
				,$this->__('View Program')
				,$row->getProgramName()
			);
		}
		return $this->__($row->getProgramName());
	}
}