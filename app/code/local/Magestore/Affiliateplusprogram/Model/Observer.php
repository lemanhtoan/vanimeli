<?php

class Magestore_Affiliateplusprogram_Model_Observer extends Varien_Object {

    /**
     * get module helper
     *
     * @return Magestore_Affiliateplusprogram_Helper_Data
     */
    protected function _getHelper() {
        if (!$this->getData('helper')) {
            $this->setData('helper', Mage::helper('affiliateplusprogram'));
        }
        return $this->getData('helper');
    }

    /**
     * get Configuration helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    /* edit By Jack */

    public function getProgramTransactionData($observer) {
        $orderNumber = $observer->getEvent()->getOrderNumber();
        $programInfo = $observer->getEvent()->getProgramInfo();
        $programTransactionData = Mage::getModel('affiliateplusprogram/transaction')->getCollection()
                ->addFieldToFilter('order_number', $orderNumber)
                ->getFirstItem();
        $programId = $programTransactionData->getProgramId();
        $programInfo->setProgramId($programId);
        return $this;
    }

    /* */

    public function addColumnBannerGrid($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $grid = $observer->getEvent()->getGrid();
        $grid->addColumn('program_id', array(
            'header' => $this->_getHelper()->__('Program Name'),
            'index' => 'program_id',
            'align' => 'left',
            'type' => 'options',
            'options' => $this->_getHelper()->getProgramOptions(),
        ));
        return $this;
    }

    public function addFieldBannerForm($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('program_id', 'select', array(
            'label' => $this->_getHelper()->__('Program Name'),
            'name' => 'program_id',
            'values' => $this->_getHelper()->getProgramOptionArray(),
        ));
        return $this;
    }

