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
 * RewardPoints Show Spending Point on Shopping Cart Page
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Checkout_Cart_Point extends Magestore_RewardPoints_Block_Template
{
    /**
     * get reward points spending block helper
     * 
     * @return Magestore_RewardPoints_Helper_Block_Spend
     */
    public function getBlockHelper()
    {
        return Mage::helper('rewardpoints/block_spend');
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
     * call method that defined from block helper
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args) {
        $helper = $this->getBlockHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
            // return call_user_method_array($method, $helper, $args);
        }
        return parent::__call($method, $args);
    }
}
