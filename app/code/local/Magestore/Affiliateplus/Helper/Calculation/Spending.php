<?php

class Magestore_Affiliateplus_Helper_Calculation_Spending extends Mage_Core_Helper_Abstract {

    /**
     * Changed By Adam 06/11/2014: Fix bug hidden tax
     * pre collect total for quote/address and return quote total
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param null|Mage_Sales_Model_Quote_Address $address
     * @return float
     */
    public function getQuoteBaseTotal($quote, $address = null) {
        $cacheKey = 'quote_base_total';
        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }
        if (is_null($address)) {
            if ($quote->isVirtual()) {
                $address = $quote->getBillingAddress();
            } else {
                $address = $quote->getShippingAddress();
            }
        }
        $baseTotal = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item->getParentItemId())
                continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseTotal += $item->getQty() * ($child->getQty() * $this->_getItemBasePrice($child)) - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                }
            } elseif ($item->getProduct()) {
                $baseTotal += $item->getQty() * $this->_getItemBasePrice($item) - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
            }
        }
//        if (Mage::getStoreConfig(self::XML_PATH_SPEND_FOR_SHIPPING, $quote->getStoreId())) {
//            $shippingAmount = $address->getShippingAmountForDiscount();
//            if ($shippingAmount !== null) {
//                $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
//            } else {
//                $baseShippingAmount = $address->getBaseShippingAmount();
//            }
//            $baseTotal += $baseShippingAmount - $address->getBaseShippingDiscountAmount() - $address->getMagestoreBaseDiscountForShipping();
//        }
        $this->saveCache($cacheKey, $baseTotal);
        return $baseTotal;
    }

    /**
     * 
     * @param type $item
     * @return float
     */
    public function _getItemBasePrice($item) {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Cache helper data to Memory
     * 
     * @var array
     */
    protected $_cacheRule = array();

    /**
     * check cache is existed or not
     * 
     * @param string $cacheKey
     * @return boolean
     */
    public function hasCache($cacheKey) {
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
     * @return 
     */
    public function saveCache($cacheKey, $value = null) {
        $this->_cacheRule[$cacheKey] = $value;
        return $this;
    }

    /**
     * get cache value by cache key
     * 
     * @param  $cacheKey
     * @return mixed
     */
    public function getCache($cacheKey) {
        if (array_key_exists($cacheKey, $this->_cacheRule)) {
            return $this->_cacheRule[$cacheKey];
        }
        return null;
    }

}