    public function addFieldTransactionForm($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $data = $observer->getEvent()->getForm()->getTransationData();
        $fieldset = $observer->getEvent()->getFieldset();

        $transactionPrograms = Mage::getResourceModel('affiliateplusprogram/transaction_collection')
                ->addFieldToFilter('transaction_id', $data['transaction_id']);

        $text = array();
        if ($transactionPrograms->getSize())
            foreach ($transactionPrograms as $transactionProgram) {
                if ($transactionProgram->getProgramId()) {
                    //Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                    $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/affiliateplusprogram_program/edit', array(    
                        '_current' => true,
                        'id' => $transactionProgram->getProgramId(),
                        'store' => $data['store_id'],
                    ));
                    $title = $this->_getHelper()->__('View Program Detail');
                    $label = $transactionProgram->getProgramName();
                } else {
                    $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section' => 'affiliateplus'));
                    $title = $this->_getHelper()->__('View Program Configuration Detail');
                    $label = $this->_getHelper()->__('Affiliate Program');
                }
                $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
            } else {
            $url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section' => 'affiliateplus'));
            $title = $this->_getHelper()->__('View Program Configuration Detail');
            $label = $this->_getHelper()->__('Affiliate Program');
            $text[] = '<a href="' . $url . '" title="' . $title . '">' . $label . '</a>';
        }

        $fieldset->addField('program_ids', 'note', array(
            'label' => $this->_getHelper()->__('Program(s)'),
            'text' => implode(' , ', $text),
        ));
        return $this;
    }

    public function addAccountTab($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $block = $observer->getEvent()->getForm();
//        Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
        $block->addTab('program_section', array(
            'label' => $this->_getHelper()->__('Programs'),
            'title' => $this->_getHelper()->__('Programs'),
            'url' => $block->getUrl('adminhtml/affiliateplusprogram_program/program', array(
                '_current' => true,
                'id' => $block->getRequest()->getParam('id'),
                'store' => $block->getRequest()->getParam('store')
            )),
            'class' => 'ajax',
            'after' => 'form_section',
        ));
        return $this;
    }

    public function accountSaveAfter($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $affiliateplusAccount = $observer->getEvent()->getAffiliateplusAccount();

        if ($affiliateplusAccount && $affiliateplusAccount->hasData('account_program')) {
            $joinPrograms = array(); 
            parse_str($affiliateplusAccount->getAccountProgram(), $joinPrograms);
            $joinPrograms = array_keys($joinPrograms);

            $joinedProgram = array();

            $oldProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                    ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
            $program = Mage::getModel('affiliateplusprogram/program');

            foreach ($oldProgramCollection as $oldProgram) {
                $joinedProgram[] = $oldProgram->getProgramId();
                if (in_array($oldProgram->getProgramId(), $joinPrograms))
                    continue;
                $program->load($oldProgram->getProgramId())
                        ->setNumAccount($program->getNumAccount() - 1)
                        ->setId($oldProgram->getProgramId())
                        ->orgSave();
                $oldProgram->delete();
            }

            $addPrograms = array_diff($joinPrograms, $joinedProgram);

            $newProgram = Mage::getModel('affiliateplusprogram/account')
                    ->setAccountId($affiliateplusAccount->getId())
                    ->setJoined(now());
            foreach ($addPrograms as $programId) {
                $program->load($programId)
                        ->setNumAccount($program->getNumAccount() + 1)
                        ->setId($programId)
                        ->orgSave();
                $newProgram->setProgramId($programId)->setId(null)->save();
            }
            Mage::getModel('affiliateplusprogram/joined')->updateJoined(null, $affiliateplusAccount->getId());
        } elseif ($affiliateplusAccount && $affiliateplusAccount->isObjectNew()) {
            $oldProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                    ->addFieldToFilter('account_id', $affiliateplusAccount->getId());
            if ($oldProgramCollection->getSize())
                return $this;

            $newProgram = Mage::getModel('affiliateplusprogram/account')
                    ->setAccountId($affiliateplusAccount->getId())
                    ->setJoined(now());
            $autoJoinPrograms = Mage::getResourceModel('affiliateplusprogram/program_collection')
                    ->addFieldToFilter('autojoin', 1);
            $group = Mage::getModel('customer/customer')->load($affiliateplusAccount->getCustomerId())->getGroupId();
            $autoJoinPrograms->getSelect()
                    ->where("scope = 0 OR (scope = 1 AND FIND_IN_SET($group,customer_groups) )");
            foreach ($autoJoinPrograms as $autoJoinProgram) {
                $autoJoinProgram->setNumAccount($autoJoinProgram->getNumAccount() + 1)->orgSave();
                $newProgram->setProgramId($autoJoinProgram->getId())->setId(null)->save();
            }
            Mage::getModel('affiliateplusprogram/joined')->updateJoined(null, $affiliateplusAccount->getId());
        }
        return $this;
    }

    public function getListProgramWelcome($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $programListObj = $observer->getEvent()->getProgramListObject();

        $programList = $programListObj->getProgramList();
        if (isset($programList['default'])) {
            /* hainh update 28-04-2014 */
            if (!Mage::helper('affiliateplusprogram')->showDefault()) {
                unset($programList['default']);
            }
        }

        $collection = Mage::getResourceModel('affiliateplusprogram/program_collection')->setStoreId(Mage::app()->getStore()->getId());
        foreach ($collection as $item)
            if ($item->getStatus() && $item->getShowInWelcome()) {
                Mage::dispatchEvent('affiliateplus_prepare_program', array('info' => $item));
                $programList[$item->getId()] = $item;
            }
        $programListObj->setProgramList($programList);
        return $this;
    }

    public function bannerPrepareCollection($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $collection = $observer->getEvent()->getCollection();

        $joinedPrograms = $this->_getHelper()->getJoinedProgramIds();
        $joinedPrograms[] = 0;
        $collection->addFieldToFilter('program_id', array('in' => $joinedPrograms));

        return $this;
    }

    public function productGetFinalPrice($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        $discountedObj = $observer->getEvent()->getDiscountedObj();

        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();

        /* hainh update 21-04-2014 delay cookie error */
        if (!$affiliateInfo) {
            $accountCode = Mage::app()->getRequest()->getParam('acc');
            $account = Mage::getModel('affiliateplus/account')->setStoreId(Mage::app()->getStore()->getId())->loadByIdentifyCode($accountCode);
            $affiliateInfo[$accountCode] = array(
                'index' => '1',
                'code' => $accountCode,
                'account' => $account,
            );
        }
        /* hainh end update */

        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                $program = Mage::helper('affiliateplusprogram')->getProgramByProductAccount($product, $account);
                if ($program) {
                    $price = $discountedObj->getPrice();

                    $discountType = $program->getDiscountType();
                    $discountValue = $program->getDiscount();
                    if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                        if ($program->getSecDiscount()) {
                            $discountType = $program->getSecDiscountType();
                            $discountValue = $program->getSecondaryDiscount();
                        }
                    }
                    if ($discountType == 'fixed' || $discountType == 'cart_fixed'
                    ) {
                        $price -= floatval($discountValue);
                    } elseif ($discountType == 'percentage') {
                        $price -= floatval($discountValue) / 100 * $price;
                    }

                    if ($price < 0)
                        $price = 0;
                    $discountedObj->setPrice($price);
                    $discountedObj->setDiscounted(true);
                }
                return $this;
            }
        return $this;
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

    public function addressCollectTotal($observer) {
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        /* hainh add discount calculate with incl or excl tax variable 22-04-2014 */
        $discount_include_tax = false;
        if ((int) (Mage::getStoreConfig('tax/calculation/discount_tax')))
            $discount_include_tax = true;

        $address = $observer->getEvent()->getAddress();
        $discountObj = $observer->getEvent()->getDiscountObj();
        $items = $address->getAllItems();

        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );

        $affiliateInfo = $discountObj->getAffiliateInfo();
        $baseDiscount = $discountObj->getBaseDiscount();
        $discountedItems = $discountObj->getDiscountedItems();
        $discountedProducts = $discountObj->getDiscountedProducts();
        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                if ($account->getUsingCoupon()) {
                    $program = $account->getUsingProgram();
                    if (!$program)
                        return $this;
                    /* Edit By Jack */
                    $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                    if ($storeId && !Mage::app()->getStore()->getId())
                        $program = Mage::getModel('affiliateplusprogram/program')
                                ->setStoreId($storeId)
                                ->load($program->getId());
                    /* End Edit */
                    $discountObj->setDefaultDiscount(false);
                    if (!$program->validateOrder($address->getQuote()))
                        return $this;
                }
                foreach ($items as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($account->getUsingCoupon()) {
                        if (!$program->validateItem($item))
                            continue;
                    } else {
                        if (in_array($item->getId(), $discountedItems))
                            continue;
                        $program = Mage::helper('affiliateplusprogram')->getProgramByItemAccount($item, $account);
                    }
                    
//                    Changed by Adam (09/05/2016): Fix issue: can't detect program if customer (buyer) can't belong to the customer group in Discount section
                    if ($program && $program->checkCustomerGroupForDiscount()) {
                        $discountType = $program->getDiscountType();
                        $discountValue = floatval($program->getDiscount());
                        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered() && !$orderId) {
                            if ($program->getSecDiscount()) {
                                $discountType = $program->getSecDiscountType();
                                $discountValue = floatval($program->getSecondaryDiscount());
                            }
                        } else if (Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1 && $orderId) {
                            if ($program->getSecDiscount()) {
                                $discountType = $program->getSecDiscountType();
                                $discountValue = floatval($program->getSecondaryDiscount());
                            }
                        }
                        if ($discountType == 'cart_fixed') {

                            $baseItemsPrice = 0;
                            foreach ($address->getAllItems() as $_item) {
                                if ($_item->getParentItemId()) {
                                    continue;
                                }
                                if (in_array($_item->getId(), $discountedItems)) {
                                    continue;
                                }
                                if (!$program->validateItem($_item)) {
                                    continue;
                                }
                                // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                                if (!$account->getUsingCoupon() && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                                    continue;
                                }

                                if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                                    foreach ($_item->getChildren() as $child) {
                                        /* Changed By Adam 08/10/2014 */
                                        if (!$discount_include_tax)
                                            $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                        else
                                            $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                    }
                                } elseif ($_item->getProduct()) {
                                    /* Changed By Adam 08/10/2014 */
                                    if (!$discount_include_tax)
                                        $baseItemsPrice += $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                                    else
                                        $baseItemsPrice += $_item->getQty() * $_item->getBasePriceInclTax() - $_item->getBaseDiscountAmount();
                                }
                            }
                            if ($baseItemsPrice) {
                                $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                                foreach ($address->getAllItems() as $_item) {
                                    if ($_item->getParentItemId()) {
                                        continue;
                                    }
                                    if (in_array($_item->getId(), $discountedItems)) {
                                        continue;
                                    }
                                    if (!$program->validateItem($_item)) {
                                        continue;
                                    }

                                    // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                                    if (!$account->getUsingCoupon() && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                                        continue;
                                    }

                                    if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                                        foreach ($_item->getChildren() as $child) {
                                            /* Changed By Adam 08/10/2014 */
                                            if (!$discount_include_tax)
                                                $price = $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                            else
                                                $price = $_item->getQty() * ($child->getQty() * $child->getBasePriceInclTax() - $child->getBaseDiscountAmount());
                                            $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                            $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount))
                                            // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                            // ->setRowTotal($child->getRowTotal() - $childBaseDiscount)
                                            // ->setBaseRowTotal($child->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($childBaseDiscount))
                                            ;

                                            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                            /* Tinh discount cho hidden tax */
                                            if ($applyTaxAfterDiscount) {

                                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                                $taxableAmount = $child->getTaxableAmount();
                                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                                if (Mage::helper('tax')->priceIncludesTax()) {
                                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                                    if ($rate > 0) {
//                                                        Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($_item->getProduct()) {
                                        /* Changed By Adam 08/10/2014 */
                                        if (!$discount_include_tax)
                                            $price = $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                                        else
                                            $price = $_item->getQty() * $_item->getBasePriceInclTax() - $_item->getBaseDiscountAmount();
                                        $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                        $_item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount))
                                        // Added By Adam 16/11/2014 to solve the problem of calculating item tax when create invoice 
                                        // ->setRowTotal($_item->getRowTotal() - $itemBaseDiscount)
                                        // ->setBaseRowTotal($_item->getBaseRowTotal() - Mage::app()->getStore()->convertPrice($itemBaseDiscount))
                                        ;
                                        /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                        /* Tinh discount cho hidden tax */
                                        if ($applyTaxAfterDiscount) {
                                            $baseTaxableAmount = $_item->getBaseTaxableAmount();
                                            $taxableAmount = $_item->getTaxableAmount();
                                            $_item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $_item->getBaseAffiliateplusAmount()));
                                            $_item->setTaxableAmount(max(0, $taxableAmount - $_item->getAffiliateplusAmount()));

                                            if (Mage::helper('tax')->priceIncludesTax()) {
                                                $rate = $this->getItemRateOnQuote($address, $_item->getProduct(), $store);
                                                if ($rate > 0) {
//                                                    Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                                    $_item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $_item->getBaseTaxableAmount(), $rate));
                                                    $_item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $_item->getTaxableAmount(), $rate));
