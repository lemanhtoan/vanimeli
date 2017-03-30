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
 * RewardPoints Show Cart Total (Review about Earning/Spending Reward Points) on Backend
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Cart_Label extends Mage_Adminhtml_Block_Sales_Order_Create_Totals_Default
{
    protected $_template = 'rewardpoints/checkout/cart/label.phtml';
    
    /**
     * check reward points system is enabled or not
     * 
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::helper('rewardpoints')->isEnable($this->getStore());
    }
    
    /**
     * get reward points helper
     * 
     * @return Magestore_RewardPoints_Helper_Point
     */
    public function getPointHelper()
    {
        return Mage::helper('rewardpoints/point');
    }
    
    /**
     * get total points that customer use to spend for order
     * 
     * @return int
     */
    public function getSpendingPoint()
    {
        return Mage::helper('rewardpoints/calculation_spending')->getTotalPointSpent();
    }
    
    /**
     * get total points that customer can earned by purchase order
     * 
     * @return int
     */
    public function getEarningPoint()
    {
        return Mage::helper('rewardpoints/calculation_earning')->getTotalPointsEarning();
    }
}
