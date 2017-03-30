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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints Rule Setup Resource Model
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Mysql4_Setup extends Magestore_RewardPoints_Model_Mysql4_Setup {

    public function getWebsiteIds() {
        $read = $this->_getResource()->getReadConnection();
        $table = $this->getTable('core/website');
        $result = $read->fetchAll("SELECT `website_id` FROM $table WHERE `website_id` > 0 ");
        $websiteIds = array();
        foreach ($result as $row) {
            $websiteIds[] = $row['website_id'];
        }
        return $websiteIds;
    }

    public function getWebsiteConfig($path, $websiteId = 0) {
        $read = $this->_getResource()->getReadConnection();
        $table = $this->getTable('core/config_data');
        $query = "SELECT `value` FROM `$table` WHERE `path` = '$path' ";
        if ($websiteId) {
            $query .= " AND `scope` = 'websites' AND `scope_id` = '$websiteId'";
        } else {
            $query .= " AND `scope` = 'default'";
        }
        $result = $read->fetchOne($query);
        if (!$result && $websiteId != 0) {
            $result = $this->getWebsiteConfig($path, 0);
        }
        return $result;
    }

    public function createOfferFromSetting() {
        $websiteIds = $this->getWebsiteIds();
        $defaultData = array(
            'status' => $this->getWebsiteConfig('rewardpoints/referfriendplugin/use_default_config', 0),
            'commission_point' => $this->getWebsiteConfig('rewardpoints/referfriendplugin/earn_points', 0),
            'discount_value' => $this->getWebsiteConfig('rewardpoints/referfriendplugin/discount_value', 0),
            'discount_type' => $this->getWebsiteConfig('rewardpoints/referfriendplugin/discount_type', 0),
        );
        $tempIds = array();
        foreach ($websiteIds as $website) {
            $temp = array();
            $temp['status'] = $this->getWebsiteConfig('rewardpoints/referfriendplugin/use_default_config', $website);
            $temp['commission_point'] = $this->getWebsiteConfig('rewardpoints/referfriendplugin/earn_points', $website);
            $temp['discount_value'] = $this->getWebsiteConfig('rewardpoints/referfriendplugin/discount_value', $website);
            $temp['discount_type'] = $this->getWebsiteConfig('rewardpoints/referfriendplugin/discount_type', $website);
            if (!array_diff($defaultData, $temp)) {
                $tempIds[] = $website;
            } else {
                $temp['website_ids'] = $website;
                $this->saveOffer($temp);
            }
        }
        $defaultData['website_ids'] = implode(',', $tempIds);
        $this->saveOffer($defaultData);
        $this->setAllWebsiteUseDefaultToZero();
    }

    private function saveOffer($data) {
        // get all customer groups id
        $customerGroupdIds = array();
        $customerGroups = Mage::getModel('customer/group')->getCollection();
        foreach ($customerGroups as $customerGroup) {
            $customerGroupdIds[] = $customerGroup->getId();
        }
        // refine Discount type 
        $data['discount_type'] == 'fix' ? $data['discount_type'] = '1' : $data['discount_type'] = '2';

        $offer = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')
                ->setData($data)
                ->setData('title',Mage::helper('rewardpointsreferfriends')->__("Default Offer."))
                ->setData('customer_group_ids', implode(',', $customerGroupdIds))
                ->setData('stop_rules_processing', '1')
                ->setData('priority', '-1');
        if ($offer->getData('discount_value') || $offer->getData('commission_point')) {
            $offer->save();
        }
        return $this;
    }

    private function setAllWebsiteUseDefaultToZero() {
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core/write');
        $table = $resource->getTableName('core/config_data');
        $write->query("UPDATE $table SET `value` = '0' WHERE `path` = 'rewardpoints/referfriendplugin/use_default_config' ");
        return $this;
    }

}
