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
 * RewardPoints Customer Account and Balance Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Customer extends Mage_Core_Helper_Abstract {

    const XML_PATH_DISPLAY_TOPLINK = 'rewardpoints/display/toplink';
    const XML_PATH_REDEEMABLE_POINTS = 'rewardpoints/spending/redeemable_points';

    /**
     * reward account model
     * 
     * @var Magestore_RewardPoints_Model_Customer
     */
    protected $_rewardAccount = null;

    /**
     * current customer ID
     * 
     * @var int
     */
    protected $_customerId = null;

    /**
     * current working store ID
     * 
     * @var int
     */
    protected $_storeId = null;

    /**
     * get current customer model
     * 
     * @return Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     * @return null
     */
    public function getCustomer() {
        if (Mage::app()->getStore()->isAdmin()) {
            $this->_customer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
            return $this->_customer;
        }
        if (Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
            return $this->_customer;
        }
//        if (Mage::getConfig()->getModuleConfig('Magestore_Webpos')->is('active', 'true')) {
//            $this->_customer = Mage::getModel('customer/customer')->load(Mage::getModel('checkout/session')->getData('rewardpoints_customerid'));
//        }
        return $this->_customer;
    }

    /**
     * get current customer ID
     * 
     * @return int
     */
    public function getCustomerId() {
        if (is_null($this->_customerId)) {
			$customerId = 0;
            if (Mage::app()->getStore()->isAdmin()) {
                $this->_customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
                return $this->_customerId;
            } else {
				if(Mage::getSingleton('customer/session')->isLoggedIn())
					$customerId = Mage::getSingleton('customer/session')->getCustomerId();
            }
            if ($customerId) {
                $this->_customerId = $customerId;
            } else {
                $this->_customerId = 0;
            }
        }
//        if (Mage::getConfig()->getModuleConfig('Magestore_Webpos')->is('active', 'true') && !Mage::getSingleton('customer/session')->getCustomerId()) {
//            $customerId = Mage::getModel('checkout/session')->getData('rewardpoints_customerid');
//            if ($customerId) {
//                $this->_customerId = $customerId;
//            } else {
//                $this->_customerId = 0;
//            }
//        }
        return $this->_customerId;
    }

    /**
     * get current working store id, used when checkout
     * 
     * @return int
     */
    public function getStoreId() {
        if (is_null($this->_storeId)) {
            if (Mage::app()->isSingleStoreMode()) {
                $this->_storeId = Mage::app()->getStore()->getId();
            } else if (Mage::app()->getStore()->isAdmin()) {
                $this->_storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
            } else {
                $this->_storeId = Mage::app()->getStore()->getId();
            }
        }
        return $this->_storeId;
    }

    /**
     * get current reward points customer account
     * 
     * @return Magestore_RewardPoints_Model_Customer
     */
    public function getAccount() {
        if (is_null($this->_rewardAccount)) {
            $this->_rewardAccount = Mage::getModel('rewardpoints/customer');
            if ($this->getCustomerId()) {
                $this->_rewardAccount->load($this->getCustomerId(), 'customer_id');
                $this->_rewardAccount->setData('customer', $this->getCustomer());
            }
        }
        /**
         * Web Pos 
         */
//        if (Mage::getConfig()->getModuleConfig('Magestore_Webpos')->is('active', 'true') && Mage::app()->getRequest()->getModuleName()=='webpos') {
//            $this->_rewardAccount = Mage::getModel('rewardpoints/customer');
//            if ($this->getCustomerId()) {
//                $this->_rewardAccount->load($this->getCustomerId(), 'customer_id');
//                $this->_rewardAccount->setData('customer', $this->getCustomer());
//            }
//        }
        return $this->_rewardAccount;
    }

    /**
     * get Reward Points Account by Customer
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @return Magestore_RewardPoints_Model_Customer
     */
    public function getAccountByCustomer($customer) {
        $rewardAccount = $this->getAccountByCustomerId($customer->getId());
        if (!$rewardAccount->hasData('customer')) {
            $rewardAccount->setData('customer', $customer);
        }
        return $rewardAccount;
    }

    /**
     * get Reward Points Account by Customer ID
     * 
     * @param int $customerId
     * @return Magestore_RewardPoints_Model_Customer
     */
    public function getAccountByCustomerId($customerId = null) {
        if (empty($customerId) || $customerId == $this->getCustomerId()
        ) {
            return $this->getAccount();
        }
        return Mage::getModel('rewardpoints/customer')->load($customerId, 'customer_id');
    }

    /**
     * get reward points balance of current customer
     * 
     * @return int
     */
    public function getBalance() {
        return $this->getAccount()->getPointBalance();
    }

    /**
     * get string of points balance formated
     * 
     * @return string
     */
    public function getBalanceFormated() {
        return Mage::helper('rewardpoints/point')->format(
                        $this->getBalance(), $this->getStoreId()
        );
    }

    /**
     * get string of points balance formated
     * Balance is estimated after customer use point to spent
     * 
     * @return string
     */
    public function getBalanceAfterSpentFormated() {
        return Mage::helper('rewardpoints/point')->format(
                        $this->getBalance() - Mage::helper('rewardpoints/calculation_spending')->getTotalPointSpent(), $this->getStoreId()
        );
    }

    /**
     * check show customer reward points on top link
     * 
     * @param type $store
     * @return boolean
     */
    public function showOnToplink($store = null) {
        return Mage::getStoreConfigFlag(self::XML_PATH_DISPLAY_TOPLINK, $store);
    }

    /**
     * check customer can use point to spend for order or not
     * 
     * @param type $store
     * @return boolean
     */
    public function isAllowSpend($store = null) {
        $minPoint = (int) Mage::getStoreConfig(self::XML_PATH_REDEEMABLE_POINTS, $store);
        if ($minPoint > $this->getBalance()) {
            return false;
        }
        return true;
    }

}
