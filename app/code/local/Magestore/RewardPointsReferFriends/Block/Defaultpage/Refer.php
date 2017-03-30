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
class Magestore_Rewardpointsreferfriends_Block_Defaultpage_Refer extends Mage_Core_Block_Template
{
    function _construct() {
        parent::_construct();
    }
    /**
     * Call helper RewardpointsReferfriends data
     * @return type
     */
    function callHelper(){
        return Mage::helper('rewardpointsreferfriends');
    }
    /**
     * Get Special Offer
     * @return type
     */
    function getSpecialOffer($store_id) {
        return $this->callHelper()->getSpecialOffer($store_id);
    }
    /**
     * Get link to redirect from page
     * @return boolean
     */
    function getShoppingLink(){
        $link = $this->callHelper()->getReferConfig('default_shopping_link');
        if($link) return $link;
        else return $this->getUrl('');
    }
    /**
     * check enable default
     * @return boolean
     */
    function isEnableDefault(){
        if($this->callHelper()->getReferConfig('use_default_config') && $this->getDefaultDiscount()) return true;
        return false;
    }
    /**
     * Get default discount for invited customer
     * @return type
     */
    function getDefaultDiscount(){
        return $this->callHelper()->getDiscountDefault();
    }
}