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
 * RewardPointsRule Spending Catalog Conditions Tab Edit Form Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Spending_Catalog_Edit_Tab_Conditions
     */
    protected function _prepareForm()
    {
        if ( Mage::getSingleton('adminhtml/session')->getFormData()){
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $model = Mage::getModel('rewardpointsrule/spending_catalog')
                  ->load($data['rule_id'])
                  ->setData($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif ( Mage::registry('rule_data')){
            $model = Mage::registry('rule_data');
            $data = $model->getData();
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
              ->setTemplate('promo/fieldset.phtml')
              ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array('legend'=>Mage::helper('rewardpointsrule')->__('Apply the rule only if the following conditions are met (leave blank for all products)')))->setRenderer($renderer);

        $fieldset->addField('conditions','text',array(
          'name'	=> 'conditions',
          'label'	=> Mage::helper('rewardpointsrule')->__('Conditions'),
          'title'	=> Mage::helper('rewardpointsrule')->__('Conditions'),
          'required'	=> true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));


        $fieldset = $form->addFieldset('actions_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Action')));

          $fieldset->addField('simple_action', 'select', array(
              'label' => Mage::helper('rewardpointsrule')->__('Action'),
              'title' => Mage::helper('rewardpointsrule')->__('Action'),
              'name' => 'simple_action',
              'options' => array(
                  'fixed' => Mage::helper('rewardpointsrule')->__('Discount for every X points'),
                  'by_price' => Mage::helper('rewardpointsrule')->__('Spend X points for every Y amount of Price'),
              ),
              'onchange'  => 'toggleSimpleAction()',
              'note'=> Mage::helper('rewardpointsrule')->__('Select the type to spend points')
          ));

          $fieldset->addField('points_spended', 'text', array(
              'label' => Mage::helper('rewardpointsrule')->__('Points (X)'),
              'title' => Mage::helper('rewardpointsrule')->__('Points (X)'),
              'class' => 'required-entry',
              'required' => true,
              'name' => 'points_spended',
          ));

          $fieldset->addField('money_step', 'text', array(
              'label' => Mage::helper('rewardpointsrule')->__('Money Step (Y)'),
              'title' => Mage::helper('rewardpointsrule')->__('Money Step (Y)'),
              'name' => 'money_step',
              'after_element_html' => '<strong>[' . Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE) . ']</strong>',
          ));

          $fieldset->addField('max_points_spended', 'text', array(
              'label' => Mage::helper('rewardpointsrule')->__('Spending-point Step '),
              'title' => Mage::helper('rewardpointsrule')->__('Spending-point Step '),
              'name' => 'max_points_spended',
              'note' => Mage::helper('rewardpointsrule')->__('To use as the minimum spending points by this rule. The number of spending points must be a multiple of the configured number.
If empty or zero, it will be calculated based on product price, points (X) and money step (Y). ')
          ));
          //Hai.Tran 12/11/2013 fix gioi han spend points
          $fieldset->addField('max_price_spended_type', 'select', array(
              'label' => Mage::helper('rewardpointsrule')->__('Limit spending points based on'),
              'title' => Mage::helper('rewardpointsrule')->__('Limit spending points based on'),
              'name' => 'max_price_spended_type',
              'options' => array(
                  'none'    => Mage::helper('rewardpointsrule')->__('None'),
                  'by_price' => Mage::helper('rewardpointsrule')->__('A fixed amount of Price'),
                  'by_percent' => Mage::helper('rewardpointsrule')->__('A percentage of Price'),
              ),
              'onchange'  => 'toggleMaxPriceSpend()',
              'note'=> Mage::helper('rewardpointsrule')->__('Select the type to limit spending points')
          ));
          $fieldset->addField('max_price_spended_value', 'text', array(
              'label' => Mage::helper('rewardpointsrule')->__('Limit value allowed to spend points at'),
              'title' => Mage::helper('rewardpointsrule')->__('Limit value allowed to spend points at'),
              'name' => 'max_price_spended_value',
              'note' => Mage::helper('rewardpointsrule')->__('Set the maximum number of Discount Amounts.
If empty or zero, there is no limitation..')
          ));
          //End Hai.Tran 12/11/2013
          
         $form->addFieldset('reward_history_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Transactions history')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/catalog_conditions.phtml'));

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}