//                                                    $_item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($_item->getBaseTaxableAmount(), $rate));
//                                                    $_item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($_item->getTaxableAmount(), $rate));
                                                }
                                            }
                                        }
                                    }
                                    $discountedItems[] = $_item->getId();
                                    $discountedProducts[] = $item->getProductId();
                                }
                                $baseDiscount += $totalBaseDiscount;
                            } else {
                                $discountedItems[] = $item->getId();
                                $discountedProducts[] = $item->getProductId();
                            }
                        } elseif ($discountType == 'fixed') {

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
                                    if ($applyTaxAfterDiscount) {
                                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                                        $taxableAmount = $child->getTaxableAmount();
                                        $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                        $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                        if (Mage::helper('tax')->priceIncludesTax()) {
                                            $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                            if ($rate > 0) {
//                                                Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                            }
                                        }
                                    }
                                }
                            } else {
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
                                if ($applyTaxAfterDiscount) {
                                    $baseTaxableAmount = $item->getBaseTaxableAmount();
                                    $taxableAmount = $item->getTaxableAmount();
                                    $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                                    $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                                    if (Mage::helper('tax')->priceIncludesTax()) {
                                        $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                        if ($rate > 0) {
//                                            Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                            $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                            $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
//                                            $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                            $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                        }
                                    }
                                }
                            }
                            $discountedItems[] = $item->getId();
                            $discountedProducts[] = $item->getProductId();
                            $baseDiscount += $itemBaseDiscount;
                        } elseif ($discountType == 'percentage') {

                            $itemBaseDiscount = 0;
                            if ($discountValue > 100)
                                $discountValue = 100;
                            if ($discountValue < 0)
                                $discountValue = 0;
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
                                    if ($applyTaxAfterDiscount) {
                                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                                        $taxableAmount = $child->getTaxableAmount();
                                        $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                        $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                        if (Mage::helper('tax')->priceIncludesTax()) {
                                            $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                            if ($rate > 0) {
//                                                Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $child->getBaseTaxableAmount(), $rate));
                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $child->getTaxableAmount(), $rate));
//                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
//                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                            }
                                        }
                                    }
                                }
                            } else {
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
                                if ($applyTaxAfterDiscount) {
                                    $baseTaxableAmount = $item->getBaseTaxableAmount();
                                    $taxableAmount = $item->getTaxableAmount();
                                    $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                                    $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                                    if (Mage::helper('tax')->priceIncludesTax()) {
                                        $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                        if ($rate > 0) {
//                                            Changed By Adam 29/10/2015: Fixed the issue of calculate grandtotal
                                            $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount - $item->getBaseTaxableAmount(), $rate));
                                            $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount - $item->getTaxableAmount(), $rate));
