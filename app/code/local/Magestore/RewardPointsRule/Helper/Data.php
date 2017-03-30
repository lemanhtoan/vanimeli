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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer 
 */
class Magestore_RewardPointsRule_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLE_PLUGIN    = 'rewardpoints/rewardpointsrule/enable';
    const XML_PATH_SHOW_ON_LISTING  = 'rewardpoints/display/product_listing';
	const XML_PATH_ENABLE_REWARDPOINTS = 'rewardpoints/general/enable';
    
    /**
     * check plugin is enabled or not
     * 
     * @param type $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_PLUGIN, $store) && Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_REWARDPOINTS, $store);
    }
    
    
    /**
     * check show earning reward points on product listing page
     * 
     * @param type $store
     * @return type
     */
    public function getCanShow($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SHOW_ON_LISTING, $store);
    }
}
