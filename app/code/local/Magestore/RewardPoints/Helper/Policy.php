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
 * RewardPoints Policy Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Policy extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SHOW_POLICY  = 'rewardpoints/general/show_policy_menu';
    const XML_PATH_POLICY_PAGE  = 'rewardpoints/general/policy_page';
    const XML_PATH_SHOW_WELCOME  = 'rewardpoints/general/show_welcome_page';
    
    /**
     * get Policy URL, return the url to view Policy
     * 
     * @return string
     */
    public function getPolicyUrl()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_SHOW_POLICY)) {
            return Mage::getUrl('rewardpoints/index/index');
        }
        return Mage::getUrl('rewardpoints/index/policy');
    }

    /**
     * @return mixed
     */
    public function getWelcomeUrl()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_SHOW_WELCOME)) {
            return Mage::getUrl('rewardpoints/index/index');
        }
        return Mage::getUrl(null, array('_direct' => Mage::getStoreConfig('rewardpoints/general/welcome_page')));
    }
    
    /**
     * Check policy menu configuration
     * 
     * @param mixed $store
     * @return boolean
     */
    public function showPolicyMenu($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SHOW_POLICY, $store);
    }
}
