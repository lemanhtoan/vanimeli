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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Rewardpointsspecialrefer extends Mage_Rule_Model_Rule {

    protected $_storeId = null;

    public function getStoreId() {
        return $this->_storeId;
    }

    public function setStoreId($storeId) {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * set field for multistore
     * @return array
     */
    public function getStoreAttributes() {
        return array(
            'title',
            'description_referal',
            'description_invited',
        );
    }

    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()) {
            $this->loadStoreValue();
        }
        return $this;
    }

    /**
     * load value for multistore
     * @param type $storeId
     * @return \Magestore_RewardPointsReferFriends_Model_Rewardpointsspecialrefer
     */
    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreId();
        if (!$storeId)
            return $this;
        $storeValues = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefervalue')->getCollection()
                ->addFieldToFilter('special_refer_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        return $this;
    }

    public function _construct() {
        parent::_construct();
        $this->_init('rewardpointsreferfriends/rewardpointsspecialrefer');
        $this->setIdFieldName('special_refer_id');
    }

    public function getConditionsInstance() {
        return Mage::getModel('salesrule/rule_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }

    public function loadPost(array $rule) {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }
        return $this;
    }

    /**
     * Fix error when load and save with collection
     */
    protected function _afterLoad() {
        $this->setConditions(null);
        $this->setActions(null);
        return parent::_afterLoad();
    }

    public function checkRule($order) {
        if ($this->getStatus() == 1) {
            $this->afterLoad();
            return $this->validate($order);
        }
        return false;
    }

    public function toString($format = '') {
        $str = Mage::helper('rewardpointsreferfriends')->__('Name: %s', $this->getTitle()) . "\n"
                . Mage::helper('rewardpointsreferfriends')->__('Start at: %s', $this->getFromDate()) . "\n"
                . Mage::helper('rewardpointsreferfriends')->__('Expire at: %s', $this->getToDate()) . "\n\n"
                . $this->getConditions()->toStringRecursive() . "\n\n";
        return $str;
    }

    /**
     * load customer for special refer
     * @param type $refercusModel
     * @param type $order
     * @return type
     */
    public function loadByCustomerOrder($address, $customerGroupId, $websiteId, $date) {
        $offerCollection = $this->getCollection()
                ->getAvailableSpecialOffer($customerGroupId, $websiteId, $date);
        $offers = array();
        foreach ($offerCollection as $offer) {
            if ($offer->getId()) {
                if ($offer->checkRule($address)) {
                    $offers[] = $offer;
                }
            }
        }
        return $offers;
    }

    protected function _beforeSave() {
        parent::_beforeSave();
        if ($storeId = $this->getStoreId()) {
            $defaultLabel = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultLabel->getData($attribute));
            }
        }
        if ($this->hasWebsiteIds()) {
            $websiteIds = $this->getWebsiteIds();
            if (is_array($websiteIds) && !empty($websiteIds)) {
                $this->setWebsiteIds(implode(',', $websiteIds));
            }
        }

        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        return $this;
    }

    protected function _afterSave() {

        if ($storeId = $this->getStoreId()) {

            $storeAttributes = $this->getStoreAttributes();

            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefervalue')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {

                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        return parent::_afterSave();
    }

    /**
     * Get address object which can be used for discount calculation
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddress(Mage_Sales_Model_Quote_Item_Abstract $item) {
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($item->getQuote()->getItemVirtualQty() > 0) {
            $address = $item->getQuote()->getBillingAddress();
        } else {
            $address = $item->getQuote()->getShippingAddress();
        }
        return $address;
    }

}