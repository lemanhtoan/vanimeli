<?php

class Magestore_RewardpointsBehavior_Block_Adminhtml_Earning_Behavior_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
    /**
     * 
     */
    public function __construct() {
        parent::__construct();
        $this->setId('rule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('rewardpointsbehavior')->__('Behavior Earning Rules'));
    }
    /**
     *
     * @return type
     */
    protected function _beforeToHtml() {
        $this->addTab('general', array(
            'label' => Mage::helper('rewardpointsbehavior')->__('Earning Points Configuration'),
            'title' => Mage::helper('rewardpointsbehavior')->__('Earning Points Configuration'),
            'content' => '',
        ));
        return parent::_beforeToHtml();
    }

}
