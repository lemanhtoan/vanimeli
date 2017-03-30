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
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Spending Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Spending_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's information
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Spending_Edit_Tab_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form();

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('rate_data')) {
            $data = Mage::registry('rate_data')->getData();
        }
        $fieldset = $form->addFieldset('rewardpoints_form', array(
            'legend' => Mage::helper('rewardpoints')->__('Rate Information')
        ));

        $dataObj = new Varien_Object($data);

        $data = $dataObj->getData();

        $fieldset->addField('points', 'text', array(
            'label' => Mage::helper('rewardpoints')->__('Spending Point(s)'),
            'title' => Mage::helper('rewardpoints')->__('Spending Point(s)'),
            'required' => true,
            'name' => 'points',
        ));

        $fieldset->addField('money', 'text', array(
            'name' => 'money',
            'label' => Mage::helper('rewardpoints')->__('Discount received'),
            'title' => Mage::helper('rewardpoints')->__('Discount received'),
            'required' => true,
            'after_element_html' => '<strong>[' . Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE) . ']</strong>',
            'note' => Mage::helper('rewardpoints')->__('The equivalent value of points'),
        ));
        
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('rewardpoints')->__('Status'),
            'title' => Mage::helper('rewardpoints')->__('Status'),
            'required' => true,
            'name' => 'status',
            'options' => Mage::getSingleton('rewardpoints/system_status')->getOptionArray(),
        ));
        
        //Hai.Tran 12/11/2013 fix gioi han spend points
        $fieldset->addField('max_price_spended_type', 'select', array(
            'label' => Mage::helper('rewardpoints')->__('Limit spending points based on'),
            'title' => Mage::helper('rewardpoints')->__('Limit spending points based on'),
            'name' => 'max_price_spended_type',
            'options' => array(
                'none' => Mage::helper('rewardpoints')->__('None'),
                //'by_point' => Mage::helper('rewardpointsrule')->__('Points'),
                'by_price' => Mage::helper('rewardpoints')->__('A fixed amount of Total Order Value'),
                'by_percent' => Mage::helper('rewardpoints')->__('A percentage of Total Order Value'),
            ),
            'onchange' => 'toggleMaxPriceSpend()',
            'note' => Mage::helper('rewardpoints')->__('Select the type to limit spending points')
        ));
        $fieldset->addField('max_price_spended_value', 'text', array(
            'label' => Mage::helper('rewardpoints')->__('Limit value allowed to spend points at'),
            'title' => Mage::helper('rewardpoints')->__('Limit value allowed to spend points at'),
            'name' => 'max_price_spended_value',
            'note' => Mage::helper('rewardpoints')->__('If empty or zero, there is no limitation.')
        ));
        //End Hai.Tran 12/11/2013

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name' => 'website_ids[]',
                'label' => Mage::helper('rewardpoints')->__('Websites'),
                'title' => Mage::helper('rewardpoints')->__('Websites'),
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
            'label' => Mage::helper('rewardpoints')->__('Customer groups'),
            'title' => Mage::helper('rewardpoints')->__('Customer groups'),
            'name' => 'customer_group_ids',
            'required' => true,
            'values' => Mage::getResourceModel('customer/group_collection')
                    ->addFieldToFilter('customer_group_id', array('gt' => 0))
                    ->load()
                    ->toOptionArray()
        ));
        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('rewardpoints')->__('Priority'),
            'label' => Mage::helper('rewardpoints')->__('Priority'),
            'required' => false,
            'name' => 'sort_order',
            'note' => Mage::helper('rewardpoints')->__('Higher priority Rate will be applied first')
        ));

        $form->setValues($data);
        $this->setForm($form);

        Mage::dispatchEvent('rewardpoints_adminhtml_spending_rate_form', array(
            'tab_form' => $this,
            'form' => $form,
            'data' => $dataObj,
        ));
        return parent::_prepareForm();
    }

}
