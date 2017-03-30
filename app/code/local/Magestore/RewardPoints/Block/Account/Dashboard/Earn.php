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
 * Rewardpoints Account Dashboard Earning Policy
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Account_Dashboard_Earn extends Magestore_RewardPoints_Block_Template
{
    /**
     * check showing container
     * 
     * @return boolean
     */
    public function getCanShow()
    {
        $rate = $this->getEarningRate();
        if ($rate && $rate->getId()) {
            $canShow = true;
        } else {
            $canShow = false;
        }
        $container = new Varien_Object(array(
            'can_show' => $canShow
        ));
        Mage::dispatchEvent('rewardpoints_block_dashboard_earn_can_show', array(
            'container' => $container,
        ));
        return $container->getCanShow();
    }
    
    /**
     * get earning rate
     * 
     * @return Magestore_RewardPoints_Model_Rate
     */
    public function getEarningRate()
    {
        if (!$this->hasData('earning_rate')) {
            $this->setData('earning_rate',
                Mage::getModel('rewardpoints/rate')->getRate(Magestore_RewardPoints_Model_Rate::MONEY_TO_POINT)
            );
        }
        return $this->getData('earning_rate');
    }
    
    /**
     * get current money formated of rate
     * 
     * @param Magestore_RewardPoints_Model_Rate $rate
     * @return string
     */
    public function getCurrentMoney($rate)
    {
        if ($rate && $rate->getId()) {
            $money = $rate->getMoney();
            return Mage::app()->getStore()->convertPrice($money, true);
        }
        return '';
    }
}
