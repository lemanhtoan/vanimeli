<?php
class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Tab_Cms extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $model = Mage::registry("block_data");
        $this->setForm($form);
        $fieldset = $form->addFieldset("page_layout", array("legend" => Mage::helper("ves_blockbuilder")->__("Page Layout")));

        $fieldset->addField('root_template', 'select', array(
            'name'     => 'root_template',
            'label'    => Mage::helper('cms')->__('Layout'),
            'required' => true,
            'values'   => Mage::getSingleton('page/source_layout')->toOptionArray()
        ));

        if (!$model->getId()) {
            $model->setRootTemplate(Mage::getSingleton('page/source_layout')->getDefaultValue());
        }

        $fieldset->addField('layout_update_xml', 'textarea', array(
            'name'      => 'layout_update_xml',
            'label'     => Mage::helper('cms')->__('Layout Update XML'),
            'style'     => 'width:90%;height:24em;'
        ));

        $designFieldset = $form->addFieldset('design_fieldset', array(
            'legend' => Mage::helper('cms')->__('Custom Design'),
            'class'  => 'fieldset-wide'
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        );

        $designFieldset->addField('custom_theme_from', 'date', array(
            'name'      => 'custom_theme_from',
            'label'     => Mage::helper('cms')->__('Custom Design From'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $dateFormatIso,
            'class'     => 'validate-date validate-date-range date-range-custom_theme-from'
        ));

        $designFieldset->addField('custom_theme_to', 'date', array(
            'name'      => 'custom_theme_to',
            'label'     => Mage::helper('cms')->__('Custom Design To'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $dateFormatIso,
            'class'     => 'validate-date validate-date-range date-range-custom_theme-to'
        ));

        $designFieldset->addField('custom_theme', 'select', array(
            'name'      => 'custom_theme',
            'label'     => Mage::helper('cms')->__('Custom Theme'),
            'values'    => Mage::getModel('core/design_source_design')->getAllOptions()
        ));


        $designFieldset->addField('custom_root_template', 'select', array(
            'name'      => 'custom_root_template',
            'label'     => Mage::helper('cms')->__('Custom Layout'),
            'values'    => Mage::getSingleton('page/source_layout')->toOptionArray(true)
        ));

        $designFieldset->addField('custom_layout_update_xml', 'textarea', array(
            'name'      => 'custom_layout_update_xml',
            'label'     => Mage::helper('cms')->__('Custom Layout Update XML'),
            'style'     => 'height:24em;'
        ));

        $meta_fieldset = $form->addFieldset("meta_data", array("legend" => Mage::helper("ves_blockbuilder")->__("Meta Data")));

        $meta_fieldset->addField("meta_keywords", "textarea", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Meta Keywords"),
            "name" => "meta_keywords",
            "class" => "form-control",
            'style'     => 'width:90%;',
            "required" => false
        ));

        $meta_fieldset->addField("meta_description", "textarea", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Meta Description"),
            "name" => "meta_description",
            "class" => "form-control",
            'style'     => 'width:90%;',
            "required" => false
        ));

        
    /*
        $fieldset->addField("params", "hidden", array(
            "label" => Mage::helper("ves_blockbuilder")->__("params"),
            "name" => "params",
        ));
    */
        

        if (Mage::getSingleton("adminhtml/session")->getBlockData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getBlockData());
            Mage::getSingleton("adminhtml/session")->getBlockData(null);
        } elseif ($model) {
            $form->setValues($model->getData());
        }

        return parent::_prepareForm();
    }

    static public function getCustomerGroups()
    {
        $data_array = array();
        $customer_groups = Mage::getModel('customer/group')->getCollection();;

        foreach ($customer_groups as $item_group) {
            $data_array[] = array('value' => $item_group->getCustomerGroupId(), 'label' => $item_group->getData('customer_group_code'));
        }
        return ($data_array);

    }
}
