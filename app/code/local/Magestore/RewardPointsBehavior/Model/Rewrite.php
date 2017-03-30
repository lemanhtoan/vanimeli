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
 * Rewardpointsbehavior Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Model_Rewrite extends Magestore_RewardPoints_Model_Transaction {

    /**
     * send Update Balance to customer
     * 
     * @param Magestore_RewardPoints_Model_Customer $rewardAccount
     * @return Magestore_RewardPoints_Model_Transaction
     */
    public function sendUpdateBalanceEmail($rewardAccount = null) {
        if (($this->getData('action') == 'birthday') && $this->getStatus() == Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED  && Mage::helper('rewardpointsbehavior')->getBirthdayConfig('enable_send', Mage::app()->getStore()->getId())) {
            if (!Mage::getStoreConfigFlag(self::XML_PATH_EMAIL_ENABLE, $this->getStoreId())) {
                return $this;
            }
            if (is_null($rewardAccount)) {
                $rewardAccount = $this->getRewardAccount();
            }
            if (!$rewardAccount->getIsNotification()) {
                return $this;
            }
            $customer = $this->getCustomer();
            if (!$customer) {
                $customer = Mage::getModel('customer/customer')->load($rewardAccount->getCustomerId());
            }
            if (!$customer->getId()) {
                return $this;
            }

            $store = Mage::app()->getStore($this->getStoreId());
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);

            $templateId = Mage::helper('rewardpointsbehavior')->getBirthdayConfig('emai_template', $store);
            Mage::getModel('core/email_template')
                    ->setDesignConfig(array(
                        'area' => 'frontend',
                        'store' => $store->getId()
                    ))->sendTransactional(
                    $templateId, Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $store), $customer->getEmail(), $customer->getName(), array(
                'store' => $store,
                'customer' => $customer,
                'title' => $this->getTitle(),
                'amount' => $this->getPointAmount(),
                'total' => $rewardAccount->getPointBalance(),
                'point_amount' => Mage::helper('rewardpoints/point')->format($this->getPointAmount(), $store),
                'point_balance' => Mage::helper('rewardpoints/point')->format($rewardAccount->getPointBalance(), $store),
                'status' => $this->getStatusLabel(),
                    )
            );

            $translate->setTranslateInline(true);
            return $this;
        }
        return parent::sendUpdateBalanceEmail($rewardAccount);
    }

}