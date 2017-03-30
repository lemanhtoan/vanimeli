<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_Field_Tiercommission extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * render config row
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $html  = '<tr id="row_' . $id . '">'
                . '<td class="label" colspan="3"></td>';
        $html .= '<td class="value"><a href="https://docs.google.com/viewer?url=https://www.magestore.com/media/productfile/a/f/affiliateplus_tier-commission-userguide.pdf" target="_bank">'.$element->getLabel().'</a>';
        $html .= '</td></tr>';
        return $html;
    }
}
