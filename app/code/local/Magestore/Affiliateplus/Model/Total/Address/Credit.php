<?php

class Magestore_Affiliateplus_Model_Total_Address_Credit extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('affiliateplus_credit');
    }

    /**
     * get Config Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return $this;
        if (!$this->_getConfigHelper()->getPaymentConfig('store_credit')) {
            return $this;
        }
        // Changed By Adam 26/05/2015: fix issue cannot use store credit to the virtual product, downloadable product
        $quote = $address->getQuote();
        if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
            return $this;
        }
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }
        $discount = 0;
        $session = Mage::getSingleton('checkout/session');
        $helper = Mage::helper('affiliateplus/account');
        if ($session->getUseAffiliateCredit() && $helper->isLoggedIn() && !$helper->disableStoreCredit() && $helper->isEnoughBalance()) {
//            Changed By Adam 02/05/2015: Fix loi khong quy doi tien te khi su dung store credit
//            $balance = $helper->getAccount()->getBalance();
            $balance = Mage::app()->getStore()->convertPrice($helper->getAccount()->getBalance());
            $discount = floatval($session->getAffiliateCredit());
            if ($discount > $balance) {
                $discount = $balance;
            }
            if ($discount > $address->getGrandTotal()) {
                $discount = $address->getGrandTotal();
            }
            if ($discount < 0) {
                $discount = 0;
            }
            $session->setAffiliateCredit($discount);
        } else {
            $session->setUseAffiliateCredit('');
        }
        
        /* hainh add discount calculate with incl or excl tax variable 22-04-2014 */
        $discount_include_tax = false;
        if ((int) (Mage::getStoreConfig('tax/calculation/discount_tax', $address->getQuote()->getStore())) == 1)
            $discount_include_tax = true;

        if ($discount) {
            /* Changed By Adam 22/05/2015 */
            $baseItemsPrice = 0;
            $items = $address->getAllItems();
            if (!count($items)){
                return $this;
            }
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        /* Changed By Adam 08/10/2014 */
                        if (!$discount_include_tax)
                            $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                        else
                            $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                    }
                } elseif ($item->getProduct()) {
                    /* Changed By Adam 08/10/2014 */
                    if (!$discount_include_tax)
                        $baseItemsPrice += $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                    else
                        $baseItemsPrice += $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                }
            }
            if ($baseItemsPrice) {
                $totalBaseDiscount = min($discount, $baseItemsPrice);
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            /* Changed By Adam 08/10/2014 */
                            if (!$discount_include_tax)
                                $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                            else
                                $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getRewardpointsBaseDiscount());
                            $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                            $child->setBaseAffiliateplusCredit($childBaseDiscount)
                                    ->setAffiliateplusCredit(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                        }
                    } elseif ($item->getProduct()) {
                        /* Changed By Adam 08/10/2014 */
                        if (!$discount_include_tax)
                            $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();
                        else
                            $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getRewardpointsBaseDiscount();

                        $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                        $item->setBaseAffiliateplusCredit($itemBaseDiscount)
                                ->setAffiliateplusCredit(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                    }
                }
            }

            $baseDiscount = $discount / Mage::app()->getStore()->convertPrice(1);
            $address->setBaseAffiliateCredit(-$baseDiscount);
            $address->setAffiliateCredit(-$discount);

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        if ($amount = $address->getAffiliateCredit()) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->_getConfigHelper()->__('Paid by Affiliate Credit'),
                'value' => $amount,
            ));
        }
        return $this;
    }

}
