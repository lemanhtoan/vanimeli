<?php

class Magestore_Affiliateplus_Model_Total_Address_Affiliateplus extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('affiliateplus');
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

        // Changed By Adam 07/10/2015: Fixed issue calculate discount when purchase recurring product
        if($address->getSubtotal() == 0) return $this;
        
        /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        if (!$applyTaxAfterDiscount) {
            return $this;
        }

        /** @var $helper Magestore_Affiliateplus_Helper_Calculation_Spending */
        $helper = Mage::helper('affiliateplus/calculation_spending');
        $baseTotal = $helper->getQuoteBaseTotal($quote, $address);
        // Endcode

        /* hainh add discount calculate with incl or excl tax variable 22-04-2014 */
        $discount_include_tax = false;
        if ((int) (Mage::getStoreConfig('tax/calculation/discount_tax', $address->getQuote()->getStore())) == 1)
            $discount_include_tax = true;
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'product') {
            return $this;
        }
        if ($this->_getConfigHelper()->getDiscountConfig('allow_discount') == 'system') {
            $appliedRuleIds = array();
            if (is_string($address->getAppliedRuleIds())) {
                $appliedRuleIds = explode(',', $address->getAppliedRuleIds());
                $appliedRuleIds = array_filter($appliedRuleIds);
            }
            if (count($appliedRuleIds)) {
                return $this;
            }
        }
        $items = $address->getAllItems();
        if (!count($items))
            return $this;
        /* edit by Jack - get all information from order editing */
        $session = Mage::getSingleton('checkout/session');
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
        /* get data processed */
        $dataProcessing = Mage::helper('affiliateplus')->processDataWhenEditOrder();
        if (isset($dataProcessing['current_couponcode']))
            $currentCouponCode = $dataProcessing['current_couponcode'];
        if (isset($dataProcessing['base_affiliate_discount']))
            $baseAffiliateDiscount = $dataProcessing['base_affiliate_discount'];
        if (isset($dataProcessing['customer_id']))
            $customerId = $dataProcessing['customer_id'];
        if (isset($dataProcessing['default_discount']))
            $defaultDiscount = $dataProcessing['default_discount'];
        /* */
        $couponCodeBySession = $session->getAffiliateCouponCode();
        $isAllowUseCoupon = Mage::helper('affiliateplus')->isAllowUseCoupon($couponCodeBySession);
        if (!$isAllowUseCoupon || !Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            $session->unsAffiliateCouponCode();
        $isEnableLiftime = Mage::getStoreConfig('affiliateplus/commission/life_time_sales');
        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered() == 1 && !$session->getData('affiliate_coupon_code') && isset($currentCouponCode) && $currentCouponCode != '') {
            return $this;
        } else if ($isEnableLiftime == 0 && Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1 && !$session->getData('affiliate_coupon_code') && isset($currentCouponCode) && $currentCouponCode != '') {
            return $this;
        }

        $baseDiscount = 0;
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $discountObj = new Varien_Object(array(
            'affiliate_info' => $affiliateInfo,
            'base_discount' => $baseDiscount,
            'default_discount' => true,
            'discounted_products' => array(),
            'discounted_items' => array(),
            'program' => '',
        ));
        if (!isset($customerId))
            $customerId = '';
        /* add new event to calculate commission when edit order - Edit By Jack */
        if (Mage::helper('affiliateplus')->isAdmin()) {
            Mage::dispatchEvent('affiliateplus_address_collect_total_edit', array(
                'address' => $address,
                'discount_obj' => $discountObj,
            ));
        }
        /* end add new event  */ else {
            Mage::dispatchEvent('affiliateplus_address_collect_total', array(
                'address' => $address,
                'discount_obj' => $discountObj,
            ));
        }
        $baseDiscount = $discountObj->getBaseDiscount();
        if ($discountObj->getDefaultDiscount()) {
            $account = '';
            foreach ($affiliateInfo as $info)
                if ((isset($info['account']) && $info['account']))
                    $account = $info['account'];
            if (((isset($defaultDiscount) && $defaultDiscount && !$couponCodeBySession && Mage::helper('affiliateplus')->isAdmin() )) || (isset($dataProcessing['program_name']) && $dataProcessing['program_name'] == 'Affiliate Program' && Mage::helper('affiliateplus')->isAdmin()) || ($discountObj->getProgram() == 'Affiliate Program') || ($account && $account->getId()) || (isset($baseAffiliateDiscount) && $baseAffiliateDiscount)) {
                $currentStoreId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                if (!$currentStoreId)
                    $currentStoreId = null;
                $discountType = $this->_getConfigHelper()->getDiscountConfig('discount_type', $currentStoreId);
                $discountValue = floatval($this->_getConfigHelper()->getDiscountConfig('discount', $currentStoreId));
                if (($orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1) || (!$orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered())) {
                    if ($this->_getConfigHelper()->getDiscountConfig('use_secondary', $currentStoreId)) {
                        $discountType = $this->_getConfigHelper()->getDiscountConfig('secondary_type', $currentStoreId);
                        $discountValue = floatval($this->_getConfigHelper()->getDiscountConfig('secondary_discount', $currentStoreId));
                    }
                }
                $discountedItems = $discountObj->getDiscountedItems();
                $discountedProducts = $discountObj->getDiscountedProducts();
                if ($discountValue <= 0) {
                    // do nothing when no discount
                } elseif ($discountType == 'cart_fixed') {
                    $baseItemsPrice = 0;
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                /* Changed By Adam 08/10/2014 */
                                if (!$discount_include_tax)
                                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                else
                                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                            }
                        } elseif ($item->getProduct()) {
                            /* Changed By Adam 08/10/2014 */
                            if (!$discount_include_tax)
                                $baseItemsPrice += $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            else
                                $baseItemsPrice += $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                        }
                    }
                    if ($baseItemsPrice) {
                        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                        foreach ($items as $item) {
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            if (in_array($item->getProductId(), $discountedProducts) && Mage::helper('affiliateplus')->isAdmin()) {
                                continue;
                            }
                            if (in_array($item->getId(), $discountedItems) && !Mage::helper('affiliateplus')->isAdmin()) {
                                continue;
                            }
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                foreach ($item->getChildren() as $child) {
                                    /* Changed By Adam 08/10/2014 */
                                    if (!$discount_include_tax)
                                        $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                    else
                                        $price = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                            ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount))
                                            // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                            // ->setRowTotal($child->getRowTotal() - $childBaseDiscount)
                                            // ->setBaseRowTotal($child->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($childBaseDiscount))
											;
                                    /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                    /* Tinh discount cho hidden tax */
                                    $baseTaxableAmount = $child->getBaseTaxableAmount();
                                    $taxableAmount = $child->getTaxableAmount();
                                    $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                    $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                    if (Mage::helper('tax')->priceIncludesTax()) {
                                        $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                        if ($rate > 0) {
//                                            Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                            $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                            $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                            $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                            $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                        }
                                    }
                                }
                            } elseif ($item->getProduct()) {
                                /* Changed By Adam 08/10/2014 */
                                if (!$discount_include_tax)
                                    $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                else
                                    $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();
                                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount))
                                        // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                        // ->setRowTotal($item->getRowTotal() - $itemBaseDiscount)
                                        // ->setBaseRowTotal($item->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($itemBaseDiscount))
										;
                                /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                /* Tinh discount cho hidden tax */
                                $baseTaxableAmount = $item->getBaseTaxableAmount();
                                $taxableAmount = $item->getTaxableAmount();
                                $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                                $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                                if (Mage::helper('tax')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                    if ($rate > 0) {
//                                        Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                        $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                        $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
//                                        $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                        $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        }
                        $baseDiscount += $totalBaseDiscount;
                    }
                } elseif ($discountType == 'fixed') {
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        $itemBaseDiscount = 0;

                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
                                /* Changed By Adam 08/10/2014 */
                                if (!$discount_include_tax)
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                else
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() );

                                $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount))
                                        // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                        // ->setRowTotal($child->getRowTotal() - $childBaseDiscount)
                                        // ->setBaseRowTotal($child->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($childBaseDiscount))
										;
                                /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                /* Tinh discount cho hidden tax */
                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                if (Mage::helper('tax')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
//                                        Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        } elseif ($item->getProduct()) {
                            $itemBaseDiscount = $item->getQty() * $discountValue;
                            /* Changed By Adam 08/10/2014 */
                            if (!$discount_include_tax)
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            else
                                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();

                            $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
                            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount))
                                    // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                        // ->setRowTotal($item->getRowTotal() - $itemBaseDiscount)
                                        // ->setBaseRowTotal($item->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($itemBaseDiscount))
										;
                            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                            /* Tinh discount cho hidden tax */
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                            if (Mage::helper('tax')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
//                                    Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
//                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                        $baseDiscount += $itemBaseDiscount;
                    }
                } elseif ($discountType == 'percentage') {
                    if ($discountValue > 100)
                        $discountValue = 100;
                    if ($discountValue < 0)
                        $discountValue = 0;
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getProductId(), $discountedProducts) && Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        if (in_array($item->getId(), $discountedItems) && !Mage::helper('affiliateplus')->isAdmin()) {
                            continue;
                        }
                        $itemBaseDiscount = 0;  // Changed By Adam to fix the problem of calculate discount
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                /* hainh add this for calculating discount base on incl or excl tax price 22-04-2014 */
                                if (!$discount_include_tax)
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                else
                                    $price = $item->getQty() * ( $child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount() );

                                $childBaseDiscount = $price * $discountValue / 100;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount))
                                        // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                        // ->setRowTotal($child->getRowTotal() - $childBaseDiscount)
                                        // ->setBaseRowTotal($child->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($childBaseDiscount))
										;
                                /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                /* Tinh discount cho hidden tax */
                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                if (Mage::helper('tax')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
//                                        Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                        
                                    }
                                }
                            }
                        } elseif ($item->getProduct()) {
                            /* hainh add this for calculating discount base on incl or excl tax price 22-04-2014 */
                            if (!$discount_include_tax)
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                            else
                                $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();

                            $itemBaseDiscount = $price * $discountValue / 100;
                            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount))
                                    // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                    // ->setRowTotal($item->getRowTotal() - $itemBaseDiscount)
                                    // ->setBaseRowTotal($item->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($itemBaseDiscount))
									;
                            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                            /* Tinh discount cho hidden tax */
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                            if (Mage::helper('tax')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
//                                    Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
//                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                        $baseDiscount += $itemBaseDiscount;
                    }
                }
            }
        }

        if ($baseDiscount) {
            $discount = Mage::app()->getStore()->convertPrice($baseDiscount);
            $address->setBaseAffiliateplusDiscount(-$baseDiscount);
            $address->setAffiliateplusDiscount(-$discount);

            $session = Mage::getSingleton('checkout/session');
            if ($discountObj->getProgram())
                $session->setProgramData($discountObj->getProgram());
            if ($session->getData('affiliate_coupon_code')) {
                $address->setAffiliateplusCoupon($session->getData('affiliate_coupon_code'));
            }

            $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
            $address->setGrandTotal($address->getGrandTotal() - $discount);

            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
            // Dua vao quote de dung khi mua hang qua Paypal
            $quote->setBaseAffiliateplusDiscount($address->getBaseAffiliateplusDiscount());
            $quote->setAffiliateplusDiscount($address->getAffiliateplusDiscount());

            /* Dung bien de gop cac loai discount cua extension vao lam 1 */
            $address->setMagestoreBaseDiscount($baseDiscount);
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        if (!$applyTaxAfterDiscount) {
            return $this;
        }
        $session = Mage::getSingleton('checkout/session');
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
        if (!$orderId)
            $orderId = '';
        //show affiliate discount edit by Jack
        if (isset($orderId) && Mage::helper('affiliateplus')->isAdmin()) {
            Mage::helper('affiliateplus')->showAffiliateDiscount($orderId);
        }
        $amount = $address->getAffiliateplusDiscount();
        $title = $this->_getConfigHelper()->__('Affiliate Discount');
        if ($amount != 0) {
            if ($address->getAffiliateplusCoupon()) {
                $title .= ' (' . $address->getAffiliateplusCoupon() . ')';
            }
            /* show coupon code when edit Order - Edit By Jack */ else if ($session->getData('affiliate_coupon_code')) {
                $title .= ' (' . $session->getData('affiliate_coupon_code') . ')';
            }
            /* end show coupon code   */
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount,
            ));
        }
        return $this;
    }

    /**
     * Changed By Adam: 06/11/2014: Fix loi hidden tax
     * @param type $address
     * @param type $product
     * @param type $store
     * @return int
     */
    public function getItemRateOnQuote($address, $product, $store) {
        $taxClassId = $product->getTaxClassId();
        if ($taxClassId) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                    $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
            );
            $rate = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
            return $rate;
        }
        return 0;
    }

    /**
     * Changed By Adam: 06/11/2014: Fix loi hidden tax
     * @param type $address
     * @param type $store
     * @return type
     */
    public function getShipingTaxRate($address, $store) {
        $request = Mage::getSingleton('tax/calculation')->getRateRequest(
                $address, $address->getQuote()->getBillingAddress(), $address->getQuote()->getCustomerTaxClassId(), $store
        );
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));
        $rate = Mage::getSingleton('tax/calculation')->getRate($request);
        return $rate;
    }

    /**
     * Changed By Adam: 06/11/2014: Fix loi hidden tax
     * @param type $price
     * @param type $rate
     * @return type
     */
    public function calTax($price, $rate) {
        return $this->round(Mage::getSingleton('tax/calculation')->calcTaxAmount($price, $rate, true, false));
    }

    /**
     * Changed By Adam: 06/11/2014: Fix loi hidden tax
     * @param type $price
     * @return type
     */
    public function round($price) {
        return Mage::getSingleton('tax/calculation')->round($price);
    }

}
