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
 * RewardPoints Name and Image Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Name extends Magestore_RewardPoints_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_RewardPoints_Block_Name
     */
    public function _prepareLayout() {
        $this->setTemplate('rewardpoints/name.phtml');
        return parent::_prepareLayout();
    }

    /**
     * get current balance of customer as text
     * 
     * @return string
     */
    public function getBalanceText() {
        return Mage::helper('rewardpoints/customer')->getBalanceFormated();
    }

    /**
     * get Image (Logo) HTML for reward points
     * 
     * @return string
     */
    public function getImageHtml() {
        return Mage::helper('rewardpoints/point')->getImageHtml($this->getIsAnchorMode());
    }
}