//                                            $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
//                                            $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                        }
                                    }
                                }
                            }
                            $discountedItems[] = $item->getId();
                            $discountedProducts[] = $item->getProductId();
                            $baseDiscount += $itemBaseDiscount;
                        }
                    }
                }
                $discountObj->setDiscountedProducts($discountedProducts);
                $discountObj->setBaseDiscount($baseDiscount);
                $discountObj->setProgram($program);
                $discountObj->setDiscountedItems($discountedItems);
                return $this;
            }
        return $this;
    }

    /* calculate affilidate discount when edit order - Edit By Jack  */

    public function addressCollectTotalEdit($observer) {
        /* hainh edit 25-04-2014 */
        /* hainh add discount calculate with incl or excl tax variable 22-04-2014 */
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
        $programId = '';
        $discount_include_tax = false;
        if ((int) (Mage::getStoreConfig('tax/calculation/discount_tax')))
            $discount_include_tax = true;
        $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        $couponCodeBySession = Mage::getSingleton('checkout/session')->getAffiliateCouponCode();
        $address = $observer->getEvent()->getAddress();
        $quote = $address->getQuote();
        $applyTaxAfterDiscount = (bool) Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $quote->getStoreId()
        );
        $discountObj = $observer->getEvent()->getDiscountObj();
        $allInfo = Mage::helper('affiliateplus')->processDataWhenEditOrder();
        $account = null; //Changed by Adam to solve the problem of Undefined variable: account when create order
        if (count($allInfo)) {
            if (isset($allInfo['account_info']))
                $account = $allInfo['account_info'];
            if (isset($allInfo['program_id']))
                $programId = $allInfo['program_id'];
        }
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            if ($programId != '' && $programId != 0) {
                $session = Mage::getSingleton('checkout/session');
                $session->unsAffiliateCouponCode();
            }
            return;
        }
        if (isset($allInfo['program_name']) && $allInfo['program_name'] == 'Affiliate Program' && $account && $programId == 0) {
            $discountObj->setProgram('Affiliate Program');
            return $this;
        }
        if (!$account) {
            $discountObj->setDefaultDiscount(false);
            return $this;
        }
        $items = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllVisibleItems();
        //$items = $address->getAllItems();
        $baseDiscount = $discountObj->getBaseDiscount();
        $discountedItems = $discountObj->getDiscountedItems();
        $discountedProducts = $discountObj->getDiscountedProducts();
        $program = Mage::getModel('affiliateplusprogram/program')
                ->setStoreId($storeId)
                ->load($programId);
        if ($program->getId() && $program->getStatus() != 1) {
            $session = Mage::getSingleton('checkout/session');
            $session->unsAffiliateCouponCode();
            $program = Mage::helper('affiliateplusprogram')->getProgramByMaxPriority($account->getId());
            if (!$program)
                return $this;
        }
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($account->getUsingCoupon() || ($couponCodeBySession && $account->getId())) {
                if ($programId != 0)
                    $discountObj->setDefaultDiscount(false);
                if (!$program->validateItem($item))
                    continue;
            } else {
                // if (in_array($item->getId(), $discountedItems))
                //   continue;
                $program = Mage::helper('affiliateplusprogram')->getProgramByItemAccount($item, $account);
            }
            if ($program) {
                $discountValue = floatval($program->getDiscount());
                $discountType = $program->getDiscountType();
                if (($orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1) || (!$orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered())) {
                    if ($program->getSecDiscount()) {
                        $discountType = $program->getSecDiscountType();
                        $discountValue = floatval($program->getSecondaryDiscount());
                    }
                }
                if ($discountType == 'cart_fixed') {
                    $baseItemsPrice = 0;
                    foreach ($address->getAllItems() as $_item) {
                        if ($_item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($_item->getId(), $discountedItems)) {
                            continue;
                        }
                        if (!$program->validateItem($_item)) {
                            continue;
                        }
                        // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                        if (!$couponCodeBySession && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                            continue;
                        }

                        if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                            foreach ($_item->getChildren() as $child) {
                                $baseItemsPrice += $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                            }
                        } elseif ($_item->getProduct()) {
                            $baseItemsPrice += $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                        }
                    }
                    if ($baseItemsPrice) {
                        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                        foreach ($address->getAllItems() as $_item) {
                            if ($_item->getParentItemId()) {
                                continue;
                            }
                            if (in_array($_item->getId(), $discountedItems)) {
                                continue;
                            }
                            if (!$program->validateItem($_item)) {
                                continue;
                            }
                            // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                            if (!$couponCodeBySession && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                                continue;
                            }

                            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                                foreach ($_item->getChildren() as $child) {
                                    $price = $_item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                            ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                                    /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                    /* Tinh discount cho hidden tax */
                                    if ($applyTaxAfterDiscount) {

                                        $baseTaxableAmount = $child->getBaseTaxableAmount();
                                        $taxableAmount = $child->getTaxableAmount();
                                        $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                        $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                        if (Mage::helper('tax')->priceIncludesTax()) {
                                            $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                            if ($rate > 0) {
                                                $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
                                                $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                            }
                                        }
                                    }
                                }
                            } elseif ($_item->getProduct()) {
                                $price = $_item->getQty() * $_item->getBasePrice() - $_item->getBaseDiscountAmount();
                                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                $_item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                                /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                                /* Tinh discount cho hidden tax */
                                if ($applyTaxAfterDiscount) {
                                    $baseTaxableAmount = $_item->getBaseTaxableAmount();
                                    $taxableAmount = $_item->getTaxableAmount();
                                    $_item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $_item->getBaseAffiliateplusAmount()));
                                    $_item->setTaxableAmount(max(0, $taxableAmount - $_item->getAffiliateplusAmount()));

                                    if (Mage::helper('tax')->priceIncludesTax()) {
                                        $rate = $this->getItemRateOnQuote($address, $_item->getProduct(), $store);
                                        if ($rate > 0) {
                                            $_item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($_item->getBaseTaxableAmount(), $rate));
                                            $_item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($_item->getTaxableAmount(), $rate));
                                        }
                                    }
                                }
                            }
                            $discountedItems[] = $_item->getId();
                            $discountedProducts[] = $_item->getProductId();
                        }
                        $baseDiscount += $totalBaseDiscount;
                    } else {
                        $discountedItems[] = $item->getId();
                        $discountedProducts[] = $item->getProductId();
                    }
                } elseif ($discountType == 'fixed') {
                    $itemBaseDiscount = 0;
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
                            $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                            $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
                            $itemBaseDiscount += $childBaseDiscount;
                            $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                            /* Tinh discount cho hidden tax */
                            if ($applyTaxAfterDiscount) {

                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                if (Mage::helper('tax')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        }
                    } else {
                        $itemBaseDiscount = $item->getQty() * $discountValue;
                        $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                        $itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
                        $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));

                        /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                        /* Tinh discount cho hidden tax */
                        if ($applyTaxAfterDiscount) {
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                            if (Mage::helper('tax')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                    }
                    if ($itemBaseDiscount > 0) {
                        $discountedItems[] = $item->getId();
                        $discountedProducts[] = $item->getProductId();
                    }
                    $baseDiscount += $itemBaseDiscount;
                } elseif ($discountType == 'percentage') {
                    $itemBaseDiscount = 0;
                    if ($discountValue > 100)
                        $discountValue = 100;
                    if ($discountValue < 0)
                        $discountValue = 0;
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
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                            /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                            /* Tinh discount cho hidden tax */
                            if ($applyTaxAfterDiscount) {

                                $baseTaxableAmount = $child->getBaseTaxableAmount();
                                $taxableAmount = $child->getTaxableAmount();
                                $child->setBaseTaxableAmount(max(0, $baseTaxableAmount - $child->getBaseAffiliateplusAmount()));
                                $child->setTaxableAmount(max(0, $taxableAmount - $child->getAffiliateplusAmount()));

                                if (Mage::helper('tax')->priceIncludesTax()) {
                                    $rate = $this->getItemRateOnQuote($address, $child->getProduct(), $store);
                                    if ($rate > 0) {
                                        $child->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($child->getBaseTaxableAmount(), $rate));
                                        $child->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($child->getTaxableAmount(), $rate));
                                    }
                                }
                            }
                        }
                    } else {
                        /* hainh add this for calculating discount base on incl or excl tax price 22-04-2014 */
                        if (!$discount_include_tax)
                            $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                        else
                            $price = $item->getQty() * $item->getBasePriceInclTax() - $item->getBaseDiscountAmount();

                        $itemBaseDiscount = $price * $discountValue / 100;
                        $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                        /* Changed By Adam: 06/11/2014: Fix loi hidden tax */
                        /* Tinh discount cho hidden tax */
                        if ($applyTaxAfterDiscount) {
                            $baseTaxableAmount = $item->getBaseTaxableAmount();
                            $taxableAmount = $item->getTaxableAmount();
                            $item->setBaseTaxableAmount(max(0, $baseTaxableAmount - $item->getBaseAffiliateplusAmount()));
                            $item->setTaxableAmount(max(0, $taxableAmount - $item->getAffiliateplusAmount()));

                            if (Mage::helper('tax')->priceIncludesTax()) {
                                $rate = $this->getItemRateOnQuote($address, $item->getProduct(), $store);
                                if ($rate > 0) {
                                    $item->setAffiliateplusBaseHiddenTaxAmount($this->calTax($baseTaxableAmount, $rate) - $this->calTax($item->getBaseTaxableAmount(), $rate));
                                    $item->setAffiliateplusHiddenTaxAmount($this->calTax($taxableAmount, $rate) - $this->calTax($item->getTaxableAmount(), $rate));
                                }
                            }
                        }
                    }
                    $discountedItems[] = $item->getId();
                    $discountedProducts[] = $item->getProductId();
                    $baseDiscount += $itemBaseDiscount;
                }
            }
        }
        $discountObj->setDiscountedProducts($discountedProducts);
        $discountObj->setProgram($program);
        $discountObj->setBaseDiscount($baseDiscount);
        $discountObj->setDiscountedItems($discountedItems);
        return $this;
    }

    /* end calculate affiliate discount */

    public function calculateCommissionBefore($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $order->setQuote(Mage::getModel('sales/quote')->load($order->getQuoteId()));
        $items = $order->getAllItems();
        $affiliateInfo = $observer->getEvent()->getAffiliateInfo();
        $commissionObj = $observer->getEvent()->getCommissionObj();

        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();
        // Changed By Adam 14/08/2014: Invoice tung phan
//        $affiliateplusCommissionItem = $commissionObj->getAffiliateplusCommissionItem();

        foreach ($affiliateInfo as $info)
            if ($account = $info['account']) {
                if ($account->getUsingCoupon()) {
                    $program = $account->getUsingProgram();

                    if (!$program)
                        return $this;

                    /* Edit By Jack */
                    $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                    if ($storeId && !Mage::app()->getStore()->getId())
                        $program = Mage::getModel('affiliateplusprogram/program')
                                ->setStoreId($storeId)
                                ->load($program->getId());
                    /* End Edit */

                    $commissionObj->setDefaultCommission(false);
                    if (!$program->validateOrder($order))
                        return $this;
                }
                foreach ($items as $item) {
                    $affiliateplusCommissionItem = '';
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($account->getUsingCoupon()) {
                        if (!$program->validateItem($item))
                            continue;
                    } else {
                        if (in_array($item->getId(), $commissionItems))
                            continue;
                        $program = Mage::helper('affiliateplusprogram')
                                ->initProgram($account->getId(), $order)
                                ->getProgramByItemAccount($item, $account);

                    }
                    if (!$program) {
                        continue;
                    }
                    $affiliateType = $program->getAffiliateType() ? $program->getAffiliateType() : $this->_getConfigHelper()->getCommissionConfig('affiliate_type');
                    /* Changed BY Adam for customize function: Commission for whole cart 22/07/2014 */
                    $baseItemsPrice = 0; // total price of the items that belong to this program.
                    foreach ($items as $_item) {
                        if ($_item->getParentItemId()) {
                            continue;
                        }

                        if (!$program->validateItem($_item)) {
                            continue;
                        }
                        // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                        if (!$account->getUsingCoupon() && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                            continue;
                        }

                        if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                            foreach ($_item->getChildrenItems() as $child) {
                                $baseItemsPrice += $_item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                            }
                        } elseif ($_item->getProduct()) {
                            $baseItemsPrice += $_item->getQtyOrdered() * $_item->getBasePrice() - $_item->getBaseDiscountAmount() - $_item->getBaseAffiliateplusAmount();
                        }
                    }
                    /* Endcode */

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        $childHasCommission = false;
                        foreach ($item->getChildrenItems() as $child) {
                            $affiliateplusCommissionItem = '';
                            if ($affiliateType == 'profit')
                                $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                            else
                                $baseProfit = $child->getBasePrice();

                            $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            if ($baseProfit <= 0)
                                continue;
                            $commissionType = $program->getCommissionType();
                            $commissionValue = floatval($program->getCommission());
                            if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                                if ($program->getSecCommission()) {
                                    $commissionType = $program->getSecCommissionType();
                                    $commissionValue = floatval($program->getSecondaryCommission());
                                }
                            }
                            if (!$commissionValue)
                                continue;

                            $childHasCommission = true;
                            /* Changed BY Adam commission for whole cart 22/07/2014 */
                            if ($commissionType == 'cart_fixed') {
                                $commissionValue = min($commissionValue, $baseItemsPrice);
                                $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                                $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            } elseif ($commissionType == 'fixed') {
                                $itemCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            } elseif ($commissionType == 'percentage') {
                                if ($commissionValue > 100)
                                    $commissionValue = 100;
                                if ($commissionValue < 0)
                                    $commissionValue = 0;
                                $itemCommission = $baseProfit * $commissionValue / 100;
                            }

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $affiliateplusCommissionItem .= $itemCommission . ",";
                            $commissionObject = new Varien_Object(array(
                                'profit' => $baseProfit,
                                'commission' => $itemCommission,
                                'tier_commission' => array(),
                                'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                                'affiliateplus_commission_item' => $affiliateplusCommissionItem, // Added By Adam 14/08/2014
                            ));
                            Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                                'item' => $child,
                                'account' => $account,
                                'commission_obj' => $commissionObject,
                                'program' => $program
                            ));

                            if ($commissionObject->getTierCommission())
                                $tierCommissions[$child->getId()] = $commissionObject->getTierCommission();

                            $commission += $commissionObject->getCommission();
                            $child->setAffiliateplusCommission($commissionObject->getCommission());

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $child->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());

                            if (!isset($extraContent[$program->getId()]['total_amount']))
                                $extraContent[$program->getId()]['total_amount'] = 0;
                            // Changed By Adam: 19/09/2014: Fix loi sai total amount o program transaction khi mua 1 san pham qty lon hon 1
