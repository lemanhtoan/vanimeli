<?php
class Ves_Base_Block_Widget_Mediabox extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $editor = new Varien_Data_Form_Element_Editor($element->getData());

        // Prevent foreach error
        $editor->getConfig()->setPlugins(array());

        $editor->setId($element->getId());
        $editor->setForm($element->getForm());
        $editor->setValue(base64_decode($editor->getValue()));

        return parent::render($editor);
    }
}