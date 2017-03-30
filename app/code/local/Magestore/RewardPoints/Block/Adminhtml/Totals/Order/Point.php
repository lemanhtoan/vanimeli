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
 * Rewardpoints Total Point Spend Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Totals_Order_Point extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
    /**
     * add points value into order total
     *     
     */
    public function initTotals()
    {
        $totalsBlock = $this->getParentBlock();
        $order = $totalsBlock->getOrder();
        
        if ($order->getRewardpointsDiscount()>=0.0001) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints',
                'label' => $this->__('Point Discount'),
                'value' => -$order->getRewardpointsDiscount(),
            )), 'subtotal');
        }
        // Show Refunded Points at here
        $refundSpentPoints = (int)Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action_type', Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_SPEND)
            ->addFieldToFilter('point_amount', array('gt' => 0))
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($refundSpentPoints > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_refund_spent',
                'label' => $this->__('Refund spent points'),
                'value' => $refundSpentPoints,
                'is_formated'   => true,
                'area'  => 'footer',
            )));
        }
        
        $refundEarnedPoints = -(int)Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('action_type', Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN)
            ->addFieldToFilter('point_amount', array('lt' => 0))
            ->addFieldToFilter('action', 'earning_creditmemo')
            ->addFieldToFilter('order_id', $order->getId())
            ->getFieldTotal();
        if ($refundEarnedPoints > 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code'  => 'rewardpoints_refund_earned',
                'label' => $this->__('Refund earned points'),
                'value' => $refundEarnedPoints,
                'is_formated'   => true,
                'area'  => 'footer',
            )));
        }
    }
}
