<?php

class Magestore_RewardPointsRule_Block_Adminhtml_Spending_Sales_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpointsrule')->__('Shopping Cart Spending Rule Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('rewardpointsrule')->__('General Information'),
            'title' => Mage::helper('rewardpointsrule')->__('General Information'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales_edit_tab_form')->toHtml(),
        ));

        $this->addTab('condition', array(
            'label' => Mage::helper('rewardpointsrule')->__('Conditions'),
            'title' => Mage::helper('rewardpointsrule')->__('Conditions'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions', array(
            'label' => Mage::helper('rewardpointsrule')->__('Actions'),
            'title' => Mage::helper('rewardpointsrule')->__('Actions'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_sales_edit_tab_actions')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
