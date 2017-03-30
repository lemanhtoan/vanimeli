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
 * Rewardpoints Transaction Resource Model
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('rewardpoints/transaction', 'transaction_id');
    }

    /**
     * Update points used for other transaction by a reduce transaction
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return Magestore_RewardPoints_Model_Mysql4_Transaction
     */
    public function updatePointUsed($transaction) {
        $totalAmount = -$transaction->getPointAmount();
        if ($totalAmount <= 0) {
            return $this;
        }

        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        // Select all available transactions
        $selectSql = $read->select()->reset()
                ->from(array('t' => $this->getMainTable()), array('transaction_id', 'point_amount', 'point_used'))
                ->where('customer_id = ?', $transaction->getCustomerId())
                ->where('point_amount > point_used')
                ->where('status = ?', Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED)
                ->order(new Zend_Db_Expr('ISNULL(expiration_date) ASC, expiration_date ASC'));

        $trans = $read->fetchAll($selectSql);
        if (empty($trans) || !is_array($trans)) {
            return $this;
        }
        $usedIds = array();
        $lastId = 0;
        $lastUse = 0;
        foreach ($trans as $tran) {
            $availableAmount = $tran['point_amount'] - $tran['point_used'];
            if ($totalAmount < $availableAmount) {
                $lastUse = $tran['point_used'] + $totalAmount;
                $lastId = $tran['transaction_id'];
                break;
            }
            $totalAmount -= $availableAmount;
            $usedIds[] = $tran['transaction_id'];
            if ($totalAmount == 0) {
                break;
            }
        }

        // Update all depend transactions
        if (count($usedIds)) {
            $write->update($this->getMainTable(), array(
                'point_used' => new Zend_Db_Expr('point_amount')
                    ), array(
                new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $usedIds) . ' )')
            ));
        }
        if ($lastId) {
            $write->update($this->getMainTable(), array(
                'point_used' => new Zend_Db_Expr((string) $lastUse)
                    ), array(
                'transaction_id = ?' => $lastId
            ));
        }

        return $this;
    }

    /**
     * Update real points and point used for holding transaction
     * by reduce holding transaction real points and increase point used
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return Magestore_RewardPoints_Model_Mysql4_Transaction
     */
    public function updateRealPointHolding($transaction) {
        $totalAmount = -$transaction->getPointAmount();
        if ($totalAmount <= 0) {
            return $this;
        }

        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        // Select all holding transactions
        $selectSql = $read->select()->reset()
                ->from(array('t' => $this->getMainTable()), array(
                    'transaction_id', 'point_amount', 'point_used'
                ))
                ->where('customer_id = ?', $transaction->getCustomerId())
                ->where('order_id = ?', $transaction->getOrderId())
                ->where('action_type = ?', Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN)
                ->where('point_amount > point_used')
                ->where('status = ?', Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD);

        $trans = $read->fetchAll($selectSql);
        if (empty($trans) || !is_array($trans)) {
            return $this;
        }
        $usedIds = array();
        $lastId = 0;
        $lastUse = 0;
        $lastReal = 0;
        foreach ($trans as $tran) {
            $availableAmount = $tran['point_amount'] - $tran['point_used'];
            if ($totalAmount < $availableAmount) {
                $lastUse = $tran['point_used'] + $totalAmount;
                $lastId = $tran['transaction_id'];
                $lastReal = $tran['point_amount'] - $lastUse;
                break;
            }
            $totalAmount -= $availableAmount;
            $usedIds[] = $tran['transaction_id'];
            if ($totalAmount == 0) {
                break;
            }
        }

        // Update all depend transactions
        if (count($usedIds)) {
            $write->update($this->getMainTable(), array(
                'point_used' => new Zend_Db_Expr('point_amount'),
                'real_point' => new Zend_Db_Expr('0'),
                    ), array(
                new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $usedIds) . ' )')
            ));
        }
        if ($lastId) {
            $write->update($this->getMainTable(), array(
                'point_used' => new Zend_Db_Expr((string) $lastUse),
                'real_point' => new Zend_Db_Expr((string) $lastReal),
                    ), array(
                'transaction_id = ?' => $lastId
            ));
        }

        return $this;
    }

    /**
     * Update real points for complete transaction
     * by reduce complete transaction real points
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return Magestore_RewardPoints_Model_Mysql4_Transaction
     */
    public function updateRealPoint($transaction) {
        $totalAmount = -$transaction->getPointAmount();
        if ($totalAmount <= 0) {
            return $this;
        }

        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        // Select all completed transactions
        $selectSql = $read->select()->reset()
                ->from(array('t' => $this->getMainTable()), array(
                    'transaction_id', 'real_point'
                ))
                ->where('customer_id = ?', $transaction->getCustomerId())
                ->where('order_id = ?', $transaction->getOrderId())
                ->where('action_type = ?', Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN)
                ->where('real_point > 0');

        $trans = $read->fetchAll($selectSql);
        if (empty($trans) || !is_array($trans)) {
            return $this;
        }
        $usedIds = array();
        $lastId = 0;
        $lastReal = 0;
        foreach ($trans as $tran) {
            if ($totalAmount < $tran['real_point']) {
                $lastId = $tran['transaction_id'];
                $lastReal = $tran['real_point'] - $totalAmount;
                break;
            }
            $totalAmount -= $tran['real_point'];
            $usedIds[] = $tran['transaction_id'];
            if ($totalAmount == 0) {
                break;
            }
        }

        // Update all depend transactions
        if (count($usedIds)) {
            $write->update($this->getMainTable(), array(
                'real_point' => new Zend_Db_Expr('0')
                    ), array(
                new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $usedIds) . ' )')
            ));
        }
        if ($lastId) {
            $write->update($this->getMainTable(), array(
                'real_point' => new Zend_Db_Expr((string) $lastReal),
                    ), array(
                'transaction_id = ?' => $lastId
            ));
        }

        return $this;
    }

    /**
     * increase field expire_email for transactions
     *
     * @param array $transIds
     * @return Magestore_RewardPoints_Model_Mysql4_Transaction
     */
    public function increaseExpireEmail($transIds) {
        $this->_getWriteAdapter()->update($this->getMainTable(), array(
            'expire_email' => new Zend_Db_Expr('expire_email + 1')
                ), array(
            new Zend_Db_Expr('transaction_id IN ( ' . implode(' , ', $transIds) . ' )')
        ));
        return $this;
    }
    /**
     * import point from CSV
     * @param type $customers
     * @throws Exception
     */
    public function importPointFromCsv($customers) {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        try {
            foreach ($customers as $customerReward) {
                $customer_id = $customerReward->getId();
                $customer_email = $customerReward->getEmail();
                $point_amount = $customerReward->getPointBalance();
                $expireAfter = $customerReward->getExpireAfter();
                if (!$expireAfter) {
                    $expireDate = null;
                } else {
                    $expireDate = date('Y-m-d H:i:s', time() + $expireAfter * 3600 * 24);
                }
                if (!$point_amount)
                    continue;
                $store_id = $customerReward->getStoreId();
                $preTransaction = array(
                    'customer_id' => $customer_id,
                    'customer_email' => $customer_email,
                    'title' => Mage::helper('rewardpoints')->__('Import Points Balance from CSV'),
                    'action' => 'admin',
                    'action_type' => Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN,
                    'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
                    'store_id' => $store_id,
//                    'extra_content' => 'reward_id=' . $customerReward->getId(),
                    'point_amount' => $point_amount,
                    'point_used' => 0,
                    'real_point' => 0,
                    'expiration_date' => $expireDate,
                    'created_time' => now(),
                    'updated_time' => now(),
                );
                $preReward = array();

                $rewardAccount = Mage::helper('rewardpoints/customer')->getAccountByCustomerId($customer_id);
                if (!$rewardAccount->getId()) {
                    $rewardAccount->setCustomerId($customer_id)
                            ->setData('point_balance', 0)
                            ->setData('holding_balance', 0)
                            ->setData('spent_balance', 0)
                            ->setData('is_notification', 1)
                            ->setData('expire_notification', 1)
                            ->save();
                }
                $preTransaction['reward_id'] = $rewardAccount->getId();
                $point_balance = $rewardAccount->getPointBalance();
                $maxBalance = (int) Mage::getStoreConfig(Magestore_RewardPoints_Model_Transaction::XML_PATH_MAX_BALANCE, $store_id);
                if ($maxBalance > 0 && $point_amount > 0 && $point_balance + $point_amount > $maxBalance
                ) {
                    if ($maxBalance > $point_balance) {
                        $preTransaction['point_amount'] = $maxBalance - $point_balance;
                        $preTransaction['real_point'] = $maxBalance - $point_balance;
                        $preReward['point_balance'] = $maxBalance;
                    } else {
                        continue;
                    }
                } else {
                    $preReward['point_balance'] = $point_balance + $point_amount;
                }
                if ($preReward['point_balance'] < 0)
                    $preReward['point_balance'] = 0;
                $dataTransaction[] = $preTransaction;
                $write->update($this->getTable('rewardpoints/customer'), $preReward, "customer_id = $customer_id");
                if (count($dataTransaction) >= 1000) {
                    $write->insertMultiple($this->getTable('rewardpoints/transaction'), $dataTransaction);
                    $dataTransaction = array();
                }
            }
            if (!empty($dataTransaction)) {
                $write->insertMultiple($this->getTable('rewardpoints/transaction'), $dataTransaction);
            }
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
        unset($customers);
    }

}
