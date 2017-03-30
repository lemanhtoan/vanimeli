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
 * RewardPointsRule Spending Catalog Conditions Actions Tab Edit Form Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit_Tab_Actions
     */
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('rule_data')) {
            $data = Mage::registry('rule_data')->getData();
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $this->setForm($form);
        $fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Update prices using the following information')));

        $fieldset->addField('discount_style', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Discount Type'),
            'title' => Mage::helper('rewardpointsrule')->__('Discount Type'),
            'name' => 'discount_style',
            'options' => array(
                'by_fixed' => Mage::helper('rewardpointsrule')->__('By a fixed amount'),
                'to_fixed' => Mage::helper('rewardpointsrule')->__('To a fixed amount'),
                'by_percent' => Mage::helper('rewardpointsrule')->__('By a percentage of the original price'),
                'to_percent' => Mage::helper('rewardpointsrule')->__('To a percentage of the original price'),
            ),
            'note'=> Mage::helper('rewardpointsrule')->__('To use as the minimum spending points by this rule. The number of spending points must be a multiple of the configured number.
Select the type to calculate discount received for every X points (in tab Conditions)')
        ));

        $fieldset->addField('discount_amount', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Discount amount'),
            'title' => Mage::helper('rewardpointsrule')->__('Discount amount'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'discount_amount',
            'note'=> Mage::helper('rewardpointsrule')->__('Discount received for every X points (in tab Conditions)')
        ));

        $fieldset->addField('uses_per_product', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Uses Allowed Per Product'),
            'title' => Mage::helper('rewardpointsrule')->__('Uses Allowed Per Product'),
            'name' => 'uses_per_product',
            'note' => Mage::helper('rewardpointsrule')->__('Set the maximum number of Discount Amounts.
If empty or zero, there is no limitation.')
        ));

        $fieldset->addField('stop_rules_processing', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Stop further rules processing'),
            'title' => Mage::helper('rewardpointsrule')->__('Stop further rules processing'),
            'name' => 'stop_rules_processing',
            'options' => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));
        
        $form->addFieldset('reward_history_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Transactions history')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/catalog_spent_actions.phtml'));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}
