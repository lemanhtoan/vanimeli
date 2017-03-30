<?php
class Ves_BlockBuilder_Block_Adminhtml_Selector_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $form = new Varien_Data_Form();
        $model = Mage::registry("selector_data");
        $this->setForm($form);
        $fieldset = $form->addFieldset("selector_data", array("legend" => Mage::helper("ves_blockbuilder")->__("Item information")));

        if($model->getId()){
            $fieldset->addField("selector_id", "hidden", array(
                "label" => Mage::helper("ves_blockbuilder")->__("Id"),
                "name" => "selector_id",
            ));
        }

        $fieldset->addField("element_name", "text", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Element Name"),
            "class" => "form-control",
            "name" => "element_name",
            "note"  => Mage::helper("ves_blockbuilder")->__("Input selector element name. for example: Background Color")
        ));

        $fieldset->addField("element_tab", "select", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Element Tab"),
            "class" => "form-control",
            "name" => "element_tab",
            'options'   => array(
                'general' => Mage::helper("ves_blockbuilder")->__('General'),
                'elements' => Mage::helper('ves_blockbuilder')->__('Products'),
                'custom' => Mage::helper('ves_blockbuilder')->__('Custom')
            ),
        ));
        $fieldset->addField("element_group", "select", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Element Group"),
            "class" => "form-control",
            "name" => "element_group",
            'options'   => Mage::helper("ves_blockbuilder")->getSelectorGroups(),
        ));

        $fieldset->addField("element_type", "select", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Element Type"),
            "class" => "form-control",
            "name" => "element_type",
            'options'   => Mage::helper("ves_blockbuilder")->getSelectorTypes(),
        ));
        $fieldset->addField("element_selector", "textarea", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Css Selector"),
            "class" => "form-control",
            "name" => "element_selector",
            'note' => Mage::helper('ves_blockbuilder')->__('Input css selector which will been built in css file. For example: body #page')
        ));
        $fieldset->addField("element_attrs", "text", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Css Properties"),
            "class" => "form-control",
            "name" => "element_attrs",
            'note' => Mage::helper('ves_blockbuilder')->__('Input css property which will apply css code. For example: background-image, color, background-color, font-size, color, font, border-color,..<br/><strong>Css Selector { attribute: value; } </strong><br/>For example: <strong>body{ background-color: #FF0000; }</strong>')
        ));

        $fieldset->addField("template", "hidden", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Template"),
            "class" => "form-control",
            "name" => "template",
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('ves_blockbuilder')->__('Status'),
            'options'   => array(
                '1' => Mage::helper('cms')->__('Enabled'),
                '2' => Mage::helper('cms')->__('Disabled'),
            ),
            'name' => 'status',
            "class" => "form-control required-entry",
            "required" => true,
        ));

        $fieldset->addField("position", "text", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Position"),
            "class" => "form-control",
            "name" => "position",
        ));
  

        if (Mage::getSingleton("adminhtml/session")->getBlockData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getBlockData());
            Mage::getSingleton("adminhtml/session")->getBlockData(null);
        } elseif ($model) {
            $form->setValues($model->getData());
        }

        return parent::_prepareForm();
    }


    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return true;
    }
}
