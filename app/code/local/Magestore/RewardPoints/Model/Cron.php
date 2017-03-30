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
 * RewardPoints Running Cron to process transactions
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Cron
{
    /**
     * Process transactions (holding, expire) by cron
     */
    public function processTransactions()
    {
        Varien_Profiler::start('REWARDPOINTS_CRON::processTransactions');
        $stores     = array();
        $allStores  = true;
        foreach (Mage::app()->getStores(true) as $_store) {
            if (Mage::getStoreConfig(Magestore_RewardPoints_Helper_Data::XML_PATH_ENABLE, $_store)) {
                $stores[$_store->getId()] = $_store->getId();
            } else {
                $allStores = false;
            }
        }
        
        // complete holding transactions
        $holdingDays = array();
        foreach ($stores as $_store) {
            $_holdDays = (int)Mage::getStoreConfig(
                Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $_store
            );
            $_holdDays = max(0, $_holdDays);
            $holdingDays[$_holdDays][$_store] = $_store;
        }
        if ($allStores && count($holdingDays) == 1) { // all stores
            reset($holdingDays);
            $_holdDays = key($holdingDays);
            $this->completeHoldingTransaction($_holdDays);
        } else { // each group stores
            foreach ($holdingDays as $_holdDays => $_storeIds) {
                $this->completeHoldingTransaction($_holdDays, $_storeIds);
            }
        }
        
        // expire transactions
        Varien_Profiler::start('REWARDPOINTS_CRON::expireTransactions');
        
        $expireTrans = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('status', array('lteq' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED))
            ->addFieldToFilter('expiration_date', array('to' => now()))
            ->addFieldToFilter('expiration_date', array('notnull' => true));
        $expireTrans->getSelect()->where('point_amount > point_used');
        
        if (count($expireTrans)) {
            $rewardAccount  = Mage::getSingleton('rewardpoints/customer');
            $customer       = Mage::getSingleton('customer/customer');
            if(count($customer)){
                foreach ($expireTrans as $_transaction) {
                    try {
                        $_transaction->setData('reward_account', $rewardAccount->load($_transaction->getRewardId()));
                        $_transaction->setData('customer', $customer->load($_transaction->getCustomerId()));
                        if($_transaction->getRewardId() && $_transaction->getCustomerId()){
                            $_transaction->expireTransaction();
                        }
                    } catch (Exception $e) {
                        Mage::logException($e);
                    }
                }
            }
        }
        
        unset($expireTrans);
        Varien_Profiler::stop('REWARDPOINTS_CRON::expireTransactions');
        
        // send before expire email to customer
        $beforeDays = array();
        foreach ($stores as $_store) {
            if (!Mage::getStoreConfigFlag(Magestore_RewardPoints_Model_Transaction::XML_PATH_EMAIL_ENABLE, $_store)) {
                $allStores = false;
                continue;
            }
            $_beforeDays = (int)Mage::getStoreConfig(
                Magestore_RewardPoints_Model_Transaction::XML_PATH_EMAIL_EXPIRE_DAYS, $_store
            );
            if ($_beforeDays <= 0) {
                $allStores = false;
            } else {
                $beforeDays[$_beforeDays][$_store] = $_store;
            }
        }
        if ($allStores && count($beforeDays) == 1) { // all stores
            reset($beforeDays);
            $_beforeDays = key($beforeDays);
            $this->sendBeforeExpireEmail($_beforeDays);
        } elseif (count($beforeDays)) { // each group stores
            foreach ($beforeDays as $_beforeDays => $_storeIds) {
                $this->sendBeforeExpireEmail($_beforeDays, $_storeIds);
            }
        }
        
        Varien_Profiler::stop('REWARDPOINTS_CRON::processTransactions');
    }
    
    /**
     * send email to customer before transaction is expired
     * 
     * @param int $beforeDays
     * @param null|array $storeIds
     */
    public function sendBeforeExpireEmail($beforeDays, $storeIds = null)
    {
        Varien_Profiler::start('REWARDPOINTS_CRON::sendBeforeExpireEmail');
        $futureTime   = date('Y-m-d H:i:s', time() + $beforeDays * 86400);
        $nowTime      = date('Y-m-d H:i:s', time() + 86400);
        $transactions = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('status', Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED)
            ->addFieldToFilter('expiration_date', array('from' => $nowTime))
            ->addFieldToFilter('expiration_date', array('to' => $futureTime))
            ->addFieldToFilter('expire_email', '0');
        $transactions->getSelect()->where('point_amount > point_used');
        if (is_array($storeIds) && count($storeIds)) {
            $transactions->addFieldToFilter('store_id', array('in' => $storeIds));
        }
        if (!count($transactions)) {
            Varien_Profiler::stop('REWARDPOINTS_CRON::sendBeforeExpireEmail');
            return ;
        }
        $rewardAccount  = Mage::getSingleton('rewardpoints/customer');
        $customer       = Mage::getSingleton('customer/customer');
        $transIds       = array();
        foreach ($transactions as $transaction) {
            try {
                $transaction->setData('reward_account', $rewardAccount->load($transaction->getRewardId()));
                $transaction->setData('customer', $customer->load($transaction->getCustomerId()));
                $transaction->sendBeforeExpireEmail();
                $transIds[] = $transaction->getId();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        if (count($transIds)) {
            try {
                Mage::getResourceModel('rewardpoints/transaction')->increaseExpireEmail($transIds);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Varien_Profiler::stop('REWARDPOINTS_CRON::sendBeforeExpireEmail');
    }
    
    /**
     * complete holding transaction for group store
     * 
     * @param int $holdDays
     * @param null|array $storeIds
     */
    public function completeHoldingTransaction($holdDays, $storeIds = null)
    {
        Varien_Profiler::start('REWARDPOINTS_CRON::completeHoldingTransaction');
        $releaseTime  = date('Y-m-d H:i:s', time() - $holdDays * 86400);
        $holdingTrans = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('status', Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD)
            ->addFieldToFilter('created_time', array('to' => $releaseTime));
        if (is_array($storeIds) && count($storeIds)) {
            $holdingTrans->addFieldToFilter('store_id', array('in' => $storeIds));
        }
        if (!count($holdingTrans)) {
            Varien_Profiler::stop('REWARDPOINTS_CRON::completeHoldingTransaction');
            return ;
        }
        $rewardAccount  = Mage::getSingleton('rewardpoints/customer');
        $customer       = Mage::getSingleton('customer/customer');
        foreach ($holdingTrans as $transaction) {
            try {
                $transaction->setData('reward_account', $rewardAccount->load($transaction->getRewardId()));
                $transaction->setData('customer', $customer->load($transaction->getCustomerId()));
                $transaction->completeTransaction();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        Varien_Profiler::stop('REWARDPOINTS_CRON::completeHoldingTransaction');
    }
}
