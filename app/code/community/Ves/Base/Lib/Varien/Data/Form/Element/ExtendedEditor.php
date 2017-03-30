<?php

class Ves_Base_Lib_Varien_Data_Form_Element_ExtendedEditor extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('hidden');
        $this->setExtType('hiddenfield');
    }

    public function getElementHtml()
    {
        $params = array();
        $params['value'] = $this->getEscapedValue();
        $params['model'] = $this->getModelData();
        $id = $this->getBlockId();
        
        $html = '<div id="'.$id.'" class="container-fluid editor_wrapper">';
        $html .= Mage::app()->getLayout()->createBlock("ves_base/adminhtml_builder_edit_editor", "blockbuilder.editor", $params)->toHtml();
        $html.= '</div>';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getLabelHtml($idSuffix = ''){
        if (!is_null($this->getLabel())) {
            $html = '<label for="'.$this->getHtmlId() . $idSuffix . '" style="'.$this->getLabelStyle().'">'.$this->getLabel()
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ).'</label>'."\n";
        }
        else {
            $html = '';
        }
        return $html;
    }
}