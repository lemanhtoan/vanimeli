<?php
class NextBits_CustomerActivation_Block_Adminhtml_Widget_Grid_Column_Renderer_Boolean
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $data = (bool) $this->_getValue($row);
        $value = $data ? 'Yes' : 'No';
        return $this->__($value);
    }
}
