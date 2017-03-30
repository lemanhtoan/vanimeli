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
 * Rewardpoints Freeshipping for order Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Total_Quote_Freeshipping extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * set freeshipping for spent point order
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_RewardPoints_Model_Total_Quote_Freeshipping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        /** @var $helper Magestore_RewardPoints_Helper_Calculation_Spending */
        $helper = Mage::helper('rewardpoints/calculation_spending');
        
        if (!$helper->getTotalPointSpent()) {
            return $this;
        }
        
        $isFreeShipping = Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Spending::XML_PATH_FREE_SHIPPING,
            $address->getQuote()->getStoreId()
        );
        if (!$isFreeShipping) {
            return $this;
        }
        
        $address->setFreeShipping(true);
        Mage::dispatchEvent('rewardpoints_collect_total_freeshipping', array(
            'address'   => $address
        ));
        
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return $this;
    }
}
