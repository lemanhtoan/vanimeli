<?php

class Magestore_Affiliateplus_Model_Observer {

    /**
     * get Config Helper 
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    public function productGetFinalPrice($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'cart')
            return $this;
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account)
            if (!$this->_checkAffiliateParam())     //hainh add this line 21-04-2014
                return $this;
        $product = $observer['product'];
        $product->setData('final_price', $this->_getFinalPrice($product, $product->getData('final_price')));
    }

    public function productListCollection($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'cart')
            return $this;
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account)
            if (!$this->_checkAffiliateParam())     //hainh add this line 21-04-2014
                return $this;
        $productCollection = $observer['collection'];
        foreach ($productCollection as $product)
            $product->setData('final_price', $this->_getFinalPrice($product, $product->getData('final_price')));
    }

    /* hainh update 21-04-2014 add this function for checking param when there's no affiliate cookie
     * because of cookie saving delay
     * return true if param is true, false if param is false
     */

    protected function _checkAffiliateParam() {
        $request = Mage::app()->getRequest();
        $accountCode = Mage::app()->getRequest()->getParam('acc');
        // hainh 29-07-2014
        if (!$accountCode || ($accountCode == '')) {
            $paramList = Mage::getStoreConfig('affiliateplus/refer/url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $request->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != ''))
                    break;
            }
        }
        $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('account_id', $accountCode)->getFirstItem();
        if ($account->getId())
            $accountCode = $account->getIdentifyCode();
        //end editing
        if (!$accountCode)
            return false;

        if ($account = Mage::getSingleton('affiliateplus/session')->getAccount())
            if ($account->getIdentifyCode() == $accountCode)
                return false;

        $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
        if (!$account->getId())
            return false;

        $storeId = Mage::app()->getStore()->getId();
        if (!$storeId)
            return false;

        return true;
    }

