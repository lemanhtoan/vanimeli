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
 * Rewardpoints Settings
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Account_settings extends Magestore_RewardPoints_Block_Template
{
    /**
     * get current reward points account
     * 
     * @return Magestore_RewardPoints_Model_Customer
     */
    public function getRewardAccount()
    {
        $rewardAccount = Mage::helper('rewardpoints/customer')->getAccount();
        if (!$rewardAccount->getId()) {
            $rewardAccount->setIsNotification(1)
                ->setExpireNotification(1);
        }
        return $rewardAccount;
    }
}