//                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice();
                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice() * $child->getQtyOrdered();
                            if (!isset($extraContent[$program->getId()]['commission']))
                                $extraContent[$program->getId()]['commission'] = 0;
                            $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();

                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();

                            $extraContent[$program->getId()]['order_item_ids'][] = $child->getProduct()->getId();
                            $extraContent[$program->getId()]['order_item_names'][] = $child->getName();
                        }
                        if ($childHasCommission) {
                            // $orderItemIds[] = $item->getProduct()->getId();
                            // $orderItemNames[] = $item->getName();
                            $commissionItems[] = $item->getId();

                            $extraContent[$program->getId()]['program_name'] = $program->getName();
                            // $extraContent[$program->getId()]['order_item_ids'][] = $item->getProduct()->getId();
                            // $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                        }
                    } else {
                        if ($affiliateType == 'profit')
                            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                        else
                            $baseProfit = $item->getBasePrice();

                        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0)
                            continue;

                        $commissionType = $program->getCommissionType();
                        $commissionValue = floatval($program->getCommission());
                        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                            if ($program->getSecCommission()) {
                                $commissionType = $program->getSecCommissionType();
                                $commissionValue = floatval($program->getSecondaryCommission());
                            }
                        }
                        if (!$commissionValue)
                            continue;
                        //jack
                        if ($item->getProduct())
                            $inProductId = $item->getProduct()->getId();
                        else
                            $inProductId = $item->getProductId();
                        //	
                        $orderItemIds[] = $inProductId;
                        $orderItemNames[] = $item->getName();
                        $commissionItems[] = $item->getId();

                        /* Changed BY Adam: commission for whole cart 22/07/2014 */
                        if ($commissionType == 'cart_fixed') {

                            $commissionValue = min($commissionValue, $baseItemsPrice);

                            $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();

                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                        } elseif ($commissionType == 'fixed') {
                            $itemCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        } elseif ($commissionType == 'percentage') {
                            if ($commissionValue > 100)
                                $commissionValue = 100;
                            if ($commissionValue < 0)
                                $commissionValue = 0;
                            $itemCommission = $baseProfit * $commissionValue / 100;
                        }

                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $affiliateplusCommissionItem .= $itemCommission . ",";
                        $commissionObject = new Varien_Object(array(
                            'profit' => $baseProfit,
                            'commission' => $itemCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                            'affiliateplus_commission_item' => $affiliateplusCommissionItem, // Added By Adam 14/08/2014
                        ));
                        Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                            'item' => $item,
                            'account' => $account,
                            'commission_obj' => $commissionObject,
                            'program' => $program
                        ));
                        if ($commissionObject->getTierCommission())
                            $tierCommissions[$item->getId()] = $commissionObject->getTierCommission();

                        $commission += $commissionObject->getCommission();
                        $item->setAffiliateplusCommission($commissionObject->getCommission());

                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $item->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());

                        $extraContent[$program->getId()]['program_name'] = $program->getName();
                        if ($item->getProduct())
                            $in_product = $item->getProduct()->getId();
                        else
                            $in_product = $item->getProductId();
                        $extraContent[$program->getId()]['order_item_ids'][] = $in_product;
                        $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                        if (!isset($extraContent[$program->getId()]['total_amount']))
                            $extraContent[$program->getId()]['total_amount'] = 0;
                        // Changed By Adam: 19/09/2014: Fix loi sai total amount o program transaction khi mua 1 san pham qty lon hon 1
