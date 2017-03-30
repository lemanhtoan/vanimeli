<?php

class Magestore_Affiliateplus_Block_Adminhtml_Field_Affiliateplusplugininstallation extends Mage_Adminhtml_Block_System_Config_Form_Field
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
        $html .= '<td class="value"><a href="http://www.magestore.com/affiliateplus/productfile/index/view/fileid/202/" target="_bank">'.$element->getLabel().'</a>';
        $html .= '</td></tr>';
        return $html;
    }
}
