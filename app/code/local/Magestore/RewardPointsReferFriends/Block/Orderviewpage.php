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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_Block_Orderviewpage extends Mage_Sales_Block_Order_Totals {

    /**
     * Rewrite initTotals to show discount on order view page, invoice, creditmemo
     */
    public function initTotals() {
        $order = $this->getParentBlock()->getOrder();
        if ($order->getRewardpointsInvitedDiscount() != 0) {
            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code' => 'rewardpoints_invited_discount',
                'value' => -$order->getRewardpointsInvitedDiscount(),
                'base_value' => -$order->getRewardpointsInvitedBaseDiscount(),
                'label' => 'Offer Discount',
                    )), 'subtotal');
        }
    }

}