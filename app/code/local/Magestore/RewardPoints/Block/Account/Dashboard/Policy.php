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
 * Rewardpoints Account Dashboard Policy
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Account_Dashboard_Policy extends Magestore_RewardPoints_Block_Template
{
    /**
     * earning transaction will be expired after days
     * 
     * @return int
     */
    public function getTransactionExpireDays()
    {
        $days = (int)Mage::getStoreConfig(Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE);
        return max(0, $days);
    }
    
    /**
     * get day holling point
     * 
     * @return int
     */
    public function getHoldingDays()
    {
        $days = (int)Mage::getStoreConfig(Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS);
        return max(0, $days);
    }
    
    /**
     * Maximum point balance allowed
     * 
     * @return int
     */
    public function getMaxPointBalance()
    {
        $maxBalance = (int)Mage::getStoreConfig(Magestore_RewardPoints_Model_Transaction::XML_PATH_MAX_BALANCE);
        return max(0, $maxBalance);
    }
    
    /**
     * Minimum point allowed to redeem
     * 
     * @return int
     */
    public function getRedeemablePoints()
    {
        $points = (int)Mage::getStoreConfig(Magestore_RewardPoints_Helper_Customer::XML_PATH_REDEEMABLE_POINTS);
        return max(0, $points);
    }
    
    /**
     * Maximun point spneding per order
     * 
     * @return int
     */
    public function getMaxPerOrder()
    {
        $points = (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Spending::XML_PATH_MAX_POINTS_PER_ORDER
        );
        return max(0, $points);
    }
}
