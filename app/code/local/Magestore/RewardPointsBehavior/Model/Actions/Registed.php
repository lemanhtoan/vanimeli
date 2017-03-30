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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsBehavior Newsletter Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Model_Actions_Registed extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    /**
     * get reward point Registed label
     * 
     * @return string
     */
    public function getActionLabel() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for registering successfully');
    }

    /**
     * get reward point Registed type
     * 
     * @return int
     */
    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get reward point Registed amount
     * 
     * @return int
     */
    public function getPointAmount() {
        $customer = $this->getData('action_object');
        return (int) Mage::helper('rewardpointsbehavior')->getSignConfig('signing_up',$customer->getStoreId());
    }

    /**
     * get reward point Registed title
     * 
     * @return string
     */
    public function getTitle() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for registering successfully');
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction() {
        $customer = $this->getData('action_object');

        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'store_id' => $customer->getStoreId(),
        );

        // Check if transaction need to hold
        $holdDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $customer->getStoreId()
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE, $customer->getStoreId()
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);
        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }    

}