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
 * RewardPointsRule Earning Catalog Edit Actions Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Catalog_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Earning_Catalog_Edit_Tab_Actions
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
        $fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Earning Point Action')));

        $fieldset->addField('simple_action', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Action'),
            'title' => Mage::helper('rewardpointsrule')->__('Action'),
            'name' => 'simple_action',
            'options' => array(
                'fixed' => Mage::helper('rewardpointsrule')->__('Give fixed X points to Customers'),
                'by_price' => Mage::helper('rewardpointsrule')->__('Give X points for every Y amount of Price'),
                'by_profit' => Mage::helper('rewardpointsrule')->__('Give X points for every Y amount of Profit'),
            ),
            'onchange'  => 'toggleSimpleAction()',
            'note'=> Mage::helper('rewardpointsrule')->__('Select the type to earn points')
        ));

        $fieldset->addField('points_earned', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Points (X)'),
            'title' => Mage::helper('rewardpointsrule')->__('Points (X)'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'points_earned',
        ));

        $fieldset->addField('money_step', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Money Step (Y)'),
            'title' => Mage::helper('rewardpointsrule')->__('Money Step (Y)'),
            'name' => 'money_step',
            'after_element_html' => '<strong>[' . Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE) . ']</strong>',
        ));

        $fieldset->addField('max_points_earned', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Maximum points earned by this rule'),
            'title' => Mage::helper('rewardpointsrule')->__('Maximum points earned by this rule'),
            'name' => 'max_points_earned',
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
        
        $form->addFieldset('catalog_earned_actions_example', array('legend' => Mage::helper('rewardpointsrule')->__('Example actions')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/catalog_earned_actions.phtml'));

        $form->setValues($data);
        return parent::_prepareForm();
    }
}
