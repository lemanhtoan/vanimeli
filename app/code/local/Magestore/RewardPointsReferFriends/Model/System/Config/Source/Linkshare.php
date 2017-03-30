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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_System_Config_Source_Linkshare
{
    /**
     * Options getter
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'default_link', 'label' => Mage::helper('rewardpointsreferfriends')->__('Default Landing Page')),
            array('value' => 'policy_link', 'label' => Mage::helper('rewardpointsreferfriends')->__('The page that Customers are redirected to when clicking on the referral link via Email, Facebook, Twitter, Google+. 
The number of uses per customer. 
Link to Referral Policy Page'))
        );
    }
}
