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
 * Rewardpoints Show Label Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Total_Quote_Label extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Magestore_RewardPoints_Model_Total_Quote_Label constructor.
     */
    public function __construct()
    {
        $this->setCode('rewardpoints_label');
    }
    
    /**
     * collect reward point label 
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_RewardPoints_Model_Total_Quote_Label
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $address->setRewardpointsSpent(0);
        $address->setRewardpointsBaseDiscount(0);
        $address->setRewardpointsDiscount(0);
        $address->setRewardpointsEarn(0);
        $address->setMagestoreBaseDiscount(0);
        
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $child->setRewardpointsBaseDiscount(0)
                            ->setRewardpointsDiscount(0)
                            ->setMagestoreBaseDiscount(0)
                            ->setRewardpointsSpent(0);
                }
            } elseif ($item->getProduct()) {
                $item->setRewardpointsBaseDiscount(0)
                        ->setRewardpointsDiscount(0)
                        ->setMagestoreBaseDiscount(0)
                        ->setRewardpointsSpent(0);
            }
        }
        return $this;
    }
    /**
     * add point label
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Magestore_RewardPoints_Model_Total_Quote_Label
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => '1',
            'value' => 1,
        ));
        return $this;
    }
}