//                        $extraContent[$program->getId()]['total_amount'] += $item->getBasePrice();
                        $extraContent[$program->getId()]['total_amount'] += $item->getBasePrice() * $item->getQtyOrdered();
                        if (!isset($extraContent[$program->getId()]['commission']))
                            $extraContent[$program->getId()]['commission'] = 0;
                        $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();
                    }
                }
                $commissionObj->setCommission($commission);
                $commissionObj->setOrderItemIds($orderItemIds);
                $commissionObj->setOrderItemNames($orderItemNames);
                $commissionObj->setCommissionItems($commissionItems);
                $commissionObj->setExtraContent($extraContent);
                $commissionObj->setTierCommissions($tierCommissions);
                // Changed By Adam 14/08/2014: Invoice tung phan
//                $commissionObj->setAffiliateplusCommissionItem($affiliateplusCommissionItem);
                return $this;
            }
        return $this;
    }

    /* calculate Commision when edit order - Edit By Jack */

    public function calculateCommissionBeforeEdit($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $order = $observer->getEvent()->getOrder();
        $order->setQuote(Mage::getModel('sales/quote')->load($order->getQuoteId()));
        $items = $order->getAllItems();
        $affiliateInfo = $observer->getEvent()->getAffiliateInfo();
        $commissionObj = $observer->getEvent()->getCommissionObj();

        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();
        $account = $observer->getEvent()->getAccount();
        $programId = $observer->getEvent()->getProgramId();
        $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        if ($account->getAccountId()) {
            if ($programId) {
                $program = Mage::getModel('affiliateplusprogram/program')
                        ->setStoreId($storeId)
                        ->load($programId);
                if (!$program)
                    return $this;
                // $commissionObj->setDefaultCommission(false);
                if (!$program->validateOrder($order))
                    return $this;
            } else
                return $this;
            foreach ($items as $item) {
                $affiliateplusCommissionItem = '';
                if ($item->getParentItemId()) {
                    continue;
                }
                if (Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code') && $account->getStatus() == 1) {
                    $commissionObj->setDefaultCommission(false);
                    if (!$program->validateItem($item))
                        continue;
                } else {
                    if (in_array($item->getId(), $commissionItems))
                        continue;
                    $program = Mage::helper('affiliateplusprogram')
                            ->initProgram($account->getId(), $order)
                            ->getProgramByItemAccount($item, $account);
                }
                if (!$program) {
                    continue;
                }
                $affiliateType = $program->getAffiliateType() ? $program->getAffiliateType() : $this->_getConfigHelper()->getCommissionConfig('affiliate_type');
                /* Changed BY Adam for customize function: Commission for whole cart 22/07/2014 */
                $baseItemsPrice = 0; // total price of the items that belong to this program.
                foreach ($items as $_item) {
                    if ($_item->getParentItemId()) {
                        continue;
                    }

                    if (!$program->validateItem($_item)) {
                        continue;
                    }

                    // Changed By Adam 01/08/2014: don't calculate the items that belong to higher program's priority
                    if (!Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code') && Mage::helper('affiliateplusprogram')->checkItemInHigherPriorityProgram($account->getId(), $_item, $program->getPriority())) {
                        continue;
                    }

                    if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                        foreach ($_item->getChildrenItems() as $child) {
                            $baseItemsPrice += $_item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                        }
                    } elseif ($_item->getProduct()) {
                        $baseItemsPrice += $_item->getQtyOrdered() * $_item->getBasePrice() - $_item->getBaseDiscountAmount() - $_item->getBaseAffiliateplusAmount();
                    }
                }
                /* Endcode */
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    $childHasCommission = false;
                    foreach ($item->getChildrenItems() as $child) {
                        $affiliateplusCommissionItem = '';
                        if ($affiliateType == 'profit')
                            $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                        else
                            $baseProfit = $child->getBasePrice();

                        $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0)
                            continue;
                        $commissionType = $program->getCommissionType();
                        $commissionValue = floatval($program->getCommission());
                        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                            if ($program->getSecCommission()) {
                                $commissionType = $program->getSecCommissionType();
                                $commissionValue = floatval($program->getSecondaryCommission());
                            }
                        }
                        if (!$commissionValue)
                            continue;

                        $childHasCommission = true;
                        /* Changed BY Adam commission for whole cart 22/07/2014 */
                        if ($commissionType == 'cart_fixed') {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                        } elseif ($commissionType == 'fixed') {
                            $itemCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                        } elseif ($commissionType == 'percentage') {
                            if ($commissionValue > 100)
                                $commissionValue = 100;
                            if ($commissionValue < 0)
                                $commissionValue = 0;
                            $itemCommission = $baseProfit * $commissionValue / 100;
                        }
                        $affiliateplusCommissionItem .= $itemCommission . ",";
                        $commissionObject = new Varien_Object(array(
                            'profit' => $baseProfit,
                            'commission' => $itemCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                            'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                        ));
                        Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                            'item' => $child,
                            'account' => $account,
                            'commission_obj' => $commissionObject,
                            'program' => $program
                        ));

                        if ($commissionObject->getTierCommission())
                            $tierCommissions[$child->getId()] = $commissionObject->getTierCommission();

                        $commission += $commissionObject->getCommission();
                        $child->setAffiliateplusCommission($commissionObject->getCommission());
                        $child->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());
                        if (!isset($extraContent[$program->getId()]['total_amount']))
                            $extraContent[$program->getId()]['total_amount'] = 0;
                        // Changed By Adam: 19/09/2014: Fix loi sai total amount o program transaction khi mua 1 san pham qty lon hon 1
