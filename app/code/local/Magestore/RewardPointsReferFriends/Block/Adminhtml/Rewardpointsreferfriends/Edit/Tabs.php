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
 * Rewardpointsreferfriends Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('rewardpointsreferfriends_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpointsreferfriends')->__('Special Offer Information'));
    }

    /**
     * prepare before render block to html
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tabs
     */
    protected function _beforeToHtml() {
        $this->addTab('general_section', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('General Information'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('General Information'),
            'content' => $this->getLayout()->createBlock('rewardpointsreferfriends/adminhtml_rewardpointsreferfriends_edit_tab_form')->toHtml(),
        ));

        $this->addTab('conditions_section', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Conditions'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Conditions'),
            'content' => $this->getLayout()->createBlock('rewardpointsreferfriends/adminhtml_rewardpointsreferfriends_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions_section', array(
            'label' => Mage::helper('rewardpointsreferfriends')->__('Actions'),
            'title' => Mage::helper('rewardpointsreferfriends')->__('Actions'),
            'content' => $this->getLayout()->createBlock('rewardpointsreferfriends/adminhtml_rewardpointsreferfriends_edit_tab_actions')->toHtml(),
        ));


        return parent::_beforeToHtml();
    }

}