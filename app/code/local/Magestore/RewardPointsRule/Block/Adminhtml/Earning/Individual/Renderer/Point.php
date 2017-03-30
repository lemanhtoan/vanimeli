<?php

class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Individual_Renderer_Point extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $_variablePattern = '/\\$([a-z0-9_]+)/i';

    public function _getValue(Varien_Object $row) {
        $format = ($this->getColumn()->getFormat()) ? $this->getColumn()->getFormat() : null;
        $defaultValue = Mage::helper('rewardpointsrule')->__('Empty');//$this->getColumn()->getDefault();
        $htmlId = 'editable_' . $row->getId();
        $saveUrl = $this->getUrl('*/*/ajaxSave');
        
        $data = parent::_getValue($row);
        $string = is_null($data) ? $defaultValue : (int) $data;
        $html = sprintf('<div id="%s" saveUrl="%s" entity="%s" oldValue="%s" class="editable" style="cursor: pointer;">%s</div>', $htmlId, $saveUrl, $row->getId(), $this->escapeHtml($string), $this->escapeHtml($string));
        return $html . "<script>if (bindInlineEdit) bindInlineEdit('{$htmlId}');</script>";
    }

}