    protected function _getFinalPrice($product, $price) {
        $discountedObj = new Varien_Object(array(
            'price' => $price,
            'discounted' => false,
        ));

        Mage::dispatchEvent('affiliateplus_product_get_final_price', array(
            'product' => $product,
            'discounted_obj' => $discountedObj,
        ));

        if ($discountedObj->getDiscounted())
            return $discountedObj->getPrice();
        $price = $discountedObj->getPrice();

        $discountType = $this->_getConfigHelper()->getDiscountConfig('discount_type');
        $discountValue = $this->_getConfigHelper()->getDiscountConfig('discount');
        if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
            if ($this->_getConfigHelper()->getDiscountConfig('use_secondary')) {
                $discountType = $this->_getConfigHelper()->getDiscountConfig('secondary_type');
                $discountValue = $this->_getConfigHelper()->getDiscountConfig('secondary_discount');
            }
        }
        if ($discountType == 'fixed' || $discountType == 'cart_fixed'
        ) {
            $price -= floatval($discountValue);
        } elseif ($discountType == 'percentage') {
            $price -= floatval($discountValue) / 100 * $price;
        }
        if ($price < 0)
            return 0;
        return $price;
    }

    public function controllerActionPredispatch($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $controller = $observer['controller_action'];
        $request = $controller->getRequest();
        $storeId = Mage::app()->getStore()->getId();
        /* Add event before run dispatch of affiliate system - added by David (01/11) */
        Mage::dispatchEvent('affiliateplus_controller_action_predispatch', array(
            'request' => $request
        ));

        /* magic add call funtion saveClickAction 23/10/2012 */
        $this->saveClickAction($observer);
        /* end */
        $accountCode = $request->getParam('acc','');
        // Added By Adam (31/08/2016): save cookie by account id from sub store url
        $account = $this->_getAccountById($request);
        if($account && $account->getStatus() == 1){
            $accountCode = $account->getIdentifyCode();
        }
        // hainh 29-07-2014
        if (!$accountCode || ($accountCode == '')) {
            $paramList = Mage::getStoreConfig('affiliateplus/refer/url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $request->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != ''))
                    break;
            }
        }
        // Changed By Adam 12/06/2015 fix issue can't detect affiliate when customer click on the affiliate link on Facebook
        if(strpos($accountCode, "?")) {
            $code = explode("?", $accountCode);
            $accountCode = $code[0];
        }
        
        // Changed By Adam 08/05/2015 fix loi tu lay identify code cua affiliate khac
        if(Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2) {
            $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('account_id', $accountCode)->getFirstItem();
        } else {
            $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('identify_code', $accountCode)->getFirstItem();
        }
        
        if ($account && $account->getId() && $account->getStatus() == 1)
            $accountCode = $account->getIdentifyCode();
        
        if (!$accountCode && $request->getParam('df08b0441bac900')) {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $write = $resource->getConnection('core_write');
            try {
                $select = $read->select()
                        ->from($resource->getTableName('affiliate_referral'), array('customer_id'))
                        ->where("identify_code=?", trim($request->getParam('df08b0441bac900')));
                $result = $read->fetchRow($select);
                $oldCustomerId = $result['customer_id'];
                if ($oldCustomerId)
                    $accountCode = Mage::getModel('affiliateplus/account')
                            ->loadByCustomerId($oldCustomerId)
                            ->getIdentifyCode();
            } catch (Exception $e) {
                
            }
        }

        if (!$accountCode)
            return $this;

        if ($account = Mage::getSingleton('affiliateplus/session')->getAccount())
            //      Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
            if (!Mage::helper('affiliateplus/config')->allowAffiliateToGetCommissionFromHisPurchase($storeId) && $account->getIdentifyCode() == $accountCode)
                return $this;

        /* Magic 19/10/2012 */

        $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
        if (!$account->getId())
            return $this;

        if (!$storeId)
            return $this;

        /* David - remove storage tracking to referer table
          $ipAddress = $request->getClientIp();
          $refererModel = Mage::getModel('affiliateplus/referer');

          $refererCollection = $refererModel->getCollection()
          ->addFieldToFilter('account_id', $account->getId());
          if (!in_array($ipAddress, $refererCollection->getIpListArray())) {
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }
          }

          $account->setStoreId($storeId)->load($account->getId());
          $refererCollection->addFieldToFilter('store_id', $storeId);
          if (!in_array($ipAddress, $refererCollection->getIpListArray()))
          if ($account->getUniqueClicksInStore())
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          else
          $account->setUniqueClicks(1);
          $account->setTotalClicks($account->getTotalClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }

          $httpReferrerInfo = parse_url($request->getServer('HTTP_REFERER'));
          $referer = isset($httpReferrerInfo['host']) ? $httpReferrerInfo['host'] : '';
          $refererModel->loadExistReferer($account->getId(), $referer, $storeId, $request->getOriginalRequest()->getPathInfo());
          //Zend_Debug::dump($refererModel->getData());die('1');
          Mage::dispatchEvent('affiliateplus_referrer_load_existed', array(
          'referrer_model' => $refererModel,
          'controller_action' => $controller,
          ));

          try {
          $refererModel->setIpAddress($ipAddress)->save();
          } catch (Exception $e) {

          }
         */

        /*
         * end
         */
        $expiredTime = $this->_getConfigHelper()->getGeneralConfig('expired_time');

        // hainh call function and comment the lines below due to update 4.2 22-07-2014
        Mage::helper('affiliateplus/cookie')->saveCookie($accountCode, $expiredTime, false, $controller);

        /*
          $cookie = Mage::getSingleton('core/cookie');
          if ($expiredTime)
          $cookie->setLifeTime(intval($expiredTime) * 86400);

          $current_index = $cookie->get('affiliateplus_map_index');

          $addCookie = new Varien_Object(array(
          'existed' => false,
          ));
          for ($i = intval($current_index); $i > 0; $i--) {
          if ($cookie->get("affiliateplus_account_code_$i") == $accountCode) {
          $addCookie->setExisted(true);
          $addCookie->setIndex($i);
          Mage::dispatchEvent('affiliateplus_controller_action_predispatch_add_cookie', array(
          'request' => $request,
          'add_cookie' => $addCookie,
          'cookie' => $cookie,
          ));
          if ($addCookie->getExisted()) {
          // change latest account
          $curI = intval($current_index);
          for ($j = $i; $j < $curI; $j++) {
          $cookie->set(
          "affiliateplus_account_code_$j", $cookie->get("affiliateplus_account_code_" . intval($j + 1))
          );
          }
          $cookie->set("affiliateplus_account_code_$curI", $accountCode);
          return $this;
          }
          }
          }
          $current_index = $current_index ? intval($current_index) + 1 : 1;
          $cookie->set('affiliateplus_map_index', $current_index);

          $cookie->set("affiliateplus_account_code_$current_index", $accountCode);

          $cookieParams = new Varien_Object(array(
          'params' => array(),
          ));
          Mage::dispatchEvent('affiliateplus_controller_action_predispatch_observer', array(
          'controller_action' => $controller,
          'cookie_params' => $cookieParams,
          'cookie' => $cookie,
          ));

          foreach ($cookieParams->getParams() as $key => $value)
          $cookie->set("affiliateplus_$key" . "_$current_index", $value);
         */
        /* Magic comment 19/10/2012 and put upward  */
        /*
          $account = Mage::getModel('affiliateplus/account')->loadByIdentifyCode($accountCode);
          if (!$account->getId())
          return $this;
          $storeId = Mage::app()->getStore()->getId();
          if (!$storeId)
          return $this;
          $ipAddress = $request->getClientIp();
          $refererModel = Mage::getModel('affiliateplus/referer');

          $refererCollection = $refererModel->getCollection()
          ->addFieldToFilter('account_id', $account->getId());
          if (!in_array($ipAddress, $refererCollection->getIpListArray())) {
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }
          }

          $account->setStoreId($storeId)->load($account->getId());
          $refererCollection->addFieldToFilter('store_id', $storeId);
          if (!in_array($ipAddress, $refererCollection->getIpListArray()))
          if ($account->getUniqueClicksInStore())
          $account->setUniqueClicks($account->getUniqueClicks() + 1);
          else
          $account->setUniqueClicks(1);
          $account->setTotalClicks($account->getTotalClicks() + 1);
          try {
          $account->save();
          } catch (Exception $e) {

          }

          $httpReferrerInfo = parse_url($request->getServer('HTTP_REFERER'));
          $referer = isset($httpReferrerInfo['host']) ? $httpReferrerInfo['host'] : '';
          $refererModel->loadExistReferer($account->getId(), $referer, $storeId, $request->getOriginalRequest()->getPathInfo());
          //Zend_Debug::dump($refererModel->getData());die('1');
          Mage::dispatchEvent('affiliateplus_referrer_load_existed', array(
          'referrer_model' => $refererModel,
          'controller_action' => $controller,
          ));

          try {
          $refererModel->setIpAddress($ipAddress)->save();
          } catch (Exception $e) {

          }
         */
        return $this;
    }

    // edit by Sally
    public function checkout_submit_all_after($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $orders = $observer['orders'];
        if ($orders and count($orders))
            foreach ($orders as $order) {
                // check to run this function 1 time for 1 order
                if (Mage::getSingleton('core/session')->getData("affiliateplus_order_placed_" . $order->getId())) {
                    return $this;
                }
                Mage::getSingleton('core/session')->setData("affiliateplus_order_placed_" . $order->getId(), true);

                // Use Store Credit to Checkout
                if ($baseAmount = $order->getBaseAffiliateCredit()) {
                    $session = Mage::getSingleton('checkout/session');
                    $session->setUseAffiliateCredit('');
                    $session->setAffiliateCredit(0);

                    $account = Mage::getSingleton('affiliateplus/session')->getAccount();
                    $payment = Mage::getModel('affiliateplus/payment')
                            ->setPaymentMethod('credit')
                            ->setAmount(-$baseAmount)
                            ->setAccountId($account->getId())
                            ->setAccountName($account->getName())
                            ->setAccountEmail($account->getEmail())
                            ->setRequestTime(now())
                            ->setStatus(3)
                            ->setIsRequest(1)
                            ->setIsPayerFee(0)
                            ->setData('is_created_by_recurring', 1)
                            ->setData('is_refund_balance', 1);
                    if (Mage::helper('affiliateplus/config')->getSharingConfig('balance') == 'store') {
                        $payment->setStoreIds($order->getStoreId());
                    }
                    $paymentMethod = $payment->getPayment();
                    $paymentMethod->addData(array(
                        'order_id' => $order->getId(),
                        'order_increment_id' => $order->getIncrementId(),
                        'base_paid_amount' => -$baseAmount,
                        'paid_amount' => -$order->getAffiliateCredit(),
                    ));
                    try {
                        $payment->save();
                        $paymentMethod->savePaymentMethodInfo();
                    } catch (Exception $e) {
                        
                    }
                }

                if (!$order->getBaseSubtotal()) {
                    return $this;
                }
                $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
                $account = '';
                foreach ($affiliateInfo as $info)
                    if ($info['account']) {
                        $account = $info['account'];
                        break;
                    }

                if ($account && $account->getId()) {
                    // Log affiliate tracking referal - only when has sales
                    if ($this->_getConfigHelper()->getCommissionConfig('life_time_sales')) {
                        $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
                        if ($order->getCustomerId()) {
                            $tracksCollection->getSelect()
                                    ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?", $order->getCustomerEmail());
                        } else {
                            $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
                        }
                        if (!$tracksCollection->getSize()) {
                            try {
                                Mage::getModel('affiliateplus/tracking')->setData(array(
                                    'account_id' => $account->getId(),
                                    'customer_id' => $order->getCustomerId(),
                                    'customer_email' => $order->getCustomerEmail(),
                                    'created_time' => now()
                                ))->save();
                            } catch (Exception $e) {
                                
                            }
                        }
                    }

                    $baseDiscount = $order->getBaseAffiliateplusDiscount();
                    //$maxCommission = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
                    // Before calculate commission
                    $commissionObj = new Varien_Object(array(
                        'commission' => 0,
                        'default_commission' => true,
                        'order_item_ids' => array(),
                        'order_item_names' => array(),
                        'commission_items' => array(),
                        'extra_content' => array(),
                        'tier_commissions' => array(),
                    ));
                    Mage::dispatchEvent('affiliateplus_calculate_commission_before', array(
                        'order' => $order,
                        'affiliate_info' => $affiliateInfo,
                        'commission_obj' => $commissionObj,
                    ));

                    $commissionType = $this->_getConfigHelper()->getCommissionConfig('commission_type');
                    $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('commission'));
                    if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                        if ($this->_getConfigHelper()->getCommissionConfig('use_secondary')) {
                            $commissionType = $this->_getConfigHelper()->getCommissionConfig('secondary_type');
                            $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('secondary_commission'));
                        }
                    }
                    $commission = $commissionObj->getCommission();
                    $orderItemIds = $commissionObj->getOrderItemIds();
                    $orderItemNames = $commissionObj->getOrderItemNames();
                    $commissionItems = $commissionObj->getCommissionItems();
                    $extraContent = $commissionObj->getExtraContent();
                    $tierCommissions = $commissionObj->getTierCommissions();

                    $defaultItemIds = array();
                    $defaultItemNames = array();
                    $defaultAmount = 0;
                    $defCommission = 0;
                    if ($commissionValue && $commissionObj->getDefaultCommission()) {
                        if ($commissionType == 'percentage') {
                            if ($commissionValue > 100)
                                $commissionValue = 100;
                            if ($commissionValue < 0)
                                $commissionValue = 0;
                        }

                        foreach ($order->getAllItems() as $item) {
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            if (in_array($item->getId(), $commissionItems)) {
                                continue;
                            }

                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                // $childHasCommission = false;
                                foreach ($item->getChildrenItems() as $child) {
                                    if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                                        $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                                    else
                                        $baseProfit = $child->getBasePrice();
                                    $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                                    if ($baseProfit <= 0)
                                        continue;

                                    // $childHasCommission = true;
                                    if ($commissionType == 'fixed')
                                        $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                                    elseif ($commissionType == 'percentage')
                                        $defaultCommission = $baseProfit * $commissionValue / 100;

                                    $commissionObj = new Varien_Object(array(
                                        'profit' => $baseProfit,
                                        'commission' => $defaultCommission,
                                        'tier_commission' => array(),
                                    ));
                                    Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                                        'item' => $child,
                                        'account' => $account,
                                        'commission_obj' => $commissionObj
                                    ));

                                    if ($commissionObj->getTierCommission())
                                        $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                                    $commission += $commissionObj->getCommission();
                                    $child->setAffiliateplusCommission($commissionObj->getCommission());

                                    $defCommission += $commissionObj->getCommission();
                                    $defaultAmount += $child->getBasePrice();

                                    $orderItemIds[] = $child->getProduct()->getId();
                                    $orderItemNames[] = $child->getName();

                                    $defaultItemIds[] = $child->getProduct()->getId();
                                    $defaultItemNames[] = $child->getName();
                                }
                                // if ($childHasCommission) {
                                // $orderItemIds[] = $item->getProduct()->getId();
                                // $orderItemNames[] = $item->getName();
                                // $defaultItemIds[] = $item->getProduct()->getId();
                                // $defaultItemNames[] = $item->getName();
                                // }
                            } else {
                                if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                                    $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                                else
                                    $baseProfit = $item->getBasePrice();
                                $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                                if ($baseProfit <= 0)
                                    continue;
                                //jack
                                if ($item->getProduct())
                                    $inProductId = $item->getProduct()->getId();
                                else
                                    $inProductId = $item->getProductId();
                                //
                                $orderItemIds[] = $inProductId;
                                $orderItemNames[] = $item->getName();

                                $defaultItemIds[] = $inProductId;
                                $defaultItemNames[] = $item->getName();

                                if ($commissionType == 'fixed')
                                    $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                                elseif ($commissionType == 'percentage')
                                    $defaultCommission = $baseProfit * $commissionValue / 100;

                                $commissionObj = new Varien_Object(array(
                                    'profit' => $baseProfit,
                                    'commission' => $defaultCommission,
                                    'tier_commission' => array(),
                                ));
                                Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                                    'item' => $item,
                                    'account' => $account,
                                    'commission_obj' => $commissionObj
                                ));

                                if ($commissionObj->getTierCommission())
                                    $tierCommissions[$item->getId()] = $commissionObj->getTierCommission();
                                $commission += $commissionObj->getCommission();
                                $item->setAffiliateplusCommission($commissionObj->getCommission());

                                $defCommission += $commissionObj->getCommission();
                                $defaultAmount += $item->getBasePrice();
                            }
                        }
                    }

                    if (!$baseDiscount && !$commission)
                        return $this;

                    // $customer = Mage::getSingleton('customer/session')->getCustomer();
                    // Create transaction
                    $transactionData = array(
                        'account_id' => $account->getId(),
                        'account_name' => $account->getName(),
                        'account_email' => $account->getEmail(),
                        'customer_id' => $order->getCustomerId(), // $customer->getId(),
                        'customer_email' => $order->getCustomerEmail(), // $customer->getEmail(),
                        'order_id' => $order->getId(),
                        'order_number' => $order->getIncrementId(),
                        'order_item_ids' => implode(',', $orderItemIds),
                        'order_item_names' => implode(',', $orderItemNames),
                        'total_amount' => $order->getBaseSubtotal(),
                        'discount' => $baseDiscount,
                        'commission' => $commission,
                        'created_time' => now(),
                        'status' => '2',
                        'store_id' => Mage::app()->getStore()->getId(),
                        'extra_content' => $extraContent,
                        'tier_commissions' => $tierCommissions,
                        //'ratio'			=> $ratio,
                        //'original_commission'	=> $originalCommission,
                        'default_item_ids' => $defaultItemIds,
                        'default_item_names' => $defaultItemNames,
                        'default_commission' => $defCommission,
                        'default_amount' => $defaultAmount,
                        'type' => 3,
                    );
                    if ($account->getUsingCoupon()) {
                        $session = Mage::getSingleton('checkout/session');
                        $transactionData['coupon_code'] = $session->getData('affiliate_coupon_code');
                        if ($program = $account->getUsingProgram()) {
                            $transactionData['program_id'] = $program->getId();
                            $transactionData['program_name'] = $program->getName();
                        } else {
                            $transactionData['program_id'] = 0;
                            $transactionData['program_name'] = 'Affiliate Program';
                        }
                        $session->unsetData('affiliate_coupon_code');
                        $session->unsetData('affiliate_coupon_data');
                    }

                    $transaction = Mage::getModel('affiliateplus/transaction')->setData($transactionData)->setId(null);

                    Mage::dispatchEvent('affiliateplus_calculate_commission_after', array(
                        'transaction' => $transaction,
                        'order' => $order,
                        'affiliate_info' => $affiliateInfo,
                    ));

                    try {
                        $transaction->save();
                        Mage::dispatchEvent('affiliateplus_recalculate_commission', array(
                            'transaction' => $transaction,
                            'order' => $order,
                            'affiliate_info' => $affiliateInfo,
                        ));

                        if ($transaction->getIsChangedData())
                            $transaction->save();
                        Mage::dispatchEvent('affiliateplus_created_transaction', array(
                            'transaction' => $transaction,
                            'order' => $order,
                            'affiliate_info' => $affiliateInfo,
                        ));

                        $transaction->sendMailNewTransactionToAccount();
                        $transaction->sendMailNewTransactionToSales();
                    } catch (Exception $e) {
                        // Exception
                    }
                }
            }
    }

    // end by Sally
    public function orderPlaceAfter($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $order = $observer['order'];

        /*Added By Adam (27/08/2016): create transaction from existed order*/
        $affiliateAccount = $observer['affiliate'];
        $transactionObj = $observer['transaction'];

        // check to run this function 1 time for 1 order
        if (Mage::getSingleton('core/session')->getData("affiliateplus_order_placed_" . $order->getId())) {
            return $this;
        }
        Mage::getSingleton('core/session')->setData("affiliateplus_order_placed_" . $order->getId(), true);

        // Use Store Credit to Checkout
        if ($baseAmount = $order->getBaseAffiliateCredit()) {
            $session = Mage::getSingleton('checkout/session');
            $session->setUseAffiliateCredit('');
            $session->setAffiliateCredit(0);

            $account = Mage::getSingleton('affiliateplus/session')->getAccount();
            $payment = Mage::getModel('affiliateplus/payment')
                    ->setPaymentMethod('credit')
                    ->setAmount(-$baseAmount)
                    ->setAccountId($account->getId())
                    ->setAccountName($account->getName())
                    ->setAccountEmail($account->getEmail())
                    ->setRequestTime(now())
                    ->setStatus(3)
                    ->setIsRequest(1)
                    ->setIsPayerFee(0)
                    ->setData('is_created_by_recurring', 1)
                    ->setData('is_refund_balance', 1);
            if (Mage::helper('affiliateplus/config')->getSharingConfig('balance') == 'store') {
                $payment->setStoreIds($order->getStoreId());
            }
            $paymentMethod = $payment->getPayment();
            $paymentMethod->addData(array(
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'base_paid_amount' => -$baseAmount,
                'paid_amount' => -$order->getAffiliateCredit(),
            ));
            try {
                $payment->save();
                $paymentMethod->savePaymentMethodInfo();
            } catch (Exception $e) {
                
            }
        }

        if (!$order->getBaseSubtotal()) {
            return $this;
        }

        /*Added By Adam (27/08/2016): create transaction from existed order*/
        if($affiliateAccount && $affiliateAccount->getId()) {
            $info[$affiliateAccount->getIdentifyCode()] = array(
                'index' => 1,
                'code'  => $affiliateAccount->getIdentifyCode(),
                'account'   => $affiliateAccount,
            );
            $cookie = Mage::getSingleton('core/cookie');
            $infoObj = new Varien_Object(array(
                'info'	=> $info,
            ));
            Mage::dispatchEvent('affiliateplus_get_affiliate_info',array(
                'cookie'	=> $cookie,
                'info_obj'	=> $infoObj,
            ));
            $affiliateInfo = $infoObj->getInfo();

        } else {
            $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        }

        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }

        if ($account && $account->getId()) {

            // Log affiliate tracking referal - only when has sales
            if ($this->_getConfigHelper()->getCommissionConfig('life_time_sales')) {
                $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
                if ($order->getCustomerId()) {
                    $tracksCollection->getSelect()
                            ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?", $order->getCustomerEmail());
                } else {
                    $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
                }
                if (!$tracksCollection->getSize()) {
                    try {
                        Mage::getModel('affiliateplus/tracking')->setData(array(
                            'account_id' => $account->getId(),
                            'customer_id' => $order->getCustomerId(),
                            'customer_email' => $order->getCustomerEmail(),
                            'created_time' => now()
                        ))->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }

            $baseDiscount = $order->getBaseAffiliateplusDiscount();
            //$maxCommission = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
            // Before calculate commission
            $commissionObj = new Varien_Object(array(
                'commission' => 0,
                'default_commission' => true,
                'order_item_ids' => array(),
                'order_item_names' => array(),
                'commission_items' => array(),
                'extra_content' => array(),
                'tier_commissions' => array(),
                    //'affiliateplus_commission_item' => '',
            ));
            Mage::dispatchEvent('affiliateplus_calculate_commission_before', array(
                'order' => $order,
                'affiliate_info' => $affiliateInfo,
                'commission_obj' => $commissionObj,
            ));

            $commissionType = $this->_getConfigHelper()->getCommissionConfig('commission_type');
            $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('commission'));
            if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                if ($this->_getConfigHelper()->getCommissionConfig('use_secondary')) {
                    $commissionType = $this->_getConfigHelper()->getCommissionConfig('secondary_type');
                    $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('secondary_commission'));
                }
            }
            $commission = $commissionObj->getCommission();
            $orderItemIds = $commissionObj->getOrderItemIds();
            $orderItemNames = $commissionObj->getOrderItemNames();
            $commissionItems = $commissionObj->getCommissionItems();
            $extraContent = $commissionObj->getExtraContent();
            $tierCommissions = $commissionObj->getTierCommissions();
