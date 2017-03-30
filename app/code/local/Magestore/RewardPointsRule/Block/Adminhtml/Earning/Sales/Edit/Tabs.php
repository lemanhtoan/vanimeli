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
 * RewardPointsRule Earning Sales Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpointsrule')->__('Shopping Cart Earning Rule Information'));
    }

    /**
     * prepare before render block to html
     * 
     * @return Magestore_RewardPointsRule_Block_Adminhtml_Earning_Sales_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('rewardpointsrule')->__('General Information'),
            'title' => Mage::helper('rewardpointsrule')->__('General Information'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_sales_edit_tab_form')->toHtml(),
        ));

        $this->addTab('condition', array(
            'label' => Mage::helper('rewardpointsrule')->__('Conditions'),
            'title' => Mage::helper('rewardpointsrule')->__('Conditions'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_sales_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions', array(
            'label' => Mage::helper('rewardpointsrule')->__('Actions'),
            'title' => Mage::helper('rewardpointsrule')->__('Actions'),
            'content' => $this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_sales_edit_tab_actions')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
