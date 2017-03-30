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
 * Rewardpointsreferfriends Resource Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Mysql4_Rewardpointsspecialrefer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_store_id = null;

    public function setStoreId($value) {
        $this->_store_id = $value;
        return $this;
    }

    public function getStoreId() {
        return $this->_store_id;
    }

    public function _construct() {
        parent::_construct();
        $this->_init('rewardpointsreferfriends/rewardpointsspecialrefer');
    }

    protected function _afterLoad() {
        parent::_afterLoad();
        if ($storeId = $this->getStoreId())
            foreach ($this->_items as $item) {
                $item->setStoreId($storeId)->loadStoreValue();
            }
        return $this;
    }

    /**
     * 
     * @param type $customerGroupId
     * @param type $websiteId
     * @param type $date
     * @return \Magestore_RewardPointsReferFriends_Model_Mysql4_Rewardpointsspecialrefer_Collection
     */
    public function getAvailableSpecialOffer($customerGroupId, $websiteId, $date = null) {
//        $createdAt = $order->getCreatedAt() ? $order->getCreatedAt() : date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
        if (is_null($date)) {
            $date = Mage::getModel('core/date')->date('Y-m-d');
        }
        $this->addFieldToFilter('website_ids', array('finset' => $websiteId))
                ->addFieldToFilter('customer_group_ids', array('finset' => (int) $customerGroupId))
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('priority', array('gteq' => '0'));
        $this->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))', $date);
        $this->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', $date);
        $this->setOrder('priority', 'DESC');
        return $this;
    }

    public function getDefaultOffer($customerGroupId, $websiteId, $date = null) {
        if (is_null($date)) {
            $date = Mage::getModel('core/date')->date('Y-m-d');
        }
        $this->addFieldToFilter('website_ids', array('finset' => $websiteId))
                ->addFieldToFilter('customer_group_ids', array('finset' => (int) $customerGroupId))
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('priority', array('eq' => '-1'));
        $this->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))', $date);
        $this->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))', $date);
        return $this->getFirstItem();
    }

}
