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
 * RewardPoints Show Cart Total (Review about Earning/Spending Reward Points)
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Checkout_Cart_Label extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'rewardpoints/checkout/cart/label.phtml';
    
	protected function _construct(){
        parent::_construct();
        if ($this->getRequest()->getModuleName() =='webpos') {
            $this->setTemplate('rewardpoints/checkout/cart/webposlabel.phtml');
        } else {
			$this->setTemplate($this->_template);
		}
    }
    /**
     * check reward points system is enabled or not
     * 
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::helper('rewardpoints')->isEnable();
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
        if (Mage::helper('rewardpoints/calculation_spending')->getTotalPointSpent() && !Mage::getStoreConfigFlag('rewardpoints/earning/earn_when_spend',Mage::app()->getStore()->getId())) {
            return 0;
        }
        return Mage::helper('rewardpoints/calculation_earning')->getTotalPointsEarning();
    }
}
