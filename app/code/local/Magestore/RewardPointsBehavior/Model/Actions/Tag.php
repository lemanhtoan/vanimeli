<?php

class Magestore_RewardPointsBehavior_Model_Actions_Tag extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    /**
     * get reward point Tag label
     * 
     * @return srting
     */
    public function getActionLabel() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for tagging a product');
    }

    /**
     * get reward point Tag type
     * 
     * @return int
     */
    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get reward point Tag title
     * 
     * @return string
     */
    public function getTitle() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for tagging a product');
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction() {
        $tag = $this->getData('action_object');
        $extraContent = $this->getData('extra_content');
        if (isset($extraContent['notice']))
            $extraContent['notice'] = htmlspecialchars($extraContent['notice']);

        if (isset($extraContent['extra_content']) && is_array($extraContent['extra_content'])) {
            $extra_content = new Varien_Object($extraContent['extra_content']);
            $extraContent['extra_content'] = $extra_content->serialize(null, '=', '&', '');
        }
        $extraContent = new Varien_Object($extraContent);
        $transactionData = array(
            'status' => Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED,
            'store_id' => $tag->getStoreId(),
            'extra_content' => $extraContent->serialize(null, '=', '&', ''),
        );

        // Check if transaction need to hold
        $holdDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $tag->getStoreId()
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE, $tag->getStoreId()
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);
        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

    /**
     * get reward point Tag amount
     * 
     * @return int
     */
    public function getPointAmount() {
        $tag = $this->getData('action_object');
        $amount = (int) Mage::helper('rewardpointsbehavior')->getTagConfig('tag', $tag->getStoreId());
        $amountPerDay = (int) Mage::helper('rewardpointsbehavior')->getTagConfig('tag_limit', $tag->getStoreId());
        if (is_numeric($amountPerDay) && $amountPerDay > 0) {
            $amountOfDay = Mage::helper('rewardpointsbehavior')->getAmountofDay('tag', $this->getCustomer()->getId());
            if ($amount > 0 && ($amountOfDay + $amount) > $amountPerDay) {
                if ($amountOfDay < $amountPerDay)
                    $amount = $amountPerDay - $amountOfDay;
                else
                    $amount = 0;
            }
        }

        return (int) $amount;
    }
    
}