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
class Magestore_Rewardpointsreferfriends_Block_Product_Productrefer extends Mage_Catalog_Block_Product_View_Abstract
{
    function _prepareLayout() {
        parent::_prepareLayout();
    }
    /**
     * check show link on catalog/product/view page or not
     * @return boolean
     */
    function showLink(){
        if($this->callHelper()->isEnable(Mage::app()->getStore()->getId())&&$this->callHelper()->isCustomerLogin()) return true;
        else return false;
    }
//    function showOnCheckoutSuccess(){
//        if($this->showLink() && $this->callHelper()->getReferConfig()) return true;
//        return false;
//    }
    /**
     * Call helper Rewardpointsreferfriends data
     * @return type
     */
    function callHelper(){
        return Mage::helper('rewardpointsreferfriends');
    }
    /**
     * Get product share link
     * @param type $proId
     * @return type
     */
    function getShareUrl($proId){
        return $this->callHelper()->getProductShareLink($proId);
    }
    /**
     * Get coupon
     * @return type
     */
    function getShareCoupon(){
        return $this->callHelper()->getCoupon();
    }
    /**
     * Get default point for referal customer
     * @return type
     */
    function getDefaultEarnPointsRefer(){
        return $this->callHelper()->getPointDefault();
    }
    /**
     * Check has Special Offer or not
     * @return type
     */
    function hasSpecialOffer(){
        return $this->callHelper()->getSpecialOffer(Mage::app()->getStore()->getId())->getSize();
    }
    /**
     * Get link send email
     * @return type
     */
    function getSendFriendUrl(){
        return Mage::getUrl('rewardpointsreferfriends/refer/sendmail', array('id'=>$this->getProduct()->getId()));
    }
    function getSendFriendUrlCheckout(){
        return Mage::getUrl('rewardpointsreferfriends/refer/sendmail');
    }
}