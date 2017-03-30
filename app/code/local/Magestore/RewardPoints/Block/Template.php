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
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Core Block Template Block
 * You should write block extended from this block when you write plugin
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Template extends Mage_Core_Block_Template {

    /**
     * check reward points system is enabled or not
     * 
     * @return boolean
     */
    public function isEnable() {
        return Mage::helper('rewardpoints')->isEnable();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml() {
        if ($this->isEnable()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @param $plugin
     * @return bool
     */
    public function isPluginEnable($plugin) {
        if (!$plugin) {
            return false;
        }
        if (Mage::helper('core')->isModuleEnabled($plugin) && Mage::helper('core')->isModuleOutputEnabled($plugin)) {
            return true;
        }
        return false;
    }

}
