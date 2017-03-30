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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsBehavior Config Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Model_Config extends Mage_Adminhtml_Model_Config {
    /**
     * init config for behavior
     */
    protected function _initSectionsAndTabs() {
        $mergeConfig = Mage::getModel('core/config_base');

        $config = Mage::getConfig()->loadModulesConfiguration('behavior.xml');

        $this->_sections = $config->getNode('sections');

        $this->_tabs = $config->getNode('tabs');
    }

}
