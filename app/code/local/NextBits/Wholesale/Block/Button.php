<?php 
class NextBits_Wholesale_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
		
		$url = Mage::helper("adminhtml")->getUrl("wholesale/adminhtml_wholesale/createwebsite/");

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Create Now!')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();
        return $html;
    }
}