//            $affiliateplusCommissionItem = $commissionObj->getAffiliateplusCommissionItem();

            $defaultItemIds = array();
            $defaultItemNames = array();
            $defaultAmount = 0;
            $defCommission = 0;

            /* Changed By Adam to customize function: Commission for whole cart 22/07/2014 */
            // Calculate the total price of items ~~ baseSubtotal
            $baseItemsPrice = 0;
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }

                // Kiem tra xem item da tinh trong program nao chua, neu roi thi ko tinh nua
                if (in_array($item->getId(), $commissionItems)) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {

                    foreach ($item->getChildrenItems() as $child) {
                        $baseItemsPrice += $item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount());
                        //$baseItemsPrice += $item->getQtyOrdered() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                    }
                } elseif ($item->getProduct()) {

                    $baseItemsPrice += $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
                }
            }

            if ($commissionValue && $commissionObj->getDefaultCommission()) {
                if ($commissionType == 'percentage') {
                    if ($commissionValue > 100)
                        $commissionValue = 100;
                    if ($commissionValue < 0)
                        $commissionValue = 0;
                }

                foreach ($order->getAllItems() as $item) {
                    $affiliateplusCommissionItem = '';
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (in_array($item->getId(), $commissionItems)) {
                        continue;
                    }

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        // $childHasCommission = false;
                        foreach ($item->getChildrenItems() as $child) {
                            $affiliateplusCommissionItem = '';
                            if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                                $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                            else
                                $baseProfit = $child->getBasePrice();
                            $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount();
                            if ($baseProfit <= 0)
                                continue;

                            // $childHasCommission = true;
                            /* Changed By Adam: Commission for whole cart 22/07/2014 */
                            if ($commissionType == "cart_fixed") {
                                $commissionValue = min($commissionValue, $baseItemsPrice);
                                $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount() - $child->getBaseAffiliateplusCredit() - $child->getRewardpointsBaseDiscount();
                                $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                                $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                            } elseif ($commissionType == 'fixed')
                                $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                            elseif ($commissionType == 'percentage')
                                $defaultCommission = $baseProfit * $commissionValue / 100;

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $affiliateplusCommissionItem .= $defaultCommission . ",";
                            $commissionObj = new Varien_Object(array(
                                'profit' => $baseProfit,
                                'commission' => $defaultCommission,
                                'tier_commission' => array(),
                                'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                                'affiliateplus_commission_item' => $affiliateplusCommissionItem     // Added By Adam 14/08/2014
                            ));
                            Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                                'item' => $child,
                                'account' => $account,
                                'commission_obj' => $commissionObj
                            ));

                            if ($commissionObj->getTierCommission())
                                $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                            $commission += $commissionObj->getCommission();
                            $child->setAffiliateplusCommission($commissionObj->getCommission());

                            // Changed By Adam 14/08/2014: Invoice tung phan
                            $child->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());

                            $defCommission += $commissionObj->getCommission();
                            $defaultAmount += $child->getBasePrice();

                            $orderItemIds[] = $child->getProduct()->getId();
                            $orderItemNames[] = $child->getName();

                            $defaultItemIds[] = $child->getProduct()->getId();
                            $defaultItemNames[] = $child->getName();
                        }
                        // if ($childHasCommission) {
                        // $orderItemIds[] = $item->getProduct()->getId();
                        // $orderItemNames[] = $item->getName();
                        // $defaultItemIds[] = $item->getProduct()->getId();
                        // $defaultItemNames[] = $item->getName();
                        // }
                    } else {
                        if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                            $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                        else
                            $baseProfit = $item->getBasePrice();
                        $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
                        if ($baseProfit <= 0)
                            continue;
                        //jack
                        if ($item->getProduct())
                            $inProductId = $item->getProduct()->getId();
                        else
                            $inProductId = $item->getProductId();
                        //
                        $orderItemIds[] = $inProductId;
                        $orderItemNames[] = $item->getName();

                        $defaultItemIds[] = $inProductId;
                        $defaultItemNames[] = $item->getName();

                        /* Changed BY Adam 22/07/2014 */
                        if ($commissionType == 'cart_fixed') {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount() - $item->getBaseAffiliateplusCredit() - $item->getRewardpointsBaseDiscount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                        } elseif ($commissionType == 'fixed')
                            $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                        elseif ($commissionType == 'percentage')
                            $defaultCommission = $baseProfit * $commissionValue / 100;

                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $affiliateplusCommissionItem .= $defaultCommission . ",";
                        $commissionObj = new Varien_Object(array(
                            'profit' => $baseProfit,
                            'commission' => $defaultCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                            'affiliateplus_commission_item' => $affiliateplusCommissionItem, // Added By Adam 14/08/2014
                        ));
                        Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                            'item' => $item,
                            'account' => $account,
                            'commission_obj' => $commissionObj
                        ));

                        if ($commissionObj->getTierCommission())
                            $tierCommissions[$item->getId()] = $commissionObj->getTierCommission();
                        $commission += $commissionObj->getCommission();
                        $item->setAffiliateplusCommission($commissionObj->getCommission());
                        // Changed By Adam 14/08/2014: Invoice tung phan
                        $item->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());

                        $defCommission += $commissionObj->getCommission();
                        $defaultAmount += $item->getBasePrice();
                    }
                }
            }
            if (!$baseDiscount && !$commission)
                return $this;

            // $customer = Mage::getSingleton('customer/session')->getCustomer();
            // Create transaction
            $transactionData = array(
                'account_id' => $account->getId(),
                'account_name' => $account->getName(),
                'account_email' => $account->getEmail(),
                'customer_id' => $order->getCustomerId(), // $customer->getId(),
                'customer_email' => $order->getCustomerEmail(), // $customer->getEmail(),
                'order_id' => $order->getId(),
                'order_number' => $order->getIncrementId(),
                'order_item_ids' => implode(',', $orderItemIds),
                'order_item_names' => implode(',', $orderItemNames),
                'total_amount' => $order->getBaseSubtotal(),
                'discount' => $baseDiscount,
                'commission' => $commission,
                'created_time' => now(),
                'status' => '2',
                'store_id' => $order->getStoreId(),
                'extra_content' => $extraContent,
                'tier_commissions' => $tierCommissions,
                //'ratio'			=> $ratio,
                //'original_commission'	=> $originalCommission,
                'default_item_ids' => $defaultItemIds,
                'default_item_names' => $defaultItemNames,
                'default_commission' => $defCommission,
                'default_amount' => $defaultAmount,
                'type' => 3,
            );
            if ($account->getUsingCoupon()) {
                $session = Mage::getSingleton('checkout/session');
                $transactionData['coupon_code'] = $session->getData('affiliate_coupon_code');
                if ($program = $account->getUsingProgram()) {
                    $transactionData['program_id'] = $program->getId();
                    $transactionData['program_name'] = $program->getName();
                } else {
                    $transactionData['program_id'] = 0;
                    $transactionData['program_name'] = 'Affiliate Program';
                }
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
            }
            //jack
            else {
                $checkProgramByConfig = Mage::getStoreConfig('affiliateplus/program/enable');
                if ($checkProgramByConfig == 0 || !Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram')) {
                    $transactionData['program_id'] = 0;
                    $transactionData['program_name'] = 'Affiliate Program';
                }
            }
            //
            $transaction = Mage::getModel('affiliateplus/transaction')->setData($transactionData)->setId(null);

            Mage::dispatchEvent('affiliateplus_calculate_commission_after', array(
                'transaction' => $transaction,
                'order' => $order,
                'affiliate_info' => $affiliateInfo,
            ));

            try {
                $transaction->save();
                Mage::dispatchEvent('affiliateplus_recalculate_commission', array(
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ));

                if ($transaction->getIsChangedData())
                    $transaction->save();
                Mage::dispatchEvent('affiliateplus_created_transaction', array(
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ));

                $transaction->sendMailNewTransactionToAccount();
                $transaction->sendMailNewTransactionToSales();
                if(is_object($transactionObj))
                    $transactionObj->setTransaction($transaction);
            } catch (Exception $e) {
                // Exception
            }
        }
    }

    public function orderLoadAfter($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $order = $observer->getOrder();
        if ($order->getBaseAffiliateCredit() > -0.0001 || Mage::app()->getStore()->roundPrice($order->getGrandTotal()) > 0 || $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED || $order->isCanceled() || $order->canUnhold()
        ) {
            return $this;
        }
        foreach ($order->getAllItems() as $item) {
            if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                $order->setForcedCanCreditmemo(true);
                return $this;
            }
        }
    }

    /* create new transaction when submit order and edit order - Edit By Jack */

    public function createNewTransaction($order) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
        $currentOrderEdit = Mage::getModel('sales/order')->load($orderId);
        $customerEmail = $currentOrderEdit->getCustomerEmail();
        $originalIncrementId = $currentOrderEdit->getIncrementId();
        $transactionAffiliate = Mage::getModel('affiliateplus/transaction')
                ->getCollection()
                ->addFieldToFilter('order_number', $originalIncrementId)
                ->getFirstItem();
        /* process code in the case :  life time affiliate Edit By Jack */
        $account = '';
        $lifeTimeAff = false;

        // Changed By Adam 15/10/2014: 2014-10-15T07:59:02+00:00 ERR (3): Notice: Undefined variable: couponCode  in C:\xampp\htdocs\project\magento1.5.0.1\app\code\local\Magestore\Affiliateplus\Model\Observer.php on line 1077
        $couponCode = Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code');
        $isEnableCouponPlugin = Mage::helper('core')->isModuleEnabled('Magestore_Affiliatepluscoupon');
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();

        if (!$orderId) {  // when create a new order
            // life time
            $customerOrderId = $order->getCustomerId();
            $accountAndProgramData = Mage::helper('affiliateplus')->getAccountAndProgramData($customerOrderId);
            $programId = $accountAndProgramData->getProgramId();
            $programName = $accountAndProgramData->getProgramName();
            $lifeTimeAff = $accountAndProgramData->getLifetimeAff();
            $account = $accountAndProgramData->getAccount();
        } else {  // when edit order
            if ((!$couponCode && $transactionAffiliate->getCouponCode()) || !$isEnableCouponPlugin) {
                /* when remove coupon of old order or un-enable coupon plugin */
                $accountAndProgramData = new Varien_Object(array(
                    'program_id' => '',
                    'program_name' => '',
                    'account' => $account,
                    'lifetime_aff' => $lifeTimeAff,
                ));
                $customerOrderId = $order->getCustomerId();
                $accountAndProgramData = Mage::helper('affiliateplus')->getAccountAndProgramData($customerOrderId);
                $account = $accountAndProgramData->getAccount();
                if ($account) {  // life time
                    $programId = $accountAndProgramData->getProgramId();
                    $programName = $accountAndProgramData->getProgramName();
                    $lifeTimeAff = $accountAndProgramData->getLifetimeAff();
                } else {  // not life time
                    // get information from old order
                    $accountIdByTransaction = $transactionAffiliate->getAccountId();
                    $account = Mage::getModel('affiliateplus/account')->load($accountIdByTransaction);
                    $programId = $transactionAffiliate->getProgramId();
                    $programName = $transactionAffiliate->getProgramName();
                    if (!$programId && !$programName) {
                        // if program id = null and program = null =>  get information from session    
                        $programData = Mage::getSingleton('checkout/session')->getProgramData();
                        if ($programData) {
                            $programId = $programData->getData('program_id');
                            $programName = $programData->getData('name');
                        }
                    }
                }
            } else {
                $programData = Mage::getSingleton('checkout/session')->getProgramData();
                if (!$couponCode) {
                    if ($programData) {
                        $programId = $programData->getData('program_id');
                        $programName = $programData->getData('name');
                    }
                    $accountIdByTransaction = $transactionAffiliate->getAccountId();
                    $account = Mage::getModel('affiliateplus/account')->load($accountIdByTransaction);
                }
            }
        }
        /* end process code */

        $baseDiscount = $order->getBaseAffiliateplusDiscount();
        //$maxCommission = $order->getBaseGrandTotal() - $order->getBaseShippingAmount();
        // Before calculate commission
        $commissionObj = new Varien_Object(array(
            'commission' => 0,
            'default_commission' => true,
            'order_item_ids' => array(),
            'order_item_names' => array(),
            'commission_items' => array(),
            'extra_content' => array(),
            'tier_commissions' => array(),
        ));
        if (!$isEnableCouponPlugin || !Mage::helper('core')->isModuleEnabled('Magestore_Affiliatepluscoupon')) {
            $session = Mage::getSingleton('checkout/session');
            $session->unsAffiliateCouponCode();
        }
        if ($couponCode && $isEnableCouponPlugin) {
            $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
            foreach ($affiliateInfo as $info)
                if ($info['account']) {
                    $account = $info['account'];
                    break;
                }
            if ($account->getUsingCoupon()) {
                $program = $account->getUsingProgram();
                if ($program) {
                    $programId = $program->getId();
                    $programName = $program->getName();
                } else {
                    $programId = 0;
                    $programName = 'Affiliate Program';
                }
            }
        }
        if (!$account || ($account && !$account->getId()) || $account->getStatus() == 2)
            return $this;
        // Log affiliate tracking referal - only when has sales
        if ($this->_getConfigHelper()->getCommissionConfig('life_time_sales')) {
            $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
            if ($order->getCustomerId()) {
                $tracksCollection->getSelect()
                        ->where("customer_id = {$order->getCustomerId()} OR customer_email = ?", $order->getCustomerEmail());
            } else {
                $tracksCollection->addFieldToFilter('customer_email', $order->getCustomerEmail());
            }
            if (!$tracksCollection->getSize()) {
                try {
                    Mage::getModel('affiliateplus/tracking')->setData(array(
                        'account_id' => $account->getId(),
                        'customer_id' => $order->getCustomerId(),
                        'customer_email' => $order->getCustomerEmail(),
                        'created_time' => now()
                    ))->save();
                } catch (Exception $e) {
                    
                }
            }
        }
        Mage::dispatchEvent('affiliateplus_calculate_commission_before_edit', array(
            'order' => $order,
            'program_id' => $programId,
            'commission_obj' => $commissionObj,
            'account' => $account,
        ));
        $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        $commissionType = $this->_getConfigHelper()->getCommissionConfig('commission_type', $storeId);
        $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('commission', $storeId));
        if (($orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered() > 1) || (!$orderId && Mage::helper('affiliateplus/cookie')->getNumberOrdered())) {
            if ($this->_getConfigHelper()->getCommissionConfig('use_secondary', $storeId)) {
                $commissionType = $this->_getConfigHelper()->getCommissionConfig('secondary_type', $storeId);
                $commissionValue = floatval($this->_getConfigHelper()->getCommissionConfig('secondary_commission', $storeId));
            }
        }
        $commission = $commissionObj->getCommission();
        $orderItemIds = $commissionObj->getOrderItemIds();
        $orderItemNames = $commissionObj->getOrderItemNames();
        $commissionItems = $commissionObj->getCommissionItems();
        $extraContent = $commissionObj->getExtraContent();
        $tierCommissions = $commissionObj->getTierCommissions();

        $defaultItemIds = array();
        $defaultItemNames = array();
        $defaultAmount = 0;
        $defCommission = 0;
        /* set Condition Edit By Jack */
        // if(isset($count) && $count == 0 && count($extraContent) == 0)
        //  return $this;
        /* */
        /* Changed By Adam to customize function: Commission for whole cart 22/07/2014 */
        // Calculate the total price of items ~~ baseSubtotal
        $baseItemsPrice = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            // Kiem tra xem item da tinh trong program nao chua, neu roi thi ko tinh nua
            if (in_array($item->getId(), $commissionItems)) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {

                foreach ($item->getChildrenItems() as $child) {
                    $baseItemsPrice += $item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                    //$baseItemsPrice += $item->getQtyOrdered() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
                }
            } elseif ($item->getProduct()) {

                $baseItemsPrice += $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
            }
        }
        if ($commissionValue && $commissionObj->getDefaultCommission()) {
            if ($commissionType == 'percentage') {
                if ($commissionValue > 100)
                    $commissionValue = 100;
                if ($commissionValue < 0)
                    $commissionValue = 0;
            }

            foreach ($order->getAllItems() as $item) {
                $affiliateplusCommissionItem = '';
                if ($item->getParentItemId()) {
                    continue;
                }
                if (in_array($item->getId(), $commissionItems)) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    // $childHasCommission = false;
                    foreach ($item->getChildrenItems() as $child) {
                        $affiliateplusCommissionItem = '';
                        if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                            $baseProfit = $child->getBasePrice() - $child->getBaseCost();
                        else
                            $baseProfit = $child->getBasePrice();
                        $baseProfit = $child->getQtyOrdered() * $baseProfit - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                        if ($baseProfit <= 0)
                            continue;

                        // $childHasCommission = true;
                        /* Changed By Adam: Commission for whole cart 22/07/2014 */
                        if ($commissionType == "cart_fixed") {
                            $commissionValue = min($commissionValue, $baseItemsPrice);
                            $itemPrice = $child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount();
                            $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                            $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                        } elseif ($commissionType == 'fixed')
                            $defaultCommission = min($child->getQtyOrdered() * $commissionValue, $baseProfit);
                        elseif ($commissionType == 'percentage')
                            $defaultCommission = $baseProfit * $commissionValue / 100;
                        $affiliateplusCommissionItem .= $defaultCommission . ",";
                        $commissionObj = new Varien_Object(array(
                            'profit' => $baseProfit,
                            'commission' => $defaultCommission,
                            'tier_commission' => array(),
                            'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                            'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                        ));
                        Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                            'item' => $child,
                            'account' => $account,
                            'commission_obj' => $commissionObj
                        ));

                        if ($commissionObj->getTierCommission())
                            $tierCommissions[$child->getId()] = $commissionObj->getTierCommission();
                        $commission += $commissionObj->getCommission();
                        $child->setAffiliateplusCommission($commissionObj->getCommission());
                        $child->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());
                        $defCommission += $commissionObj->getCommission();
                        $defaultAmount += $child->getBasePrice();
                        // Changed by Adam 15/10/2014
                        // $orderItemIds[] = $child->getProduct()->getId();
                        $orderItemIds[] = $child->getProductId();
                        $orderItemNames[] = $child->getName();
                        // Changed by Adam 15/10/2014
                        // $defaultItemIds[] = $child->getProduct()->getId();
                        $defaultItemIds[] = $child->getProductId();
                        $defaultItemNames[] = $child->getName();
                    }
                    // if ($childHasCommission) {
                    // $orderItemIds[] = $item->getProduct()->getId();
                    // $orderItemNames[] = $item->getName();
                    // $defaultItemIds[] = $item->getProduct()->getId();
                    // $defaultItemNames[] = $item->getName();
                    // }
                } else {
                    if ($this->_getConfigHelper()->getCommissionConfig('affiliate_type') == 'profit')
                        $baseProfit = $item->getBasePrice() - $item->getBaseCost();
                    else
                        $baseProfit = $item->getBasePrice();
                    //Zend_Debug::dump($item->getBaseAffiliateplusAmount());die;
                    $baseProfit = $item->getQtyOrdered() * $baseProfit - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                    if ($baseProfit <= 0)
                        continue;

                    // Changed by Adam 15/10/2014: Call to a member function getId() on a non-object in C:\xampp\htdocs\project\magento1.5.0.1\app\code\local\Magestore\Affiliateplus\Model\Observer.php on line 1315
                    // $orderItemIds[] = $item->getProduct()->getId();
                    $orderItemIds[] = $item->getProduct() ? $item->getProduct()->getId() : $item->getProductId();
                    $orderItemNames[] = $item->getName();

                    // Changed by Adam 15/10/2014
                    $defaultItemIds[] = $item->getProduct() ? $item->getProduct()->getId() : $item->getProductId();
                    $defaultItemNames[] = $item->getName();

                    /* Changed BY Adam 22/07/2014 */
                    if ($commissionType == 'cart_fixed') {
                        $commissionValue = min($commissionValue, $baseItemsPrice);
                        $itemPrice = $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
                        $itemCommission = $itemPrice * $commissionValue / $baseItemsPrice;
                        $defaultCommission = min($itemPrice * $commissionValue / $baseItemsPrice, $baseProfit);
                    } elseif ($commissionType == 'fixed')
                        $defaultCommission = min($item->getQtyOrdered() * $commissionValue, $baseProfit);
                    elseif ($commissionType == 'percentage')
                        $defaultCommission = $baseProfit * $commissionValue / 100;
                    $affiliateplusCommissionItem .= $defaultCommission . ",";
                    $commissionObj = new Varien_Object(array(
                        'profit' => $baseProfit,
                        'commission' => $defaultCommission,
                        'tier_commission' => array(),
                        'base_item_price' => $baseItemsPrice, // Added By Adam 22/07/2014
                        'affiliateplus_commission_item' => $affiliateplusCommissionItem,
                    ));
                    Mage::dispatchEvent('affiliateplus_calculate_tier_commission', array(
                        'item' => $item,
                        'account' => $account,
                        'commission_obj' => $commissionObj
                    ));

                    if ($commissionObj->getTierCommission())
                        $tierCommissions[$item->getId()] = $commissionObj->getTierCommission();
                    $commission += $commissionObj->getCommission();
                    $item->setAffiliateplusCommission($commissionObj->getCommission());
                    // Changed By Adam 14/08/2014: Invoice tung phan
                    $item->setAffiliateplusCommissionItem($commissionObj->getAffiliateplusCommissionItem());
                    $defCommission += $commissionObj->getCommission();
                    $defaultAmount += $item->getBasePrice();
                }
            }
        }
        /* if remove coupon, then return Edit By Jack */
        $currentCouponCode = $transactionAffiliate->getCouponCode();
        if (($currentCouponCode && !Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code') && !$baseDiscount) || $account->getStatus() == 2)
            $commission = 0;
        //set Commission Value
        Mage::getSingleton('adminhtml/session_quote')->setCommission($commission);
        /* end if */
        if (!$baseDiscount && !$commission)
            return $this;
        // Create transaction 
        $storeId = Mage::getSingleton('adminhtml/session_quote')->getStore()->getId();
        $transactionData = array(
            'account_id' => $account->getId(),
            'account_name' => $account->getName(),
            'account_email' => $account->getEmail(),
            'customer_id' => $order->getCustomerId(), // $customer->getId(),
            'customer_email' => $order->getCustomerEmail(), // $customer->getEmail(),
            'order_id' => $order->getId(),
            'order_number' => $order->getIncrementId(),
            'order_item_ids' => implode(',', $orderItemIds),
            'order_item_names' => implode(',', $orderItemNames),
            'total_amount' => $order->getBaseSubtotal(),
            'discount' => $baseDiscount,
            'commission' => $commission,
            'created_time' => now(),
            'status' => '2',
            'store_id' => $storeId,
            'extra_content' => $extraContent,
            'tier_commissions' => $tierCommissions,
            'default_item_ids' => $defaultItemIds,
            'default_item_names' => $defaultItemNames,
            'default_commission' => $defCommission,
            'default_amount' => $defaultAmount,
            'type' => 3,
            'program_id' => $programId,
            'program_name' => $programName,
            'coupon_code' => Mage::getSingleton('checkout/session')->getData('affiliate_coupon_code'),
        );
        $transaction = Mage::getModel('affiliateplus/transaction')->setData($transactionData)->setId(null);
        $transactionLatest = Mage::getModel('affiliateplus/transaction')
                        ->getCollection()->getLastItem();

        try {
            if ($transactionLatest->getOrderNumber() != $transactionData['order_number']) {
                $transaction->save();
                if ($transaction->getIsChangedData())
                    $transaction->save();
                if (!$affiliateInfo)
                    $affiliateInfo = '';
                Mage::dispatchEvent('affiliateplus_recalculate_commission', array(
                    'transaction' => $transaction,
                    'order' => $order,
                    'affiliate_info' => $affiliateInfo,
                ));
                Mage::dispatchEvent('affiliateplus_created_transaction', array(
                    'transaction' => $transaction,
                    'order' => $order,
                ));
                $transaction->sendMailNewTransactionToAccount();
                $transaction->sendMailNewTransactionToSales();
            }
        } catch (Exception $e) {
            // Exception
        }
    }

    /* end create new transaction  */

    public function orderSaveAfter($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $order = $observer->getOrder();
        $storeId = $order->getStoreId();

        // Return money on affiliate balance
        if ($order->getData('state') == Mage_Sales_Model_Order::STATE_CANCELED) {

            $paymentMethod = Mage::getModel('affiliateplus/payment_credit')->load($order->getId(), 'order_id');
            if ($paymentMethod->getId() && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0
            ) {
                $payment = Mage::getModel('affiliateplus/payment')->load($paymentMethod->getPaymentId())
                        ->setData('payment', $paymentMethod);
                $account = $payment->getAffiliateplusAccount();
                if ($account && $account->getId() && $payment->getId()) {
                    try {
                        $refundAmount = $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount();
                        $account->setBalance($account->getBalance() + $refundAmount)
                                ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                                ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                                ->save();
                        $paymentMethod->setBaseRefundAmount($paymentMethod->getBasePaidAmount())
                                ->setRefundAmount($paymentMethod->getPaidAmount())
                                ->save();
                        $payment->setStatus(4)->save();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }

        $configOrderStatus = $this->_getConfigHelper()->getCommissionConfig('updatebalance_orderstatus', $storeId);
        $configOrderStatus = $configOrderStatus ? $configOrderStatus : 'processing';

        if ($order->getStatus() == $configOrderStatus) {

            $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');
            // Complete Transaction or hold transaction
            if ($this->_getConfigHelper()->getCommissionConfig('holding_period', $storeId)) {
                return $transaction->hold();
            }
            return $transaction->complete();
        }
        $cancelStatus = explode(',', $this->_getConfigHelper()->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        if (in_array($order->getStatus(), $cancelStatus)) {
            $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');
            // Cancel Transaction
            return $transaction->cancel();
        }

        /* call back function createNewTransaction() - Edit By Jack */
        $actionName = Mage::app()->getRequest()->getActionName();
        $controllerName = Mage::app()->getRequest()->getControllerName();
        if (($actionName == 'cancel' && $controllerName == 'sales_order') || ($actionName == 'save' && $controllerName == 'sales_order_invoice') || ($actionName == 'save' && $controllerName == 'sales_order_shipment'))
            return $this;
        if (Mage::helper('affiliateplus')->isAdmin())
            $this->createNewTransaction($order);
        /* end call back function */
    }

    public function paypalPrepareItems($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            $paypalCart = $observer->getEvent()->getPaypalCart();
            if ($paypalCart) {
                $salesEntity = $paypalCart->getSalesEntity();
                $totalDiscount = 0;

                if ($salesEntity->getBaseAffiliateplusDiscount())
                    $totalDiscount = $salesEntity->getBaseAffiliateplusDiscount();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateplusDiscount())
                            $totalDiscount = $address->getBaseAffiliateplusDiscount();
                if ($totalDiscount)
                    $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, abs((float) $totalDiscount), Mage::helper('affiliateplus')->__('Affiliate Discount'));

                /* Changed By Adam 16/04/2015 */
                $totalCredit = 0;
                if ($salesEntity->getBaseAffiliateCredit())
                    $totalCredit = $salesEntity->getBaseAffiliateCredit();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateCredit())
                            $totalCredit = $address->getBaseAffiliateCredit();
                if ($totalCredit)
                    $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, abs((float) $totalCredit), Mage::helper('affiliateplus')->__('Paid by Affiliate Credit'));
            }
        } else {
            $salesEntity = $observer->getSalesEntity();
            $additional = $observer->getAdditional();
            if ($salesEntity && $additional) {
                $totalDiscount = 0;
                if ($salesEntity->getBaseAffiliateplusDiscount())
                    $totalDiscount = $salesEntity->getBaseAffiliateplusDiscount();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateplusDiscount())
                            $totalDiscount = $address->getBaseAffiliateplusDiscount();
                if ($totalDiscount) {
                    $items = $additional->getItems();
                    $items[] = new Varien_Object(array(
                        'name' => Mage::helper('affiliateplus')->__('Affiliate Discount'),
                        'qty' => 1,
                        'amount' => -(abs((float) $totalDiscount)),
                    ));
                    $additional->setItems($items);
                }

                /* Changed By Adam 16/04/2015 */
                $totalCredit = 0;
                if ($salesEntity->getBaseAffiliateCredit())
                    $totalCredit = $salesEntity->getBaseAffiliateCredit();
                else
                    foreach ($salesEntity->getAddressesCollection() as $address)
                        if ($address->getBaseAffiliateCredit())
                            $totalCredit = $address->getBaseAffiliateCredit();
                if ($totalCredit) {
                    $items = $additional->getItems();
                    $items[] = new Varien_Object(array(
                        'name' => Mage::helper('affiliateplus')->__('Affiliate Discount'),
                        'qty' => 1,
                        'amount' => -(abs((float) $totalCredit)),
                    ));
                    $additional->setItems($items);
                }
            }
        }
    }

    /**
     *
     * @param type $observer
     * @return \Magestore_Affiliateplus_Model_Observer
     */
    public function saveClickAction($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $controller = $observer['controller_action'];
        $request = $controller->getRequest();
        $accountCode = $request->getParam('acc');
        // Added By Adam (31/08/2016): save cookie by account id from sub store url
        $account = $this->_getAccountById($request);
        if($account && $account->getStatus() == 1){
            $accountCode = $account->getIdentifyCode();
        }
        // hainh 29-07-2014
        $param = array();   // Added By Adam to fix the error
        if (!$accountCode || ($accountCode == '')) {
            $paramList = Mage::getStoreConfig('affiliateplus/refer/url_param_array');
            $paramArray = explode(',', $paramList);
            for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
                $accountCode = $request->getParam($paramArray[$i]);
                if ($accountCode && ($accountCode != '')) {
                    $param = $paramArray[$i];
                    break;
                }
            }
        }
        // Changed By Adam 12/06/2015 fix issue can't detect affiliate when customer click on the affiliate link on Facebook
        if(strpos($accountCode, "?")) {
            $code = explode("?", $accountCode);
            $accountCode = $code[0];
        }
        
        // Changed By Adam 08/05/2015 fix loi tu lay identify code cua affiliate khac
        if(Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2) {
            $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('account_id', $accountCode)->getFirstItem();
        } else {
            $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('identify_code', $accountCode)->getFirstItem();
        }
        
        if ($account->getId())
            $accountCode = $account->getIdentifyCode();
        //end editing

        if (!$accountCode && $request->getParam('df08b0441bac900')) {
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            try {
                $select = $read->select()
                        ->from($resource->getTableName('affiliate_referral'), array('customer_id'))
                        ->where("identify_code=?", trim($request->getParam('df08b0441bac900')));
                $result = $read->fetchRow($select);
                $oldCustomerId = $result['customer_id'];
                if ($oldCustomerId)
                    $accountCode = Mage::getModel('affiliateplus/account')
                            ->loadByCustomerId($oldCustomerId)
                            ->getIdentifyCode();
            } catch (Exception $e) {
                
            }
        }
        if (!$accountCode)
            return $this;
        if ($account = Mage::getSingleton('affiliateplus/session')->getAccount())
            if ($account->getIdentifyCode() == $accountCode)
                return $this;
        $storeId = Mage::app()->getStore()->getId();
        if (!$storeId)
            return $this;
        
        $account = Mage::getModel('affiliateplus/account')->setStoreId($storeId)->loadByIdentifyCode($accountCode);
        if (!$account->getId() || ($account->getStatus() != 1))
            return $this;

        $ipAddress = $request->getClientIp();
        $banner_id = $request->getParam('bannerid');
        if ($banner_id) {
            $banner = Mage::getModel('affiliateplus/banner')->load($banner_id);
            $banner->setStoreId($storeId);
            if ($banner->getStatus() != 1)
                $banner_id = 0;
        }
        /*
         * check
         */
        $check = FALSE;
        if (Mage::helper('affiliateplus')->exitedCookie($param))
            return $this;
        if (!$check) {
            if (Mage::helper('affiliateplus')->isProxys())
                return $this;
        }
        if (!$check) {
            if (Mage::helper('affiliateplus')->isRobots())
                return $this;
        }
        /*
         * end check
         */
        $domain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (!$domain && $request->getParam('src')) {
            $domain = $request->getParam('src');
        }
        $landing_page = $request->getOriginalPathInfo();
        $actionModel = Mage::getModel('affiliateplus/action');
        if ($check) {
            $isUnique = 0;
        } else {
            $isUnique = $actionModel->checkIpClick($ipAddress, $account->getId(), $domain, $banner_id, 2);
        }

        $action = $actionModel->saveAction($account->getId(), $banner_id, 2, $storeId, 0, $ipAddress, $domain, $landing_page);
        if ($isUnique) {
            if (Mage::helper('affiliateplus/config')->getActionConfig('detect_iframe')) {
                $hashCode = md5($action->getCreatedDate() . $action->getId());
                $session = Mage::getSingleton('core/session');
                $session->setData('transaction_checkiframe__action_id', $action->getId());
                $session->setData('transaction_checkiframe_hash_code', $hashCode);
            } else {
                $action->setIsUnique(1)->save();
                Mage::dispatchEvent('affiliateplus_save_action_before', array(
                    'action' => $action,
                    'is_unique' => $isUnique,
                ));
            }
        }
    }

    /* magic update affiliate account when account customer change 13/11/2012 */

    //hainh 25-07-2014
    public function customerSaveAfter($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $request = Mage::app()->getRequest();
        $customer = $observer->getEvent()->getCustomer();
        $account = Mage::getModel('affiliateplus/account')->loadByCustomer($customer);
        if ($account->getId() > 0) {
            $account->setName($customer->getName());
            $account->setEmail($customer->getEmail());
            $account->save();
        } 
        // Changed By Adam 11/05/2015: Fix issue when admin create affiliate account in back-end, using the email of existed customer (enable function Auto create Affiliate account when Customer registers)
        elseif (Mage::getStoreConfig('affiliateplus/account/auto_create_affiliate') && ($request->getActionName() != 'createPost') && ($request->getModuleName() != 'affiliates') && !Mage::app()->getStore()->isAdmin()) { //check if this is affiliate create form or not
            try {
                Mage::helper('affiliateplus/account')->createAffiliateAccount('', '', $customer, $request->getPost('notification'), '', '');
            } catch (Exception $e) {
                return $this;
            }
        } elseif (Mage::getStoreConfig('affiliateplus/account/auto_create_affiliate') && ($request->getActionName() != 'createPost') && ($request->getModuleName() != 'affiliateplusadmin') && Mage::app()->getStore()->isAdmin()) { //check if this is affiliate create form or not
            try {
                Mage::helper('affiliateplus/account')->createAffiliateAccount('', '', $customer, $request->getPost('notification'), '', '');
            } catch (Exception $e) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * partial refund: reduce commission from affiliate's balance
     * @param type $observer
     * @return Magestore_Affiliateplus_Model_Observer
     */
    public function creditmemoSaveAfter($observer) {

        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $creditmemo = $observer->getCreditmemo();

        if ($creditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED) {
            return $this;
        }
        // Refund for Affiliate Credit
        $this->creditmemoRefund($creditmemo);

        $storeId = $creditmemo->getStoreId();
        if (!$this->_getConfigHelper()->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
            return $this;
        }

        $order = $creditmemo->getOrder();
        $cancelStatus = explode(',', $this->_getConfigHelper()->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        $transaction = Mage::getModel('affiliateplus/transaction')->load($order->getIncrementId(), 'order_number');

        if (in_array('closed', $cancelStatus) && !$order->canCreditmemo()) {

            //            edit by viet
            $transaction->reduce($creditmemo);
            //            end by viet
            return $this;
        }

        if ($transaction->getId()) {
            $transaction->reduce($creditmemo);
        }
    }

    /**
     * Refund order when using balance as store credit
     * @param type $creditmemo
     * @return type
     */
    public function creditmemoRefund($creditmemo) {

        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        // $creditmemo = $observer->getCreditmemo();
        $order = $creditmemo->getOrder();

        $paymentMethod = Mage::getModel('affiliateplus/payment_credit')->load($order->getId(), 'order_id');
        if ($paymentMethod->getId() && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0
        ) {
            $payment = Mage::getModel('affiliateplus/payment')->load($paymentMethod->getPaymentId())
                    ->setData('payment', $paymentMethod);
            $account = $payment->getAffiliateplusAccount();
            if ($account && $account->getId() && $payment->getId()) {
                try {
                    $refundAmount = -$creditmemo->getBaseAffiliateCredit();
                    $account->setBalance($account->getBalance() + $refundAmount)
                            ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                            ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                            ->save();
                    $paymentMethod->setBaseRefundAmount($paymentMethod->getBaseRefundAmount() + $refundAmount)
                            ->setRefundAmount($paymentMethod->getRefundAmount() - $creditmemo->getAffiliateCredit())
                            ->save();
                    if (abs($paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount()) < 0.0001) {
                        $payment->setStatus(4)->save();
                    }
                } catch (Exception $e) {
                    
                }
            }
        }
    }

    public function blockToHtmlAfter($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;

        $helper = Mage::helper('affiliateplus/account');
        $block = $observer['block'];
        if($block instanceof Magestore_AffiliateplusReferFriend_Block_Product){
            $transport = $observer['transport'];
            $html = $transport->getHtml();


            $html .= $block->getLayout()->createBlock('affiliateplus/affiliateplus')->setTemplate('affiliateplus/account/product.phtml')->renderView();

            $transport->setHtml($html);
        }



        if ($helper->accountNotLogin() || $helper->disableStoreCredit() || !$helper->isEnoughBalance()
        ) {
            return;
        }


        if ($block instanceof Mage_Checkout_Block_Cart_Coupon) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                    . '_' . $block->getRequest()->getRequestedControllerName()
                    . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'checkout_cart_index') {
                $transport = $observer['transport'];
                $html = $transport->getHtml();
                $html .= $block->getLayout()->createBlock('affiliateplus/credit_cart')->renderView();
                $transport->setHtml($html);
            }
        }
        if ($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                    . '_' . $block->getRequest()->getRequestedControllerName()
                    . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'onestepcheckout_index_index' || $requestPath == 'checkout_onepage_index'
            ) {
                return;
            }
            $transport = $observer['transport'];
            $html = $transport->getHtml();

            $creditHtml = $block->getLayout()->createBlock('affiliateplus/credit_form')->renderView();
            $html .= '<script type="text/javascript">checkOutLoadAffiliateCredit(' . Mage::helper('core')->jsonEncode(array('html' => $creditHtml)) . ');onLoadAffiliateCreditForm();</script>';

            $transport->setHtml($html);
        }
    }

    public function salesruleValidatorProcess($observer) {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        if ($this->_getConfigHelper()->getDiscountConfig('allow_discount') != 'affiliate') {
            return $this;
        }
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info)
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        if (!$account) {
            return $this;
        }
        $result = $observer['result'];
        $result->setDiscountAmount(0)
                ->setBaseDiscountAmount(0);
        $rule = $observer['rule'];
        $rule->setRuleId('')->setStopRulesProcessing(true);
    }

    public function unHoldTransaction() {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        $days = (int) $this->_getConfigHelper()->getCommissionConfig('holding_period');
        $activeTime = time() - $days * 86400;
        $collection = Mage::getResourceModel('affiliateplus/transaction_collection')
                ->addFieldToFilter('status', 4)
                ->addFieldToFilter('holding_from', array('to' => date('Y-m-d H:i:s', $activeTime)));
        foreach ($collection as $transaction) {
            try {
                $transaction->unHold();
            } catch (Exception $e) {
                
            }
        }
    }
    
    //Changed By Adam 01/06/2015: Add jquey on top to avoid conflict
    public function prepareLayoutBefore($observer){
        $block = $observer->getEvent()->getBlock();
        if ("head" == $block->getNameInLayout()) {
            $file  = '/magestore/jquery-1.11.1.min.js';
            $block->addJs($file);
        }
        return $this;
    }

    //hainh 13-05-2014
    /*
      public function disableCache($observer) {
      $request = Mage::app()->getRequest();
      $accountCode = $request->getParam('acc');
      // hainh 29-07-2014
      if (!$accountCode || ($accountCode == '')) {
      $paramList = Mage::getStoreConfig('affiliateplus/refer/url_param_array');
      $paramArray = explode(',', $paramList);
      for ($i = (count($paramArray) - 1); $i >= 0; $i--) {
      $accountCode = $request->getParam($paramArray[$i]);
      if ($accountCode && ($accountCode != ''))
      break;
      }
      }
      //end editing
      if ($accountCode && ($accountCode != '')) {
      $request->setParam('no_cache', true);
      }
      }
     * 
     */

    /**
     * Added By Adam (31/08/2016): save cookie by account id from sub store url
     * @param $request
     * @return bool
     */
    protected function _getAccountById($request) {
        if($request->getRouteName() == 'affiliateplus' && $request->getControllerName() == 'index' && $request->getActionName() == 'view'){
            $id = $request->getParam('id');
            if($id){
                $account = Mage::getModel('affiliateplus/account')->load($id);
                if($account && $account->getId()){
                    return $account;
                }
                return false;
            }
            return false;
        }
        return false;
    }
}
