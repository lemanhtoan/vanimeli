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
 * RewardPointsReferFriends Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_System_Config_Source_Maxdiscount {

    /**
     * Options getter
     * 
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED, 'label' => Mage::helper('rewardpointsreferfriends')->__('Fixed amount')),
            array('value' => Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_PERCENT, 'label' => Mage::helper('rewardpointsreferfriends')->__('Percentage')),
        );
    }

}
