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
 * Action Earn Point for Order
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Action_Earning_Affiliate
    extends Magestore_RewardPoints_Model_Action_Abstract
    implements Magestore_RewardPoints_Model_Action_Interface
{
    /**
     * Calculate and return point amount that customer earned from order
     *
     * @return int
     */
    public function getPointAmount()
    {   
        $rewardPoints = $this->getData('action_object');
        return $rewardPoints->getRewardpointsEarn();
    }
    /**
     * get Label for this action, this is the reason to change
     * customer reward points balance
     *
     * @return string
     */
    public function getActionLabel()
    {
        return Mage::helper('rewardpoints')->__('Earn point(s) from Affiliate instead of money');
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get Text Title for this action, used when create an transaction
     *
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('rewardpoints')->__('Earn point(s) from Affiliate instead of money');
    }

    /**
     * get HTML Title for action depend on current transaction
     *
     * @param Magestore_RewardPoints_Model_Transaction $transaction
     * @return string
     */
    public function getTitleHtml($transaction = null)
    {
        return $this->getTitle();
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Abstract
     */
    public function prepareTransaction()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $rewardPoints = $this->getData('action_object');
        

        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'store_id' => $storeId
        );

        // Check if transaction need to hold
        $holdDays = (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS,
            $storeId
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int)Mage::getStoreConfig(
            Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE,
            $storeId
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);

        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

}
