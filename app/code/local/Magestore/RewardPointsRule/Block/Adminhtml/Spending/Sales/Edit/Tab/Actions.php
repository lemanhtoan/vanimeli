<?php

class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Sales_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $model = Mage::getModel('rewardpointsrule/spending_sales')
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
        $fieldset = $form->addFieldset('points_action_fieldset', array('legend' => Mage::helper('rewardpointsrule')->__('Update prices using the following information')));

        $fieldset->addField('discount_style', 'select', array(
            'label' => Mage::helper('rewardpointsrule')->__('Action'),
            'title' => Mage::helper('rewardpointsrule')->__('Action'),
            'name' => 'discount_style',
            'options' => array(
                'cart_fixed' => Mage::helper('rewardpointsrule')->__('Give a fixed discount amount for the whole cart'),
                'by_percent' => Mage::helper('rewardpointsrule')->__('Give a percent discount amount for the whole cart'),
            ),
        ));

        $fieldset->addField('discount_amount', 'text', array(
            'label' => Mage::helper('rewardpointsrule')->__('Discount Amount'),
            'title' => Mage::helper('rewardpointsrule')->__('Discount Amount'),
            'name' => 'discount_amount',
            'required' => true,
            'note'=> Mage::helper('rewardpointsrule')->__('Discount received for every X points in tab Conditions')
            
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

        $form->addFieldset('sales_action_example', array('legend' => Mage::helper('rewardpointsrule')->__('Example actions')))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('rewardpointsrule/example/sales_spending_actions.phtml'));
        
        $form->setValues($data);
        return parent::_prepareForm();
    }
}
