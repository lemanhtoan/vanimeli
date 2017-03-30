<?php

/**
 * Rewrite System Config Switcher
 */
class Magestore_RewardPointsBehavior_Block_Adminhtml_System_Configuration_Switcher extends Mage_Adminhtml_Block_System_Config_Switcher {

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setTemplate('rewardpointsbehavior/config/switcher.phtml');
        return $this;
    }

}
