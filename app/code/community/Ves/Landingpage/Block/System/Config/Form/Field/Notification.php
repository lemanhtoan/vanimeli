<?php

class Ves_Landingpage_Block_System_Config_Form_Field_Notification extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $time = intval($element->getValue());
        $time = !empty($time)?$time:time();
        $url  = Mage::getBaseUrl('js');

        $output = '';
        
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        $timeUpdate = Mage::app()->getLocale()->date()->toString($format);
        
        return $timeUpdate. $output;
    }
}
?>