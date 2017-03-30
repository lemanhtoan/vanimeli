<?php
class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $model = Mage::registry("block_data");
        $this->setForm($form);
        $fieldset = $form->addFieldset("block_data", array("legend" => Mage::helper("ves_blockbuilder")->__("Item information")));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        if($model->getId()){
            $fieldset->addField("block_id", "hidden", array(
                "label" => Mage::helper("ves_blockbuilder")->__("Id"),
                "name" => "block_id",
            ));
        }
        $builder_type_label = "Block";

        if( 1 ==  Mage::registry('is_pagebuilder')){
            $builder_type_label = "Page";
        }

        if( 1 ==  Mage::registry('is_productbuilder')){
            $builder_type_label = "Product";
        } 

        $fieldset->addField("title", "text", array(
                "label" => Mage::helper("ves_blockbuilder")->__($builder_type_label." Title"),
                "name" => "title",
                "class" => "form-control required-entry",
                "required" => true
            ));

        $fieldset->addField("alias", "text", array(
                "label" => Mage::helper("ves_blockbuilder")->__("URL Key"),
                "name" => "alias",
                "class" => "form-control required-entry",
                "required" => true,
            ));
        
        $fieldset->addField("shortcode", "hidden", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Shortcode"),
            "name" => "shortcode",
            "class" => "form-control",
            "readonly" => false,
            "onclick" => 'jQuery(this).select();'
        ));

        if( 1 ==  Mage::registry('is_pagebuilder')){
            /**
             * Check is single store mode
             */
            if (!Mage::app()->isSingleStoreMode()) {
                $field = $fieldset->addField('store_id', 'multiselect', array(
                    'name' => 'stores[]',
                    'label' => Mage::helper('ves_blockbuilder')->__('Store View'),
                    'title' => Mage::helper('ves_blockbuilder')->__('Store View'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')
                                 ->getStoreValuesForForm(false, true),
                ));
                $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
                $field->setRenderer($renderer);
            }
            else {
                $fieldset->addField('store_id', 'hidden', array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                ));
                $model->setStoreId(Mage::app()->getStore(true)->getId());
            }
        } 
        
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

        $fieldset->addField("show_from", "date", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Display ".$builder_type_label." From Date"),
            "class" => "form-control float-left-element",
            "name" => "show_from",
            "image"  => $this->getSkinUrl("images/grid-cal.gif"),
            "input_format" => $dateFormatIso,
            "format"       => $dateFormatIso
        ));

        $fieldset->addField("show_to", "date", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Display ".$builder_type_label." To Date"),
            "class" => "form-control float-left-element",
            "name" => "show_to",
            "image"  => $this->getSkinUrl("images/grid-cal.gif"),
            "input_format" => $dateFormatIso,
            "format"       => $dateFormatIso
        ));

        $fieldset->addField('customer_group', 'multiselect', array(
                'name' => 'customer_group[]',
                'label' => Mage::helper('ves_blockbuilder')->__('Enable '.$builder_type_label.' for certain customer groups'),
                'title' => Mage::helper('ves_blockbuilder')->__('Enable '.$builder_type_label.' for certain customer groups'),
                'required' => false,
                'values' => Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Tab_Form::getCustomerGroups(),
            ));


        $fieldset->addField("position", "text", array(
            "label" => Mage::helper("ves_blockbuilder")->__("Position"),
            "class" => "form-control",
            "name" => "position",
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
