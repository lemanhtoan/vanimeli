<?php

class Magestore_RewardPointsBehavior_Block_Adminhtml_Earning_Behavior extends Mage_Adminhtml_Block_System_Config_Edit {



    public function __construct() {
        parent::__construct();

        $sections = Mage::getSingleton('rewardpointsbehavior/config')->getSections();
        $this->_section = $sections->rewardpoints;
        $this->setTitle((string) $this->_section->label);
        $this->setHeaderCss((string) $this->_section->header_css);
    }
/**
 * init Form
 * @return \Magestore_RewardPointsBehavior_Block_Adminhtml_Earning_Behavior
 */
    public function initForm() {
        $blockName = 'rewardpointsbehavior/adminhtml_earning_behavior_form';
        $this->setChild('form', $this->getLayout()->createBlock($blockName)->initForm());
        return $this;
    }

}
