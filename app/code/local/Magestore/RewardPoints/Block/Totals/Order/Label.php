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
 * Rewardpoints Total Label Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Totals_Order_Label extends Magestore_RewardPoints_Block_Template
{
    /**
     * add points label into creditmemo total
     *     
     */
    public function initTotals()
    {
        if (!$this->isEnable()) {
            return $this;
        }
        $totalsBlock = $this->getParentBlock();
        $order = $totalsBlock->getOrder();
        
        if ($order->getRewardpointsEarn()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_earn_label',
                'label' => $this->__('Earn Points'),
                'value' => Mage::helper('rewardpoints/point')->format($order->getRewardpointsEarn()),
                'is_formated'   => true,
            )), 'first');
        }
        
        if ($order->getRewardpointsSpent()) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_spent_label',
                'label' => $this->__('Spend Points'),
                'value' => Mage::helper('rewardpoints/point')->format($order->getRewardpointsSpent()),
                'is_formated'   => true,
            )), 'first');
        }
    }
}
