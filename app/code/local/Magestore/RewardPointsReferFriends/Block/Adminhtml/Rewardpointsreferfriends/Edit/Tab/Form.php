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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * prepare tab form's genaral information
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tab_Form
     */
    protected function _prepareForm() {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('offer_data')) {
            $data = Mage::registry('offer_data')->getData();
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('offer_');
        $this->setForm($form);
        $dataObj = new Varien_Object(array(
            'store_id' => '',
            'title_in_store',
            'description_referal_in_store',
            'description_invited_in_store',
        ));
        if (isset($data))
            $dataObj->addData($data);
        $data = $dataObj->getData();

        $storeId = $this->getRequest()->getParam('store');
        if ($storeId)
            $store = Mage::getModel('core/store')->load($storeId);
        else
            $store = Mage::app()->getStore();
        $inStore = $this->getRequest()->getParam('store');
        $defaultLabel = Mage::helper('rewardpointsreferfriends')->__('Use Default');
        $defaultTitle = Mage::helper('rewardpointsreferfriends')->__('-- Please Select --');
        $scopeLabel = Mage::helper('rewardpointsreferfriends')->__('STORE VIEW');
//        $form->addFieldSet('description_fieldset', array('legend' => Mage::helper('rewardpointsreferfriends')->__('Description')))->setRenderer(Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')->setTemplate('customerreward/offer/description.phtml'));

        $fieldset = $form->addFieldset('general_fieldset', array('legend' => Mage::helper('rewardpointsreferfriends')->__('General Information')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Title'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Title'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'title',
            'disabled' => ($inStore && !$data['title_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="title_default" name="title_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['title_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="title_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $scopeLabel . ']
          ' : '</td><td class="scope-label">
			[' . $scopeLabel . ']',
        ));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('hidden' => false, 'add_variables' => false, 'add_widgets' => false, 'add_images' => false, 'files_browser_window_url' => $this->getBaseUrl() . 'admin/cms_wysiwyg_images/index/'));
        $fieldset->addField('description_referal', 'editor', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Special Offer description shown for Referrals '),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Special Offer description shown for Referrals '),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'description_referal',
            'disabled' => ($inStore && !$data['description_referal_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="description_referal_default" name="description_referal_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['description_referal_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="description_referal_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $scopeLabel . ']
          ' : '</td><td class="scope-label">
			[' . $scopeLabel . ']',
            'wysiwyg' => true,
            'config' => $wysiwygConfig,
        ));
        $fieldset->addField('description_invited', 'editor', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Special Offer description shown for Referred Friends'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Special Offer description shown for Referred Friends'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'description_invited',
            'disabled' => ($inStore && !$data['description_invited_in_store']),
            'after_element_html' => $inStore ? '</td><td class="use-default">
			<input id="description_invited_default" name="description_invited_default" type="checkbox" value="1" class="checkbox config-inherit" ' . ($data['description_invited_in_store'] ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
			<label for="description_invited_default" class="inherit" title="' . $defaultTitle . '">' . $defaultLabel . '</label>
          </td><td class="scope-label">
			[' . $scopeLabel . ']
          ' : '</td><td class="scope-label">
			[' . $scopeLabel . ']',
            'wysiwyg' => true,
            'config' => $wysiwygConfig,
        ));
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name' => 'website_ids[]',
                'label' => Mage::helper('rewardpointsreferfriends')->__('Websites'),
                'title' => Mage::helper('rewardpointsreferfriends')->__('Websites'),
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

        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $found = false;

        foreach ($customerGroups as $group) {
            if ($group['value'] == 0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array(
                'value' => 0,
                'label' => Mage::helper('rewardpointsreferfriends')->__('NOT LOGGED IN'))
            );
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name' => 'customer_group_ids',
            'label' => Mage::helper('rewardpointsreferfriends')->__('Customer Groups'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Customer Groups'),
            'required' => true,
            'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
        ));
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => Mage::helper('rewardpointsreferfriends')->__('Validate from'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('From date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso,
        ));

        $fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'label' => Mage::helper('rewardpointsreferfriends')->__('Validate to'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('To date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso,
        ));
        if (!isset($data['status']) || $data['status'] == null) {
            $data['status'] = 2;
        }
        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Status'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => '1',
                    'label' => Mage::helper('rewardpointsreferfriends')->__('Active'),
                ),
                array(
                    'value' => '2',
                    'label' => Mage::helper('rewardpointsreferfriends')->__('Inactive'),
                ),
            ),
        ));

        $fieldset->addField('priority', 'text', array(
            'name' => 'priority',
            'label' => Mage::helper('rewardpointsreferfriends')->__('Priority'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Priority'),
            'class' => 'validate-zero-or-greater',
            'note' => Mage::helper('rewardpointsreferfriends')->__('Higher priority Offer will be applied first'),
        ));

        $form->setValues($data);
        return parent::_prepareForm();
    }

}
