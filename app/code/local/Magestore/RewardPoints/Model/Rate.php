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
 * Rewardpoints Rate Information Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Rate extends Mage_Core_Model_Abstract
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * Rate direction - Spending Point
     */
    const POINT_TO_MONEY = 1;
    
    /**
     * Rate direction - Earning Point
     */
    const MONEY_TO_POINT = 2;
    
    /**
     * Redefine event Prefix, event object
     * 
     * @var string
     */
    protected $_eventPrefix = 'rewardpoints_rate';
    protected $_eventObject = 'rewardpoints_rate';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/rate');
    }
    
    /**
     * prepare customer group and website for save to database
     * 
     * @return Magestore_RewardPoints_Model_Rate
     */
    protected function _beforeSave()
    {
        if (is_array($this->getWebsiteIds())) {
            $this->setWebsiteIds(implode(',', $this->getWebsiteIds()));
        }
		if(!($this->getWebsiteIds())){
            $this->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
		}
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(implode(',', $this->getCustomerGroupIds()));
        }
        return parent::_beforeSave();
    }
    
    /**
     * get all spending direction rate (hash array)
     * 
     * @return array
     */
    public function getSpendingDirectionHash()
    {
        $directionObj = new Varien_Object(array(
            'direction' => array(
                self::POINT_TO_MONEY    => Mage::helper('rewardpoints')->__('Get discount for spending points'),
            )
        ));
        Mage::dispatchEvent($this->_eventPrefix . '_get_spending_direction', array(
            'direction' => $directionObj,
        ));
        return $directionObj->getData('direction');
    }
    
    /**
     * get all earning direction rate (hash array)
     * 
     * @return array
     */
    public function getEarningDirectionHash()
    {
        $directionObj = new Varien_Object(array(
            'direction' => array(
                self::MONEY_TO_POINT    => Mage::helper('rewardpoints')->__('Earn points for purchasing order'),
            )
        ));
        Mage::dispatchEvent($this->_eventPrefix . '_get_earning_direction', array(
            'direction' => $directionObj,
        ));
        return $directionObj->getData('direction');
    }
    
    /**
     * convert hash array to pair (value - label) array
     * 
     * @param array $directionHash
     * @return array
     */
    public function getDirectionArray($directionHash = array())
    {
        $options = array();
        foreach ($directionHash as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
    
    /**
     * get Rate by direction
     * 
     * @param type $direction
     * @param type $customerGroupId 
     * @param type $websiteId
     * @return false | Magestore_RewardPoints_Model_Rate
     */
    public function getRate($direction = 1, $customerGroupId = null, $websiteId = null)
    {
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        $rateCollection = $this->getCollection()
            ->addFieldToFilter('direction', $direction)
            ->addFieldToFilter('website_ids', array('finset' => $websiteId))
            ->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
            ->addFieldToFilter('points', array('gt' => 0))
            ->addFieldToFilter('status', array('eq' => self::STATUS_ACTIVE))
            ->addFieldToFilter('money', array('gt' => 0));
        $rateCollection->getSelect()->order('sort_order DESC');
        $rateCollection->getSelect()->order('rate_id DESC');
        $rate = $rateCollection->getFirstItem();
        if ($rate && $rate->getId()) {
            return $rate;
        }
        return false;
    }
    
    /**
     * get all spending rates
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Rate_Collection
     */
    public function getSpendingRates()
    {
        $spendingDirection = array_keys($this->getSpendingDirectionHash());
        return $this->getCollection()->addFieldToFilter('direction', array('in' => $spendingDirection));
    }
    
    /**
     * get all earning rates
     * 
     * @return Magestore_RewardPoints_Model_Mysql4_Rate_Collection
     */
    public function getEarningRates()
    {
        $earningDirection = array_keys($this->getEarningDirectionHash());
        return $this->getCollection()->addFieldToFilter('direction', array('in' => $earningDirection));
    }
}
