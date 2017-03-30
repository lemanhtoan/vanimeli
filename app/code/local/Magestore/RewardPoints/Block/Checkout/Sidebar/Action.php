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
 * RewardPoints Show Earning Point on Mini Cart Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Checkout_Sidebar_Action extends Magestore_RewardPoints_Block_Template
{
    /**
     * Check store is enable for display on minicart sidebar
     * 
     * @return type
     */
    public function enableDisplay()
    {
        return Mage::helper('rewardpoints/point')->showOnMiniCart();
    }
    
    /**
     * get Image (HTML) for reward points
     * 
     * @param boolean $hasAnchor
     * @return string
     */
    public function getImageHtml($hasAnchor = true)
    {
        return Mage::helper('rewardpoints/point')->getImageHtml($hasAnchor);
    }
}
