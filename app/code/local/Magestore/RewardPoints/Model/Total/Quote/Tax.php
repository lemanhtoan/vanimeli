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
 * Magestore Rewrite to caculate taxt for discount
 * 
 * @category    Magestore
 * @package     Magestore_Magestore
 * @author      Magestore Developer
 */
class Magestore_Rewardpoints_Model_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    
    /**
     * Calculate tax for Quote (total)
     * 
     * @param type $item
     * @param type $rate
     * @param type $taxGroups
     * @return Magestore_Magestore_Model_Total_Quote_Tax
     */
    protected function _aggregateTaxPerRate($item, $rate, &$taxGroups) {
        $discount       = $item->getDiscountAmount();
        $baseDiscount   = $item->getBaseDiscountAmount();
        if($item->getIsPriceInclTax()){
            $item->setMagestoreDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreDiscount(), $rate, false, false));
            $item->setMagestoreBaseDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreBaseDiscount(), $rate, false, false));
        }
        $item->setDiscountAmount($discount + $item->getMagestoreDiscount() + $item->getMagestoreDiscountTax());
        $item->setBaseDiscountAmount($baseDiscount + $item->getMagestoreBaseDiscount() + $item->getMagestoreBaseDiscountTax());
        
        parent::_aggregateTaxPerRate($item, $rate, $taxGroups);
        
        $afterDiscount = (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $this->_store);
        if($item->getIsPriceInclTax() && $afterDiscount){
            foreach ($this->_hiddenTaxes as $key => $taxInfoItem) {
                if (isset($taxInfoItem['item']) && $item->getId() == $taxInfoItem['item']->getId() && $taxInfoItem['value'] >= $item->getMagestoreDiscountTax() && $item->getMagestoreDiscountTax() >0) {
                    $this->_hiddenTaxes[$key]['value'] = $taxInfoItem['value'] - $item->getMagestoreDiscountTax();
                    $this->_hiddenTaxes[$key]['base_value'] = $taxInfoItem['base_value'] - $item->getMagestoreBaseDiscountTax();
                    break;
                }
            }
            //fix 1.4
            if($item->getHiddenTaxAmount()){
                $item->setHiddenTaxAmount($item->getHiddenTaxAmount() - $item->getMagestoreDiscountTax());
                $item->setBaseHiddenTaxAmount($item->getBaseHiddenTaxAmount() - $item->getMagestoreBaseDiscountTax());
            }
        }
        
        $item->setDiscountAmount($discount);
        $item->setBaseDiscountAmount($baseDiscount);
        return $this;
    }
    
    /**
     * Calculate tax for each product
     * 
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param type $rate
     * @return Magestore_Magestore_Model_Total_Quote_Tax
     */
    protected function _calcUnitTaxAmount(Mage_Sales_Model_Quote_Item_Abstract $item, $rate) {
        $discount       = $item->getDiscountAmount();
        $baseDiscount   = $item->getBaseDiscountAmount();
        if($item->getIsPriceInclTax()){
            $item->setMagestoreDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreDiscount(), $rate, false, false));
            $item->setMagestoreBaseDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreBaseDiscount(), $rate, false, false));
        }
        $item->setDiscountAmount($discount + $item->getMagestoreDiscount() + $item->getMagestoreDiscountTax());
        $item->setBaseDiscountAmount($baseDiscount + $item->getMagestoreDiscount() + $item->getMagestoreBaseDiscountTax());
        
        parent::_calcUnitTaxAmount($item, $rate);
        
        $afterDiscount = (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $this->_store);
        if($item->getIsPriceInclTax() && $afterDiscount){
            foreach ($this->_hiddenTaxes as $key => $taxInfoItem) {
                if (isset($taxInfoItem['item']) && $item->getId() == $taxInfoItem['item']->getId() && $taxInfoItem['value'] >= $item->getMagestoreDiscountTax() && $item->getMagestoreDiscountTax() >0) {
                    $this->_hiddenTaxes[$key]['value'] = $taxInfoItem['value'] - $item->getMagestoreDiscountTax();
                    $this->_hiddenTaxes[$key]['base_value'] = $taxInfoItem['base_value'] - $item->getMagestoreBaseDiscountTax();
                    break;
                }
            }
            //fix 1.4
            if($item->getHiddenTaxAmount()){
                $item->setHiddenTaxAmount($item->getHiddenTaxAmount() - $item->getMagestoreDiscountTax());
                $item->setBaseHiddenTaxAmount($item->getBaseHiddenTaxAmount() - $item->getMagestoreBaseDiscountTax());
            }
        }
        
        $item->setDiscountAmount($discount);
        $item->setBaseDiscountAmount($baseDiscount);
        return $this;
    }
    
    /**
     * Calculate tax for each item
     * 
     * @param type $item
     * @param type $rate
     * @return Magestore_Magestore_Model_Total_Quote_Tax
     */
    protected function _calcRowTaxAmount($item, $rate) {
        $discount       = $item->getDiscountAmount();
        $baseDiscount   = $item->getBaseDiscountAmount();
        if($item->getIsPriceInclTax()){
            $item->setMagestoreDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreDiscount(), $rate, false, false));
            $item->setMagestoreBaseDiscountTax($this->_calculator->calcTaxAmount($item->getMagestoreBaseDiscount(), $rate, false, false));
        }
        $item->setDiscountAmount($discount + $item->getMagestoreDiscount() + $item->getMagestoreDiscountTax());
        $item->setBaseDiscountAmount($baseDiscount + $item->getMagestoreDiscount() + $item->getMagestoreBaseDiscountTax());
        
        parent::_calcRowTaxAmount($item, $rate);
        
        $afterDiscount = (bool)Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $this->_store);
        if($item->getIsPriceInclTax() && $afterDiscount){
            foreach ($this->_hiddenTaxes as $key => $taxInfoItem) {
                if (isset($taxInfoItem['item']) && $item->getId() == $taxInfoItem['item']->getId() && $taxInfoItem['value'] >= $item->getMagestoreDiscountTax() && $item->getMagestoreDiscountTax() >0) {
                    $this->_hiddenTaxes[$key]['value'] = $taxInfoItem['value'] - $item->getMagestoreDiscountTax();
                    $this->_hiddenTaxes[$key]['base_value'] = $taxInfoItem['base_value'] - $item->getMagestoreBaseDiscountTax();
                    break;
                }
            }
            //fix 1.4
            if($item->getHiddenTaxAmount()){
                $item->setHiddenTaxAmount($item->getHiddenTaxAmount() - $item->getMagestoreDiscountTax());
                $item->setBaseHiddenTaxAmount($item->getBaseHiddenTaxAmount() - $item->getMagestoreBaseDiscountTax());
            }
        }
        
        $item->setDiscountAmount($discount);
        $item->setBaseDiscountAmount($baseDiscount);
        return $this;
    }
    
    /**
     * Calculate tax for shipping amount
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @param type $taxRateRequest
     */
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest) {
        $discount       = $address->getShippingDiscountAmount();
        $baseDiscount   = $address->getBaseShippingDiscountAmount();
        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        if($address->getIsShippingInclTax()){
            $address->setMagestoreDiscountTaxForShipping($this->_calculator->calcTaxAmount($address->getMagestoreDiscountForShipping(), $this->_calculator->getRate($taxRateRequest), false, false));
            $address->setMagestoreBaseDiscountTaxForShipping($this->_calculator->calcTaxAmount($address->getMagestoreBaseDiscountForShipping(), $this->_calculator->getRate($taxRateRequest), false, false));
        }
        $address->setShippingDiscountAmount($discount+$address->getMagestoreDiscountForShipping()+$address->getMagestoreDiscountTaxForShipping());
        $address->setBaseShippingDiscountAmount($baseDiscount+$address->getMagestoreBaseDiscountForShipping()+$address->getMagestoreBaseDiscountTaxForShipping());
        
        parent::_calculateShippingTax($address, $taxRateRequest);
        
        if($address->getIsShippingInclTax() && $address->getMagestoreDiscountTaxForShipping() > 0){
            $length = count($this->_hiddenTaxes);
            if($this->_hiddenTaxes[$length-1]['value']>0){
                $this->_hiddenTaxes[$length-1]['value'] = $this->_hiddenTaxes[$length-1]['value'] - $address->getMagestoreDiscountTaxForShipping();
                $this->_hiddenTaxes[$length-1]['base_value'] = $this->_hiddenTaxes[$length-1]['base_value'] - $address->getMagestoreBaseDiscountTaxForShipping();
            }
            
            //fix 1.4
            if($address->getShippingHiddenTaxAmount()){
                $address->setShippingHiddenTaxAmount($address->getShippingHiddenTaxAmount() - $address->getMagestoreDiscountTaxForShipping());
                $address->setBaseShippingHiddenTaxAmount($address->getBaseShippingHiddenTaxAmount() - $address->getMagestoreBaseDiscountTaxForShipping());
            }
        }
        
        $address->setShippingDiscountAmount($discount);
        $address->setBaseShippingDiscountAmount($baseDiscount);
        return $this;
    }
}
