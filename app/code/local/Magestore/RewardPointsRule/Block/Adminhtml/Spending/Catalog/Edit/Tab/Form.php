<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Spending Catalog Edit Tab Form Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     * 
     * @return type
     */
    protected function _prepareForm() {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('rule_data')) {
            $data = Mage::registry('rule_data')->getData();
        }
        if (!is_null(Mage::app()->getRequest()->getParam('group_id'))) {
            $data['customer_group_ids'] = Mage::app()->getRequest()->getParam('group_id');
        }
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $this->setForm($form);
        $fieldset = $form->addFieldset('general_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('General Information')));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Rule Name'),
            'title' => Mage::helper('rewardpointsrule')->__('Rule Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('description', 'editor', array(
            'name' => 'description',
            'label' => Mage::helper('rewardpointsrule')->__('Description'),
            'title' => Mage::helper('rewardpointsrule')->__('Description'),
            'style' => 'width:276px;height:100px;',
            'note' => Mage::helper('rewardpointsrule')->__('Rule description shown on Reward Information page'),
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Status'),
            'title' => Mage::helper('rewardpointsrule')->__('Status'),
            'name' => 'is_active',
            'values' => array(
                array(
                    'value' => '1',
                    'label' => Mage::helper('rewardpointsrule')->__('Active'),
                ),
                array(
                    'value' => '0',
                    'label' => Mage::helper('rewardpointsrule')->__('Inactive'),
                ),
            ),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name' => 'website_ids[]',
                'label' => Mage::helper('rewardpointsrule')->__('Websites'),
                'title' => Mage::helper('rewardpointsrule')->__('Websites'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        } else {
            $fieldset->addField('website_ids', 'hidden', array(
                'name' => 'website_ids[]',
                'value' => Mage::app()->getStore(true)->getWebsiteId()
            ));
            $data['website_ids'] = Mage::app()->getStore(true)->getWebsiteId();
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'label' => Mage::helper('rewardpointsrule')->__('Customer groups'),
            'title' => Mage::helper('rewardpointsrule')->__('Customer groups'),
            'name' => 'customer_group_ids',
            'required' => true,
            'values' => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gt' => 0))
                    ->load()
                    ->toOptionArray()
        ));
        
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => Mage::helper('rewardpointsrule')->__('Valid from'),
            'title' => Mage::helper('rewardpointsrule')->__('From date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso,
        ));

        $fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'label' => Mage::helper('rewardpointsrule')->__('Valid to'),
            'title' => Mage::helper('rewardpointsrule')->__('To date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso,
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('rewardpointsrule')->__('Priority'),
            'title' => Mage::helper('rewardpointsrule')->__('Priority'),
            'note' => Mage::helper('rewardpointsrule')->__('Rule with higher priority will be applied first.')
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
