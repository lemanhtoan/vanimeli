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
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form {
  /**
   * prepare form action tab
   * @return type Form
   */
    protected function _prepareForm() {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $model = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')
                ->load($data['special_refer_id'])
                ->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('offer_data')) {
            $model = Mage::registry('offer_data');
            $data = Mage::registry('offer_data')->getData();
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('offer_');
        $this->setForm($form);
        $fieldset = $form->addFieldset('commission_fieldset', array('legend' => Mage::helper('rewardpointsreferfriends')->__('Commission for referral')));
        $options = array(
            array(
                'value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_TO_CUSTOMER,
                'label' => Mage::helper('rewardpointsreferfriends')->__('Give X points to Customers'),
            ),
            array(
                'value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_EVERY_MONEY,
                'label' => Mage::helper('rewardpointsreferfriends')->__('Give X points for every Y money spent'),
            ),
            array(
                'value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_ACTION_GIVE_POINT_EVERY_QTY,
                'label' => Mage::helper('rewardpointsreferfriends')->__('Give X points for every Y qty purchased'),
            ),
        );

        $fieldset->addField('commission_action', 'select', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Action'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Action'),
            'name' => 'commission_action',
            'values' => $options,
            'onchange' => 'toggleSimpleAction()',
        ));

        $fieldset->addField('commission_point', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Points (X)'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Value'),
            'name' => 'commission_point',
//            'note' => Mage::helper('rewardpointsreferfriends')->__('Points or Percent of Value referred'),
            'required' => true,
        ));
        $fieldset->addField('money_step', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Money step (Y)'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Money step (Y)'),
            'name' => 'money_step',
            'after_element_html' => '<strong>[' . Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE) . ']</strong>',
        ));

        $fieldset->addField('qty_step', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Quantity (Y)'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Quantity (Y)'),
            'name' => 'qty_step',
        ));
        $fieldset->addField('stop_rules_processing', 'select', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Stop processing further rules'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Stop processing further rules'),
            'name' => 'stop_rules_processing',
            'options' => array(
                '1' => Mage::helper('rewardpointsreferfriends')->__('Yes'),
                '0' => Mage::helper('rewardpointsreferfriends')->__('No'),
            ),
        ));
        $fieldset = $form->addFieldset('discount_fieldset', array('legend' => Mage::helper('rewardpointsreferfriends')->__('Discount for invited friends')));
        $fieldset->addField('discount_type', 'select', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Discount type'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Discount type'),
            'name' => 'discount_type',
            'values' => array(
                array(
                    'value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED,
                    'label' => Mage::helper('rewardpointsreferfriends')->__('Fixed Amount'),
                ),
                array(
                    'value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_PERCENT,
                    'label' => Mage::helper('rewardpointsreferfriends')->__('Percentage'),
                )
            ),
        ));

        $fieldset->addField('discount_value', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Discount value'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Discount value'),
            'name' => 'discount_value',
//            'note' => Mage::helper('rewardpointsreferfriends')->__('%s / Point(s) or Percent of Order Total', Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE)),
            'required' => true,
        ));

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newActionHtml/form/offer_actions_fieldset'));
		$fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('rewardpointsreferfriends')->__('Apply the rule only to cart items matching the following conditions (leave blank for all items)')))->setRenderer($renderer);
        $fieldset->addField('actions', 'text', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Apply To'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Apply To'),
            'name' => 'actions',
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));
        $form->setValues($data);
        return parent::_prepareForm();
    }

}
