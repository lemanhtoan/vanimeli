<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Index
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * RewardPointsRule Product Indexer Model
 * Predefine list of methods required by indexer
 */
class Magestore_RewardPointsRule_Model_Indexer_Product extends Mage_Index_Model_Indexer_Abstract
{
    protected $_cacheRule = array();
    
    /**
     * Data key for matching result to be saved in
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION
        )
    );
    
    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('rewardpointsrule')->__('Earning Points Product');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('rewardpointsrule')->__('Index point earning for Products');
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        //$entity = $event->getDataObject();
        //$event->setDataObject($this->getIndexProduct($entity));
    }

    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getDataObject();
        foreach ($this->getIndexProduct($entity) as $data){
            $product = Mage::getModel('rewardpointsrule/earning_product')->getCollection();
            $product->addFieldToFilter('customer_group_id', $data['customer_group_id'])
                    ->addFieldToFilter('website_id', $data['website_id'])
                    ->addFieldToFilter('product_id', $data['product_id']);
            $item = $product->getFirstItem();
            $item->addData($data)
                 ->save();
        }
    }

    public function reindexAll() {
        // reindex all data
        Mage::getModel('rewardpointsrule/earning_product')->applyAll();
        if(Mage::getStoreConfig('rewardpointsrule/indexmanagement/flag')){
            Mage::getModel('core/config')->saveConfig('rewardpointsrule/indexmanagement/flag', 0);
        }
    }
    
    
    /**
     * index product is use to index data when save product
     * 
     * @param type $product
     * @return mixed
     */
    public function getIndexProduct($product)
    {
        $indexEntitys = array();
        if($product->getRewardpointsSpend())
            return $indexEntitys;
        try {
            if(is_numeric($product))
            {
                $product = Mage::getModel('catalog/product')->load($product);
            }
            if(is_object($product))
            {
                $ids = $this->prepareIds();
                foreach($ids as $id){
                    $points = $this->getPointsOnRules($product, $id['customerGroupId'], $id['websiteId']);
                    if ($points['points'] < 1) continue;
                    $indexEntitys[] = array(
                        'customer_group_id' => $id['customerGroupId'],
                        'website_id'        => $id['websiteId'],
                        'product_id'        => $product->getId(),
                        'rule_ids'          => $points['ruleIds'],
                        'earning_point'     => $points['points']
                    );
                }
                
            }
        } catch(Exception $e){
            Mage::logException($e);
        }
        return $indexEntitys;
    }
    
    /**
     * this function is get ids of customerGroupd and website
     * 
     * @return array
     */
    protected function prepareIds()
    {
        if ($this->hasCache('customer_website_ids')) {
            return $this->getCache('customer_website_ids');
        }
        $data = array();
        //get id
        $websiteIds = array();
        foreach (Mage::app()->getWebsites() as $website) {
            $websiteIds[] = $website->getId();
        }
        $customerGroupIds = array();
        foreach (Mage::getResourceModel('customer/group_collection') as $customerGroup) {
            $customerGroupIds[] = $customerGroup->getData('customer_group_id');
        }
        foreach ($websiteIds as $webId){
            foreach ($customerGroupIds as $cusGrpId){
                $data[] = array('websiteId' => $webId, 'customerGroupId' => $cusGrpId);
            }
        }
        //Zend_Debug::dump($data); die;
        $this->saveCache('customer_website_ids', $data);
        return $data;
    }
    
    /**
     * get Points on rule for indexer
     * 
     * @param type $product
     * @param type $customerGroupId
     * @param type $websiteId
     * @param type $date
     * @return array
     */
    protected function getPointsOnRules($product, $customerGroupId, $websiteId, $date = null)
    {
        if (!is_object($product) and is_numeric($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        if(!$product->getFinalPrice())
		{
			$product = Mage::getModel('catalog/product')->load($product->getId());
		}
        $ruleIds = '';
        $points = 0;
        
        $rules = $this->getRuleCollection($customerGroupId, $websiteId, $date);
        foreach ($rules as $rule) {
            if ($rule->validate($product)) {
                $ruleIds .= $rule->getId().',';
                $product->setStoreId(Mage::app()->getDefaultStoreView()->getId());//Hai.Tran 11/11/2013 fix finalPrice
                $point = Mage::helper('rewardpointsrule/calculation_earning')
                    ->calcCatalogPoint(
                        $rule->getSimpleAction(),
                        $rule->getPointsEarned(),
//                        $product->getFinalPrice(),
//                        $product->getFinalPrice() - $product->getCost(),
                        Mage::helper('tax')->getPrice($product, $product->getFinalPrice(),null,null,null, null,Mage::app()->getDefaultStoreView()),
                        Mage::helper('tax')->getPrice($product, $product->getFinalPrice(),null,null,null, null,Mage::app()->getDefaultStoreView()) - $product->getCost(),
                        $rule->getMoneyStep(),
                        $rule->getMaxPointsEarned()
                );
                $points += $point;
                
                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
        $ruleIds = substr($ruleIds, 0, -1);
        return array('points' => $points, 'ruleIds' => $ruleIds);
    }
    
    public function getRuleCollection($customerGroupId, $websiteId, $date = null) {
        $collectionKey = "catalog_earning_product_collection:$customerGroupId:$websiteId";
        if (!$this->hasCache($collectionKey)) {
            $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->setAvailableFilter($customerGroupId, $websiteId, $date);
            foreach ($rules as $rule) {
                $rule->afterLoad();
            }
            $this->saveCache($collectionKey, $rules);
        }
        return $this->getCache($collectionKey);
    }
    
    
    /**
     * check cache is existed or not
     * 
     * @param string $cacheKey
     * @return boolean
     */
    protected function hasCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_cacheRule)) {
            return true;
        }
        return false;
    }
    
    /**
     * save value to cache
     * 
     * @param string $cacheKey
     * @param mixed $value
     * @return Magestore_RewardPoints_Helper_Calculation_Abstract
     */
    protected function saveCache($cacheKey, $value = null)
    {
        $this->_cacheRule[$cacheKey] = $value;
        return $this;
    }
    
    /**
     * get cache value by cache key
     * 
     * @param  $cacheKey
     * @return mixed
     */
    protected function getCache($cacheKey)
    {
        if (array_key_exists($cacheKey, $this->_cacheRule)) {
            return $this->_cacheRule[$cacheKey];
        }
        return null;
    }
}

