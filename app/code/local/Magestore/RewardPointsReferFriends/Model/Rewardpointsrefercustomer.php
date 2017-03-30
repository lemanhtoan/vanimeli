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
 * Rewardpointsreferfriends Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Rewardpointsrefercustomer extends Mage_Core_Model_Abstract {

    /**
     * 
     */
    public function _construct() {
        parent::_construct();
        $this->_init('rewardpointsreferfriends/rewardpointsrefercustomer');
    }

    /**
     * hash key
     * @return string
     */
    public function getHashKey() {
        $gravel = rand(1, 100000);
        $hashKey = substr(str_replace(',', '', strtr(base64_encode(microtime() . ',' . $this->getId() . ',' . $this->getCustomerId() . ',' . $gravel), '+/=,', '-_,')), 3, 8);
        $customerRefer = $this->load($hashKey, 'key');
        if ($customerRefer && $customerRefer->getId()) {
            return $this->getHashKey();
        } else {
            return $hashKey;
        }
    }

    protected function _beforeSave() {
        if (!$this->getData('coupon')) {
            if (Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method')) {
                if (Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') == 'coupon') {
                    $this->setData('coupon', Mage::helper('rewardpointsreferfriends')->getReferConfig('pattern'));
                } else {
                    $this->setData('coupon', Mage::helper('rewardpointsreferfriends')->getReferConfig('pattern_for_both'));
                }
            } else {
                $this->setData('coupon', Mage::helper('rewardpointsreferfriends/coupon')->getDefaulPatern());
            }
        }
        if (!$this->getData('key')) {
            $this->setData('key', $this->getHashKey());
        }
        if ($this->couponIsExpression())
            $this->setData('coupon', $this->_getCouponCode());
        if (!$this->getData('coupon'))
            $this->setData('coupon', strtr(base64_encode(microtime()), '+/=,', 'ACTZ'));
        return parent::_beforeSave();
    }

    /**
     * convert coupon code
     * @return type
     */
    public function couponIsExpression() {
        return Mage::helper('rewardpointsreferfriends/coupon')->isExpression($this->getData('coupon'));
    }

    /**
     * enprotype coupon code
     * @return string
     */
    protected function _getCouponCode() {
        $code = Mage::helper('rewardpointsreferfriends/coupon')->calcCode($this->getData('coupon'));
        $times = 10;
        while (Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByCoupon($code)->getId() && $times) {
            $code = Mage::helper('rewardpointsrefercustomer/coupon')->calcCode($this->getData('coupon'));
            $times--;
            if ($times == 0)
                $code = '';
        }
        return $code;
    }

    /**
     * load coupon from tabel rewardpoints_refer_customer
     * @param type $code
     * @return type
     */
    public function loadByCoupon($code) {
        if (!$code)
            return Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer');
        $collection = Mage::getResourceModel('rewardpointsreferfriends/rewardpointsrefercustomer_collection');
        $collection->getSelect()
                ->where("FIND_IN_SET('$code',`coupon`)");
        return $collection->getFirstItem();
    }

    /**
     * load key from tabel rewardpoints_refer_customer
     * @param type $key
     * @return type
     */
    public function loadByKey($key) {
        if (!$key)
            return Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer');
        $collection = Mage::getResourceModel('rewardpointsreferfriends/rewardpointsrefercustomer_collection');
        $collection->getSelect()
                ->where("FIND_IN_SET('$key',`key`)");
        return $collection->getFirstItem();
    }

    /**
     * validate refer links customer
     * @return boolean
     */
    public function validateReferLinkCus() {

        if (!$this->getId() || Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') != 'link')
            return false;
        return true;
    }

    public function addKey($key) {
        if (!$key)
            return $this;
        $keyString = $this->getData('key');
        $keyArray = explode(',', $keyString);
        $keyArray[] = $key;
        $this->setData('key', implode(',', $keyArray));
        return $this;
    }

    public function getKey() {
        $keyString = $this->getData('key');
        $keyArray = explode(',', $keyString);
        return end($keyArray);
    }

    public function addCoupon($coupon) {
        if (!$coupon)
            return $this;
        $couponString = $this->getData('coupon');
        $couponArray = explode(',', $couponString);
        $couponArray[] = $coupon;
        $this->setData('coupon', implode(',', $couponArray));
        return $this;
    }

    public function getCoupon() {
        $couponString = $this->getData('coupon');
        $couponArray = explode(',', $couponString);
        return end($couponArray);
    }

    /**
     * set key or coupon is newest.
     * @param type $type
     * @param type $value
     */
    public function moveToTop($type, $value) {
        $oldValue = $this->getData($type);
        $oldValueArray = explode(',', $oldValue);
        $index = array_search($value, $oldValueArray);
        if ($index) {
            unset($oldValueArray[$index]);
        }
        $oldValueArray[] = $value;
        $this->setData($type, implode(',', $oldValueArray))->save();
    }

}
