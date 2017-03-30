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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Product Earning Mysql4 Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsRule
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Model_Mysql4_Earning_Product extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('rewardpointsrule/earning_product', 'id');
    }

    /**
     * this function is use to update data in table rewardpoints_earning_product
     *      
     * @return \Magestore_RewardPointsRule_Model_Mysql4_Earning_Product
     * @throws Exception
     */
    public function updateEarningProduct() {
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        //delete all
        $write->delete($this->getTable('rewardpointsrule/earning_product'));
        $write->commit();

        $rows = array();
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS); //Hai.Tran 11/11/2013 fix finalPrice
        $catalog_product = Mage::getResourceModel('catalog/product_collection');
        // Prepare Catalog Product Collection to used for Rules
        $catalog_product->addAttributeToSelect('price')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect('tax_class_id'); //Hai.Tran 14/11/2013

        $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->addFieldToFilter('is_active', 1);
        $rules->getSelect()
                ->where("(from_date IS NULL) OR (DATE(from_date) <= ?)", now(true))
                ->where("(to_date IS NULL) OR (DATE(to_date) >= ?)", now(true));
        foreach ($rules as $rule) {
            $rule->afterLoad();
            $rule->getConditions()->collectValidatedAttributes($catalog_product);
        }

        try {
            foreach ($catalog_product as $product) {
                $datas = Mage::getSingleton('rewardpointsrule/indexer_product')
                        ->getIndexProduct($product);

                foreach ($datas as $data) {
                    $rows[] = $data;
                }
                if (count($rows) == 1000) {
                    $write->insertMultiple($this->getTable('rewardpointsrule/earning_product'), $rows);
                    $rows = array();
                }
            }
            if (!empty($rows)) {
                $write->insertMultiple($this->getTable('rewardpointsrule/earning_product'), $rows);
            }
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }
        return $this;
    }
    
     /**
     * public _getWriteAdapter function
     *      
     * @return function     
     */
     public function getWriteAdapter() {
          return $this->_getWriteAdapter();          
     }  


}