//                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice();
                        $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice() * $child->getQtyOrdered();
                        if (!isset($extraContent[$program->getId()]['commission']))
                            $extraContent[$program->getId()]['commission'] = 0;
                        $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();

                        $orderItemIds[] = $child->getProduct()->getId();
                        $orderItemNames[] = $child->getName();

                        $extraContent[$program->getId()]['order_item_ids'][] = $child->getProduct()->getId();
                        $extraContent[$program->getId()]['order_item_names'][] = $child->getName();
                    }
                    if ($childHasCommission) {
                        // $orderItemIds[] = $item->getProduct()->getId();
                        // $orderItemNames[] = $item->getName();
                        $commissionItems[] = $item->getId();

                        $extraContent[$program->getId()]['program_name'] = $program->getName();
                        // $extraContent[$program->getId()]['order_item_ids'][] = $item->getProduct()->getId();
                        // $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                    }
                } else {
                    if ($affiliateType == 'profit')
                        $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                    else
                        $baseProfit = $item->getBasePrice();

                    $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                    if ($baseProfit <= 0)
                        continue;
                    $commissionType = $program->getCommissionType();
                    $commissionValue = floatval($program->getCommission());
                    if (Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1 || (Mage::helper('affiliateplus/cookie')->getNumberOrdered() && !$order->getOriginalIncrementId())) {
                        if ($program->getSecCommission()) {
                            $commissionType = $program->getSecCommissionType();
                            $commissionValue = floatval($program->getSecondaryCommission());
                        }
                    }
                    if (!$commissionValue)
                        continue;
                    //jack
                    if ($item->getProduct())
                        $productId = $item->getProduct()->getId();
                    else
                        $productId = $item->getProductId();
                    //
                    $orderItemIds[] = $productId;
                    $orderItemNames[] = $item->getName();
                    $commissionItems[] = $item->getId();

                    /* Changed BY Adam: commission for whole cart 22/07/2014 */
                    if ($commissionType == 'cart_fixed') {

                        $commissionValue = min($commissionValue, $baseItemsPrice);

                        $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();

                        $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                    } elseif ($commissionType == 'fixed') {
                        $itemCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                    } elseif ($commissionType == 'percentage') {
                        if ($commissionValue > 100)
                            $commissionValue = 100;
                        if ($commissionValue < 0)
                            $commissionValue = 0;
                        $itemCommission = $baseProfit * $commissionValue / 100;
                    }
                    $affiliateplusCommissionItem .= $itemCommission . ",";
                    $commissionObject = new Varien_Object(array(
                        'profit' => $baseProfit,
                        'commission' => $itemCommission,
                        'tier_commission' => array(),
                        'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                        'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                    ));
                    Mage::dispatchEvent('affiliateplusprogram_calculate_tier_commission', array(
                        'item' => $item,
                        'account' => $account,
                        'commission_obj' => $commissionObject,
                        'program' => $program
                    ));

                    if ($commissionObject->getTierCommission())
                        $tierCommissions[$item->getId()] = $commissionObject->getTierCommission();
                    $commission += $commissionObject->getCommission();
                    $item->setAffiliateplusCommission($commissionObject->getCommission());
                    $item->setAffiliateplusCommissionItem($commissionObject->getAffiliateplusCommissionItem());
                    $extraContent[$program->getId()]['program_name'] = $program->getName();
                    $extraContent[$program->getId()]['order_item_ids'][] = $item->getProduct()->getId();
                    $extraContent[$program->getId()]['order_item_names'][] = $item->getName();
                    if (!isset($extraContent[$program->getId()]['total_amount']))
                        $extraContent[$program->getId()]['total_amount'] = 0;
                    // Changed By Adam: 19/09/2014: Fix loi sai total amount o program transaction khi mua 1 san pham qty lon hon 1
//                            $extraContent[$program->getId()]['total_amount'] += $child->getBasePrice();
                    $extraContent[$program->getId()]['total_amount'] += $item->getBasePrice() * $item->getQtyOrdered();
                    if (!isset($extraContent[$program->getId()]['commission']))
                        $extraContent[$program->getId()]['commission'] = 0;
                    $extraContent[$program->getId()]['commission'] += $commissionObject->getCommission();
                }
            }
            $commissionObj->setCommission($commission);
            $commissionObj->setOrderItemIds($orderItemIds);
            $commissionObj->setOrderItemNames($orderItemNames);
            $commissionObj->setCommissionItems($commissionItems);
            $commissionObj->setExtraContent($extraContent);
            $commissionObj->setTierCommissions($tierCommissions);
            return $this;
        }
        return $this;
    }

    /* end calculate Commission */

    public function createdTransaction($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $transaction = $observer->getEvent()->getTransaction();
        $order = $observer->getEvent()->getOrder();

        $extraContent = $transaction->getExtraContent();
        $originalCommission = $transaction->getOriginalCommission();
        if ($extraContent && count($extraContent)) {
            $transactionModel = Mage::getModel('affiliateplusprogram/transaction')
                    ->setTransactionId($transaction->getId())
                    ->setOrderId($transaction->getOrderId())
                    ->setOrderNumber($transaction->getOrderNumber())
                    ->setAccountId($transaction->getAccountId())
                    ->setAccountName($transaction->getAccountName());

            $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId());
            foreach ($extraContent as $programId => $programData) {
                $transactionModel->addData($programData);
                $transactionModel->setOrderItemIds(implode(',', $programData['order_item_ids']))
                        ->setOrderItemNames(implode(',', $programData['order_item_names']))
                        ->setProgramId($programId)
                        ->setCommission($programData['commission'])
                        ->setId(null)->save();
                $program->load($programId);
                $program->setTotalSalesAmount($program->getTotalSalesAmount() + $transactionModel->getTotalAmount())->orgSave();
            }

            if ($transaction->getDefaultCommission())
                $transactionModel->setOrderItemIds(implode(',', $transaction->getDefaultItemIds()))
                        ->setOrderItemNames(implode(',', $transaction->getDefaultItemNames()))
                        ->setProgramId(0)
                        ->setProgramName($this->_getHelper()->__('Affiliate Program'))
                        ->setCommission($transaction->getDefaultCommission())
                        ->setTotalAmount($transaction->getDefaultAmount())
                        ->setId(null)->save();
        }
        return $this;
    }

    /* Magic 26/11/2012 change number of account when delete customer */

    public function customerDeleteBefore($observer) {
        /* hainh edit 25-04-2014 */
        if (!Mage::helper('affiliateplusprogram')->isPluginEnabled()) {
            return;
        }
        $customer = $observer->getEvent()->getCustomer();
        $affiliateAccount = Mage::getModel('affiliateplus/account')->loadByCustomer($customer);
        $collection = Mage::getModel('affiliateplusprogram/account')->getCollection();
        $collection->addFieldToFilter('account_id', $affiliateAccount->getId());
        foreach ($collection as $value) {
            $program = Mage::getModel('affiliateplusprogram/program')->load($value->getProgramId());
            $numAccount = $program->getNumAccount();
            try {
                $program->setNumAccount($numAccount - 1);
                $program->save();
            } catch (Exception $e) {
                
            }
        }
        return $this;
    }

    /*
     * Changed By Adam to solve the problem of invoice tung phan 25/08/2014
     */

    public function update_commission_to_affiliateplusprogram_transaction_partial_invoice($observer) {
        $transaction = $observer->getTransaction();
        $order = $observer->getOrder();
        $programTransactions = Mage::getModel('affiliateplusprogram/transaction')->getCollection()
                ->addFieldToFilter('transaction_id', $transaction->getId());
        try {
            foreach ($programTransactions as $programTransaction) {
                $commission = 0;
                $orderItemIds = explode(",", $programTransaction->getOrderItemIds());

                foreach ($order->getAllItems() as $item) {
                    if ($item->getAffiliateplusCommission()) {
                        if (in_array($item->getProductId(), $orderItemIds)) {
                            $affiliateplusCommissionItem = explode(",", $item->getAffiliateplusCommissionItem());
                            $totalComs = array_sum($affiliateplusCommissionItem);

                            $commission += $totalComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                        }
                    }
                }
                if ($commission)
                    $programTransaction->setCommission($commission)->save();
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            die();
        }
    }

    public function update_affiliateplusprogram_transaction_partial_refund($observer) {
        $transaction = $observer->getTransaction();
        $creditmemo = $observer->getCreditmemo();

        $programTransactions = Mage::getModel('affiliateplusprogram/transaction')->getCollection()
                ->addFieldToFilter('transaction_id', $transaction->getId());

        try {
            foreach ($programTransactions as $programTransaction) {
                $commission = 0;
                $orderItemIds = explode(",", $programTransaction->getOrderItemIds());

                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $orderItem = $creditmemoItem->getOrderItem();

                    if (in_array($orderItem->getProductId(), $orderItemIds)) {

                        $affiliateplusCommissionItem = explode(",", $orderItem->getAffiliateplusCommissionItem());
                        $totalComs = array_sum($affiliateplusCommissionItem);

                        $commission += $totalComs * $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
                    }
                }

                if ($commission) {
                    $programTransaction->setCommission($programTransaction->getCommission() - $commission)->save();
                }
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            die();
        }
    }

    /**
     * Changed By Adam 06/10/2014
     * Fix bug: cancel order => cancel transactiion, commission and discount in transaction = 0 but in program_transaction, commission still stays
     * @param type $observer
     */
    public function affiliateplus_cancel_transaction_multipleprogram($observer) {
        $transaction = $observer->getTransaction();
        $transactionStatus = $observer->getStatus();
        $programTransaction = Mage::getModel('affiliateplusprogram/transaction')->load($transaction->getId(), "transaction_id");
        if ($programTransaction && $programTransaction->getId()) {
            $programTransaction->setCommission(0)
                    ->save();
        }
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

}
