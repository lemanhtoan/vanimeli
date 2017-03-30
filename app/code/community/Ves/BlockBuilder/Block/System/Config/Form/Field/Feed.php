<?php

class Ves_BlockBuilder_Block_System_Config_Form_Field_Feed extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('Pages Builder Plugins Market', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }

}

?>