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
class Magestore_RewardPointsBehavior_Model_Actions_Fbshare extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    public function getActionLabel() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for sharing link via Facebook');
    }

    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    public function getPointAmount() {
        $fb = $this->getData('action_object');
        return (int) $fb['share_earn'];
    }

    public function getTitle() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for sharing link via Facebook');
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction() {
        $fb = $this->getData('action_object');
        $storeId = Mage::app()->getStore()->getId();
        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'store_id' => $storeId,
            'extra_content' => $this->getData('extra_content'),
        );

        // Check if transaction need to hold
        $holdDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $storeId
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE, $storeId
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);
        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

}