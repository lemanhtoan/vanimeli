<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Rule_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('productlist_form', array('legend'=>Mage::helper('productlist')->__('Rule information')));
        $_data = Mage::registry('productlist_data');

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('productlist')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
            ));

        $fieldset->addField('identifier', 'text', array(
            'label'     => Mage::helper('productlist')->__('URL Key'),
            'class'     => 'validate-identifier',
            'required'  => true,
            'name'      => 'identifier',
            ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('productlist')->__('Status'),
            'name'      => 'status',
            'class'     => 'required-entry',
            'required'  => true,
            'values'    => array(
                array(
                  'value'     => 1,
                  'label'     => Mage::helper('productlist')->__('Enabled'),
                  ),
                array(
                  'value'     => 2,
                  'label'     => Mage::helper('productlist')->__('Disabled'),
                  ),
                ),
            ));

        if (!Mage::app()->isSingleStoreMode()){
            $fieldset->addField('stores', 'multiselect', array(
                'name' => 'stores[]',
                'label' => $this->__('Store View'),
                'required' => TRUE,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
                ));
        }else{
            if ($_data->getStore() && is_array($_data->getStore()))
            {
                $_stores = $_data->getStore();
                if (isset($_stores[0]) && $_stores[0] != '')
                {
                    $_stores = $_stores[0];
                }else{
                    $_stores = 0;
                }
                $_data->setStore($_stores);
            }

            $fieldset->addField('stores', 'hidden', array(
                'name' => 'stores[]'
                ));
        }

        $fieldset->addField('customer_group', 'multiselect', array(
            'name' => 'customer_group[]',
            'label' => Mage::helper('productlist')->__('Customer groups'),
            'title' => Mage::helper('productlist')->__('Customer groups'),
            'class'     => 'required-entry',
            'required' => true,
            'values' => Ves_ProductList_Block_Adminhtml_Rule_Edit_Tab_Form::getCustomerGroups(),
            ));

        if (!$_data->getStore())
        {
            $_data->setStore(0);
        }

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $fieldset->addField('date_from', 'date', array(
            'name'   => 'date_from',
            'label'  => $this->__('From Date'),
            'title'  => $this->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            ));

        $fieldset->addField('date_to', 'date', array(
            'name'   => 'date_to',
            'label'  => $this->__('To Date'),
            'title'  => $this->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            ));

        $fieldset->addField('thumbnail', 'image', array(
            'label'     => Mage::helper('productlist')->__('Thumbnail Image'),
            'name'      => 'thumbnail',
            ));

        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('productlist')->__('Image'),
            'name'      => 'image',
            ));

        $inputType = 'textarea';
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $inputType = 'editor';
        }

        $wysiwygConfig = Mage::getSingleton('productlist/system_config_wysiwyg')->getConfig(
          array('add_variables' => false));

        $fieldset->addField('description', $inputType, array(
            'label'     => Mage::helper('productlist')->__('Description'),
            'name'      => 'description',
            'style'     => 'width:600px;height:300px;',
            'wysiwyg'   => true,
            'config'  => $wysiwygConfig,
            ));

        $fieldset->addField('short_description', $inputType, array(
            'label'     => Mage::helper('productlist')->__('Short Description'),
            'name'      => 'short_description',
            'style'     => 'width:600px;height:250px;',
            'wysiwyg'   => true,
            'config'  => $wysiwygConfig,
            'note' => $this->__('User for Rule Widget'),
            ));

        if ( Mage::getSingleton('adminhtml/session')->getProductlistData()){
            $form->setValues(Mage::getSingleton('adminhtml/session')->getProductlistData());
            Mage::getSingleton('adminhtml/session')->setProductlistData(null);
        }elseif ( Mage::registry('productlist_data') ){
            $form->setValues(Mage::registry('productlist_data')->getData());
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