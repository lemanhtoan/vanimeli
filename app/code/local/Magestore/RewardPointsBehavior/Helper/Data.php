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
 * RewardPointsBehavior Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLE = 'rewardpoints/behaviorplugin/enable';

    /**
     * get enable referfriends plugin
     * @param type $store
     * @return boolean
     */
    public function isEnable($store = null) {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE, $store) && Mage::helper('rewardpoints')->isEnable();
    }

    /**
     * get configuration
     * @param type $code
     * @param type $store
     * @return boolean
     */
    public function getBehaviorConfig($code, $store = null) {
        return true;
        //return Mage::getStoreConfig('rewardpoints/referfriendplugin/' . $code, $store);
    }

    public function getSignConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_signandnews/' . $code, $store);
    }

    public function getRateConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_rate_product/' . $code, $store);
    }

    public function getReviewConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_review_product/' . $code, $store);
    }

    public function getTagConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_tagging_product/' . $code, $store);
    }

    public function getPollConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_talk_poll/' . $code, $store);
    }

    public function getBirthdayConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_customer_birthday/' . $code, $store);
    }

    public function getSocialConfig($group, $code, $store = null){
        return Mage::getStoreConfig("rewardpoints/{$group}/{$code}", $store);
    }

    public function getLoginConfig($code, $store = null) {
        return Mage::getStoreConfig('rewardpoints/group_login/' . $code, $store);
    }

    public function getIcon() {
        return Mage::helper('rewardpoints/point')->getImageHtml();
    }
    public function getSharingTools(){
        return array('facebook' => 'fblike', 'facebook_share' => 'fbshare', 'googleplus' => 'ggplus', 'twitter' => 'tweeting','pinterest' => 'pin');//, 'linkedin' => 'linkedin', 'pinterest' => 'pinterest');
    }
    public function canEarnPoint($action, $customer, $link){
        /** If custome is not login **/
        if(!$customer) return false;
        
        /** If point earn equal 0 **/
        if($this->getEarnPoint($action, $customer) == 0) return false;
        
        /** If minimum time is not enought **/
        $sharingTools = $this->getSharingTools();
        $group = ($action == 'facebook_share') ? 'facebook' : $action;
        $minimumTime = $this->getSocialConfig($group, 'minimum_time');
        $createdTime = strtotime($this->getSocialCreated($sharingTools[$action], $customer->getId()));
        if (($createdTime + $minimumTime) > time()) return false;
        
        /** If customer has already shared this link **/
        if(strpos($link,'?k='))
            $link = substr($link, 0, strpos($link,'?k='));
        if($this->getSocialEarned($sharingTools[$action], $customer->getId(), $link)->getSize() > 0) return false;
        return true;
    }
    public function getEarnPoint($action, $customer){
        if(!$customer) return 0;
        $sharingTools = $this->getSharingTools();
        $group = ($action == 'facebook_share') ? 'facebook' : $action;
        $code = ($action == 'facebook_share') ? '_share' : '';
        $pointEarn = $this->getSocialConfig($group, 'point_earn' . $code);
        $pointLimit = $this->getSocialConfig($group, 'point_limit' . $code);
        $totalEarnPerDay = $this->getAmountofDay($sharingTools[$action], $customer->getId());
        return max(0, min($pointLimit - $totalEarnPerDay, $pointEarn));
    }

    /**
     * get amout poit of action now
     * @param type $action
     * @param type $customer_id
     * @return type
     */
    public function getAmountofDay($action, $customer_id, $create_time = null) {
        $date = date('Y-m-d');
        $datas = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('customer_id', $customer_id)
                ->addFieldToFilter('status', array('in' => array(Magestore_RewardPoints_Model_Transaction::STATUS_COMPLETED, Magestore_RewardPoints_Model_Transaction::STATUS_PENDING,Magestore_RewardPoints_Model_Transaction::STATUS_ON_HOLD)));
        $datas->getSelect()
                ->columns(array(
                    'total' => new Zend_Db_Expr("IFNULL(SUM(main_table.point_amount), '')")))
                ->group('action');
        if ($create_time != null){
            $datas->getSelect()->where('extra_content LIKE ?', '%create_time=' . $create_time . '%');
        } else {
            $datas->getSelect()->where('(date(created_time) = date(?))', $date);
        }
        if($datas->getSize() > 0){
            return $datas->getFirstItem()->getTotal();
        } else {
            return 0;
        }
    }

    /**
     * get amout point of social
     * @param type $action
     * @param type $customer_id
     * @return type
     */
    public function getSocialCreated($action, $customer_id) {
        $date = date('Y-m-d');
        $datas = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('customer_id', $customer_id)
                ->setOrder('created_time', 'DESC');
//        $datas->getSelect()->where('(date(created_time) = date(?))', $date)
//                ->order('main_table.created_time DESC');
        $data = $datas->getFirstItem()->getCreatedTime();
        return $data;
    }

    /**
     * get point earned of customer via social
     * @param type $action
     * @param type $customer_id
     * @param type $link
     * @return type
     */
    public function getSocialEarned($action, $customer_id, $link) {
        $data = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', $action)
                ->addFieldToFilter('extra_content', $link)
                ->addFieldToFilter('customer_id', $customer_id);
        return $data;
    }

    public function earnedLoginToday($customer_id) {
        $localDate = Mage::getModel('core/date')->date('Y-m-d');
        $data = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'login')
                ->addFieldToFilter('customer_id', $customer_id);
        $data->getSelect()->where('date(extra_content) = date(?)',$localDate);
        return count($data);
    }
 
   /**
     * Encrypt data
     *
     * @param  mixed $data Data to encrypt
     * @return string
     */
    public function encrypt(array $data)
    {
        $json = json_encode($data);
        $encode = base64_encode($json);
        $encode = str_replace(array('+', '/'), array('@', '$'), $encode);
        return $encode;
    }
 
    /**
     * Decrypt data
     *
     * @param  mixed $data Data to decrypt
     * @return object
     */
    public function decrypt($data)
    {
        $data = str_replace(array('@', '$'), array('+', '/'), $data);
        $json = rtrim(base64_decode($data), "\0");
        return json_decode($json, true);
    }
}
