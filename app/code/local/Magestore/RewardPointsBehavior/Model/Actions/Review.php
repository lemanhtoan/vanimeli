<?php

class Magestore_RewardPointsBehavior_Model_Actions_Review extends Magestore_RewardPoints_Model_Action_Abstract implements Magestore_RewardPoints_Model_Action_Interface {

    /**
     * get reward point Review label
     * 
     * @return string
     */
    public function getActionLabel() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for reviewing a product');
    }

    /**
     * get reward point Review type
     * 
     * @return int
     */
    public function getActionType() {
        return Magestore_RewardPoints_Model_Transaction::ACTION_TYPE_EARN;
    }

    /**
     * get reward point Review title
     * 
     * @return string
     */
    public function getTitle() {
        return Mage::helper('rewardpointsbehavior')->__('Receive point(s) for reviewing a product');
    }

    /**
     * prepare data of action to storage on transactions
     * the array that returned from function $action->getData('transaction_data')
     * will be setted to transaction model
     * 
     * @return Magestore_RewardPoints_Model_Action_Interface
     */
    public function prepareTransaction() {
        $review = $this->getData('action_object');
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
            'store_id' => $review->getStoreId(),
            'extra_content' => $extraContent->serialize(null, '=', '&', ''),
        );

        // Check if transaction need to hold
        $holdDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_HOLDING_DAYS, $review->getStoreId()
        );
        if ($holdDays > 0) {
            $transactionData['status'] = Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD;
        }

        // Set expire time for current transaction
        $expireDays = (int) Mage::getStoreConfig(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_EXPIRE, $review->getStoreId()
        );
        $transactionData['expiration_date'] = $this->getExpirationDate($expireDays);
        $this->setData('transaction_data', $transactionData);
        return parent::prepareTransaction();
    }

    /**
     * get reward point Review amount
     * 
     * @return int
     */
    public function getPointAmount() {
        $review = $this->getData('action_object');
        $amount = (int) Mage::helper('rewardpointsbehavior')->getReviewConfig('review', $review->getStoreId());
        $amountPerDay = (int) Mage::helper('rewardpointsbehavior')->getReviewConfig('review_limit', $review->getStoreId());
        if (is_numeric($amountPerDay) && $amountPerDay > 0) {
            $amountOfDay = Mage::helper('rewardpointsbehavior')->getAmountofDay('review', $this->getCustomer()->getId(), date('Y-m-d', strtotime($review->getCreatedAt())));
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