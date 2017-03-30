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
 * Rewardpoints Customer Information Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Customer extends Mage_Core_Model_Abstract {

    /**
     * Redefine event Prefix, event object
     * 
     * @var string
     */
    protected $_eventPrefix = 'rewardpoints_customer';
    protected $_eventObject = 'rewardpoints_customer';

    public function _construct() {
        parent::_construct();
        $this->_init('rewardpoints/customer');
    }

    /**
     * Get Customer Model
     * 
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        if (!$this->hasData('customer')) {
            $this->setData('customer', Mage::getModel('customer/customer')->load($this->getData('customer_id'))
            );
        }
        return $this->getData('customer');
    }

    /**
     * get customer total sales
     * @return int
     */
    public function getTotalSales() {
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return 0;
        }
        //get all order belong this customer Id
        $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('status', array('nin' => array(
                Mage_Sales_Model_Order::STATE_CLOSED,
                Mage_Sales_Model_Order::STATE_CANCELED)
        ));
        if (!$orders->getSize()) {
            return 0;
        }

        // calculate total sales
        $total = 0;
        foreach ($orders as $order) {
            $total += $order->getBaseTotalPaid() - $order->getBaseTotalRefunded();
        }
        return $total;
    }

    /**
     * get Accumulated Points.
     * @return int 
     */
    public function getAccumulatedPoints() {
        return $this->getPointBalance() + $this->getSpentBalance();
    }

}
