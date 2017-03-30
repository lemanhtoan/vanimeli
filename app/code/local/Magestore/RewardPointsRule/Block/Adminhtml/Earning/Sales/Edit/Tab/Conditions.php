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
 * RewardPointsRule Earning Sales Edit Tab Conditions Block
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tab_Conditions
     */
    protected function _prepareForm()
    {
        if ( Mage::getSingleton('adminhtml/session')->getFormData()){
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $model = Mage::getModel('rewardpointsrule/earning_sales')
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

        $fieldset = $form->addFieldset('conditions_fieldset', array('legend'=>Mage::helper('rewardpointsrule')->__('Apply the rule to shopping carts only if the following conditions are met (leave blank for all carts)')))->setRenderer($renderer);

        $fieldset->addField('conditions','text',array(
          'name'	=> 'conditions',
          'label'	=> Mage::helper('rewardpointsrule')->__('Conditions'),
          'title'	=> Mage::helper('rewardpointsrule')->__('Conditions'),
          'required'	=> true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->addFieldset('sales_conditions_example', array('legend' => Mage::helper('rewardpointsrule')->__('Example conditions')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/sales_conditions.phtml'));

        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}