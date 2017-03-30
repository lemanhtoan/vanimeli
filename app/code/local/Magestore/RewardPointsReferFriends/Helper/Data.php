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
 * RewardPointsReferFriends Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLE = 'rewardpoints/referfriendplugin/enable';

    /**
     * get enable referfriends plugin
     * @param type $store
     * @return boolean
     */
    public function isEnable($store = null) {
        return Mage::getStoreConfigFlag(Magestore_RewardPoints_Helper_Data::XML_PATH_ENABLE, $store) && Mage::getStoreConfigFlag(self::XML_PATH_ENABLE, $store);
    }

    /**
     * get home page magento
     * @return string
     */
    public function getHomePage() {
        $key = $this->getReferCode()->getKey();
        return $this->_getUrl('').'?k=' . $key;
    }

    public function getReferConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/referfriendplugin/' . $code, $store);
    }

    function isCustomerLogin() {
        if (Mage::helper('customer')->isLoggedIn())
            return true;
        else
            return false;
    }

    /*
     * fixed
     */

    function getSpecialOffer($store_id = null) {
        if ($store_id == null)
            $store_id = 0;
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $createdAt = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $offer = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->getCollection()
                ->setStoreId($store_id)
                ->addFieldToFilter('website_ids', array('finset' => $customer->getWebsiteId()))
                ->addFieldToFilter('customer_group_ids', array('finset' => $customer->getGroupId()))
                ->addFieldToFilter('status', 1);
        $offer->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', $createdAt);
        $offer->setOrder('priority', 'DESC');
        return $offer;
    }

    /**
     * Get item on refer_customer
     * @return boolean
     */
    function getReferCode() {
        if (Mage::helper('customer')->isLoggedIn()) {
            $customerId = Mage::helper('customer')->getCustomer()->getId();
            $referCustomer = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($customerId, 'customer_id');
            if(!$referCustomer || !$referCustomer->getId()){
                $referCustomer->setCustomerId($customerId)->save();
                return $this->getReferCode();
            }
            return $referCustomer;
        }
        return null;
    }

    function getLinkKey(){
        $referCode = $this->getReferCode();
        if($referCode && $referCode->getId()){
            return $referCode->getKey();
        } else {
            return null;
        }
    }
    //link which admin setting
    function getDefaultLink($refer = false) {
        if ($this->getReferConfig('refer_method') != 'coupon') {
            $link = $this->getReferConfig('default_shopping_link');
            $key = $this->getLinkKey();
            if ($link && $key) {
                if (!$refer)
                    return $link . '?k=' . $key;
                else
                    return $link;
            }
        }
        return false;
    }

    //link to policy page defaultpage/refer
    function getPolicyLink() {
        if ($this->getReferConfig('refer_method') != 'coupon') {
            $key = $this->getLinkKey();
            if ($key)
                return Mage::getUrl('rewardpointsreferfriends/refer/index') . '?k=' . $key;
            else
                return false;
        }
        else
            return false;
    }

    /**
     * link to share each product
     * @param type $proId
     * @return boolean
     */
    function getProductShareLink($proId) {
        $config = $this->getReferConfig('refer_method');
        if ($config != 'coupon') {
            $key = $this->getReferCode()->getKey();
            if ($key)
                return Mage::getUrl('catalog/product/view', array(
                            'id' => $proId,
                            '_use_rewrite' => true,
                            '_secure' => true
                        )) . '?k=' . $key;
        }
        return false;
    }

    /**
     * Get coupon
     * @return boolean
     */
    function getCoupon() {
        $config = $this->getReferConfig('refer_method');
        if ($config != 'link') {
            $value = $this->getReferCode();
            if ($value && $value->getCoupon())
                return $value->getCoupon();
        }
        else
            return false;
    }

    /**
     * Get default earn points for referal customer
     * @return boolean
     */
    function getPointDefault() {
        if ($this->getReferConfig('use_default_config') && $this->getReferConfig('earn_points'))
            return Mage::helper('rewardpoints/point')->format(Mage::helper('rewardpointsreferfriends/calculation_earning')->round($this->getReferConfig('earn_points')));
        return false;
    }

    /**
     * Get default discount for invited customer
     * @return boolean
     */
    function getDiscountDefault($store_id = null) {
        $discountValue = $this->getReferConfig('discount_value', $store_id);
        if ($this->getReferConfig('use_default_config') && $discountValue) {
            if ($this->getReferConfig('discount_type') == 'fix') {
                return Mage::helper('core')->formatPrice($discountValue);
            } else {
                return $discountValue . '%';
            }
        }
        return false;
    }

    /**
     * Convert date to form d/m/Y and return string
     * @param type $fromDate
     * @param type $toDate
     * @return type
     */
    function getDateExpire($fromDate, $toDate) {
        if ($fromDate && !$toDate) {
            $datefrom = Mage::getModel('core/date')->date('d M Y', $fromDate);
            return '(From ' . $datefrom . ')';
        }
        if (!$fromDate && $toDate) {
            $dateto = Mage::getModel('core/date')->date('d M Y', $toDate);
            return '(To ' . $dateto . ')';
        }
        if ($fromDate && $toDate) {
            $datefrom = Mage::getModel('core/date')->date('d M Y', $fromDate);
            $dateto = Mage::getModel('core/date')->date('d M Y', $toDate);
            return '(' . $datefrom . ' - ' . $dateto . ')';
        }
        return '';
    }

    /**
     * get style color of coupon to print pdf
     * @return String
     */
    public function getPdfStyleColor() {
        return '#' . $this->getReferConfig('style_color');
    }

    /**
     * get text color of coupon to print pdf
     * @return string
     */
    public function getPdfCouponColor() {
        return '#' . $this->getReferConfig('coupon_color');
    }

    /**
     * limit customer uses coupon
     * @return type
     */
    public function getUsesPerCustomer() {
        return $this->getReferConfig('uses_per_customer');
    }

    /**
     * get type discount show in pdf
     */
    public function getTypeMaxDiscount($store_id = null) {
        return $this->getReferConfig('max_discount', $store_id);
    }

    /**
     * get offer which have discount max
     * @param int $store_id
     * @return type Object
     */
    public function getOfferWithMaxDiscount($store_id = null) {
        if ($store_id == null)
            $store_id = 0;
//        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $createdAt = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        $offer = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->getCollection()
                ->setStoreId($store_id)
//                ->addFieldToFilter('website_ids', array('finset' => $customer->getWebsiteId()))
//                ->addFieldToFilter('discount_value', array('finset' => $customer->getGroupId()))
                ->addFieldToFilter('status', Magestore_RewardPointsReferFriends_Model_Status::STATUS_ENABLED)
                ->addFieldToFilter('discount_type', $this->getTypeMaxDiscount());

        $offer->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', $createdAt);
        $offer->setOrder('discount_value', 'DESC');
        return $offer->getFirstItem();
    }

    public function getCaptionCoupon($store_id = null) {
        return $this->getReferConfig('caption', $store_id);
    }

    public function getBackgroundCoupon($store_id = null) {
        return '#' . $this->getReferConfig('background_coupon', $store_id);
    }

    public function getBackgroundImg($store_id = null) {
        return $this->getReferConfig('background', $store_id);
    }

}
