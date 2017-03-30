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
 * RewardPoints Calculator Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Calculator extends Mage_Core_Helper_Abstract {

    const XML_PATH_ROUNDING_METHOD = 'rewardpoints/earning/rounding_method';

    /**
     * Rounding number by reward points configuration
     * 
     * @param mixed $number
     * @param mixed $store
     * @return int
     */
    public function round($number, $store = null) {
        switch (Mage::getStoreConfig(self::XML_PATH_ROUNDING_METHOD, $store)) {
            case 'floor':
                return floor($number);
            case 'ceil':
                return ceil($number);
        }
        return round($number);
    }

    /**
     * Calculate price including tax or excluding tax
     * 
     * @param type $product
     * @param type $price
     * @param type $includingTax return type (includingTax of excludingTax)
     * @param type $item
     * @return type price
     */
//    public function getPrice($product, $price, $includingTax = null, $item = false) {
//        if (!$price) {
//            return $price;
//        }
//        $store = Mage::app()->getStore();
//
//        if ($item)
//            $priceIncludingTax = false;
//        else
//            $priceIncludingTax = Mage::getSingleton('tax/config')->priceIncludesTax($store);
//
//        if (($priceIncludingTax && $includingTax) || (!$priceIncludingTax && !$includingTax)) {
//            return $price;
//        }
//
//        $percent = $product->getTaxPercent();
//        $includingPercent = null;
//
//        $taxClassId = $product->getTaxClassId();
//        if (is_null($percent)) {
//            if ($taxClassId) {
//                $request = Mage::getSingleton('tax/calculation')
//                        ->getRateRequest(null, null, null, $store);
//                $percent = Mage::getSingleton('tax/calculation')
//                        ->getRate($request->setProductClassId($taxClassId));
//            }
//        }
//        $product->setTaxPercent($percent);
//        if ($includingTax && !$priceIncludingTax) {
//            $price = $this->_calculatePrice($price, $percent, true);
//        } else {
//            $price = $this->_calculatePrice($price, $percent, false);
//        }
//        return $store->roundPrice($price);
//    }
    public function getPrice($product, $price, $includingTax = null, $item = false) {
        if (!$price) {
            return $price;
        }
        $store = Mage::app()->getStore();

        if ($item)
            $priceIncludingTax = false;
        else
            $priceIncludingTax = Mage::getSingleton('tax/config')->priceIncludesTax($store);

        if (($priceIncludingTax && $includingTax) || (!$priceIncludingTax && !$includingTax)) {
            return $price;
        }

        $percent = $product->getTaxPercent();
        $includingPercent = null;

        $taxClassId = $product->getTaxClassId();
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = Mage::getSingleton('tax/calculation')
                        ->getRateRequest(null, null, null, $store);
                $percent = Mage::getSingleton('tax/calculation')
                        ->getRate($request->setProductClassId($taxClassId));
            }
        }
        if ($taxClassId && $priceIncludingTax) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $store);
            $includingPercent = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
        }
        if ($percent === false || is_null($percent) || $percent == 0) {
            if ($priceIncludingTax && !$includingPercent) {
                return $price;
            }
        }
        $product->setTaxPercent($percent);
        if ($includingTax && !$priceIncludingTax) {
            $price = $this->_calculatePrice($price, $percent, true);
        } else {
            if ($includingPercent != $percent) {
                $price = $this->_calculatePrice($price, $includingPercent, false);
                if ($percent != 0) {
                    $price = Mage::getSingleton('tax/calculation')->round($price);
                    $price = $this->_calculatePrice($price, $percent, true);
                }
            } else
                $price = $this->_calculatePrice($price, $percent, false);
        }
        return $store->roundPrice($price);
    }

    /**
     * @param $price
     * @param $percent
     * @param $type
     * @return mixed
     */
    protected function _calculatePrice($price, $percent, $type) {
        $calculator = Mage::getSingleton('tax/calculation');
        if ($type) {
            $taxAmount = $calculator->calcTaxAmount($price, $percent, false, false);
            return $price + $taxAmount;
        } else {
            $taxAmount = $calculator->calcTaxAmount($price, $percent, true, false);
            return $price - $taxAmount;
        }
    }

}
