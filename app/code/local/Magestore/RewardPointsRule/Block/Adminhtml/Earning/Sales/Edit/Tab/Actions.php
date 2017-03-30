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
 * RewardPointsRule Earning Sales Edit Actions Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tab_Actions
     */
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $model = Mage::getModel('rewardpointsrule/earning_sales')
                ->load($data['rule_id'])
                ->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif (Mage::registry('rule_data')) {
            $model = Mage::registry('rule_data');
            $data = $model->getData();
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        $this->setForm($form);
        $fieldset = $form->addFieldset('points_action_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Earning Point Action')));

        $fieldset->addField('simple_action', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Action'),
            'title' => Mage::helper('rewardpointsrule')->__('Action'),
            'name' => 'simple_action',
            'options' => array(
                'fixed' => Mage::helper('rewardpointsrule')->__('Give fixed X points to Customers'),
                'by_total' => Mage::helper('rewardpointsrule')->__('Give X points for every Y money spent'),
                'by_qty' => Mage::helper('rewardpointsrule')->__('Give X points for every Y qty purchased'),
            ),
            'onchange' => 'toggleSimpleAction()',
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
        
        $fieldset->addField('qty_step', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Quantity (Y)'),
            'title' => Mage::helper('rewardpointsrule')->__('Quantity (Y)'),
            'name' => 'qty_step',
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

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newActionHtml/form/rule_actions_fieldset'));

        $fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Apply the rule only to cart items matching the following conditions (leave blank for all items)')))->setRenderer($renderer);

        $fieldset->addField('actions', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Apply To'),
            'title' => Mage::helper('rewardpointsrule')->__('Apply To'),
            'name' => 'actions',
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));

        $form->addFieldset('sales_action_example', array('legend' => Mage::helper('rewardpointsrule')->__('Example actions')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/sales_actions.phtml'));
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
}
