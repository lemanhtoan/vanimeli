<?php

class Magestore_RewardPointsBehavior_Block_Adminhtml_Earning_Behavior_Form extends Mage_Adminhtml_Block_System_Config_Form {
    /**
     * init object for behavior plugin
     * @return \Magestore_RewardPointsBehavior_Block_Adminhtml_Earning_Behavior_Form
     */
    protected function _initObjects() {
        $this->_configRoot = Mage::getConfig()->getNode(null, $this->getScope(), $this->getScopeCode());

        $this->_configDataObject = Mage::getModel('adminhtml/config_data')
                ->setSection($this->getSectionCode())
                ->setWebsite($this->getWebsiteCode())
                ->setStore($this->getStoreCode());
        $this->_configData = $this->_configDataObject->load();

        $this->_configFields = Mage::getSingleton('rewardpointsbehavior/config');

        $this->_defaultFieldsetRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_fieldset');
        $this->_defaultFieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        return $this;
    }

}
