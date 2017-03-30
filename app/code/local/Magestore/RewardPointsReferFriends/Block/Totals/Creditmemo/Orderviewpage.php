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
class Magestore_Rewardpointsreferfriends_Block_Totals_Creditmemo_Orderviewpage extends Magestore_RewardPoints_Block_Template {

    /**
     * Rewrite initTotals to show discount on order view page, invoice, creditmemo
     */
    public function initTotals() {
        if (!Mage::helper('rewardpointsreferfriends')->isEnable()) {
            return $this;
        }
        $totalsBlock = $this->getParentBlock();
        $creditmemo = $totalsBlock->getSource();
        if ($creditmemo->getRewardpointsInvitedDiscount() != 0) {
            $totalsBlock->addTotal(new Varien_Object(array(
                'code' => 'rewardpoints_invited_discount',
                'value' => -$creditmemo->getRewardpointsInvitedDiscount(),
                'base_value' => -$creditmemo->getRewardpointsInvitedBaseDiscount(),
                'label' => 'Offer Discount',
                    )), 'subtotal');
        }
    }

}