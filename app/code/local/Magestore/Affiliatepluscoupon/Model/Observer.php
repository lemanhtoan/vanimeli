<?php

class Magestore_Affiliatepluscoupon_Model_Observer {

    //hainh 23-07
    public function couponPostAction($observer) {
        // Changed By Adam 31/07/2014
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig('affiliateplus/coupon/enable') || !Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;


        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if (!$code)
            return $this;

        $session = Mage::getSingleton('checkout/session');

        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$account->getId()) {
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel') && $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
            }
            return $this;
            //      Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
        } elseif (!Mage::helper('affiliateplus/config')->allowAffiliateToGetCommissionFromHisPurchase($storeId) && $account->getCustomerId() == $customerId) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $this->clearAffiliateCookie();
            return $this;
        }

        if ($action->getRequest()->getParam('remove') == 1) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode()));
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return $this;
            }
        } else {
            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid() && Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram')) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                            ->addFieldToFilter('program_id', $account->getCouponPid())
                            ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        $quote->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                $quote->collectTotals()->save();
                // }
            }
            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available && !Mage::helper('affiliateplus')->checkLifeTimeForOrderBackend($customerId)) {
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code));
            }
            // Changed By Adam: don't allow customer to use coupon code if he has been assigned lifetime to an affiliate.
            else if ($available && Mage::helper('affiliateplus')->checkLifeTimeForOrderBackend($customerId)) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $session->addError(Mage::helper('affiliatepluscoupon')->__('Can not apply this coupon because another affiliate was gotten lifetime commission!'));
            } else {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $session->addError(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code));
            }
        }
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }

    //hainh end editing

    /* Magic Add event controller_action_predispatch_onestepcheckout_index_add_coupon 15/11/2012 */

    public function quoteGetCouponCode($observer) {
        $url_current = Mage::helper('core/url')->getCurrentUrl();
        $url_save_address = Mage::getUrl('onestepcheckout/index/save_address/');
        if ($url_current == $url_save_address) {
            $quote = $observer->getEvent()->getQuote();
            $quote->collectTotals()->save();
        }
        return $this;
    }

    public function couponPostActionOneStep($observer) {
        // Changed By Adam 31/07/2014
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;

        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if (!$code)
            return $this;

        $session = Mage::getSingleton('checkout/session');

        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$account->getId()) {
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel') && $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
            }
            if ($action->getRequest()->getParam('remove') != 1) {
                return $this;
            } elseif (!Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return $this;
            }
            //      Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
        } elseif (!Mage::helper('affiliateplus/config')->allowAffiliateToGetCommissionFromHisPurchase($storeId) && $account->getCustomerId() == $customerId) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $this->clearAffiliateCookie();
            return $this;
        }

        if ($action->getRequest()->getParam('remove') == 1) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $quote->collectTotals()
                        ->save();
                if ($quote->getCouponCode())
                    $error = TRUE;
                $this->clearAffiliateCookie();
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode());
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                //return $this;
                $quote = $session->getQuote();
                if ($quote->getCouponCode()) {
                    return $this;
                } else {
                    $quote->collectTotals()->save();
                    $error = true;
                }
            }
        } else {
            $error = false;
            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid() && Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram')) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                            ->addFieldToFilter('program_id', $account->getCouponPid())
                            ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        $quote->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                $quote->collectTotals()->save();
                // }
            }

            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available) {
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code);
            } else {
                $error = true;
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code);
            }
        }

        $layout = $observer->getEvent()->getControllerAction()->getLayout();
        $update = $layout->getUpdate();
        $update->load('onestepcheckout_onestepcheckout_review');
        $layout->unsetBlock('shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        $result = array(
            'error' => $error,
            'message' => $message,
            'review_html' => $output
        );

        $action->getResponse()->setBody(Zend_Json::encode($result));
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        return $result;
    }

    /* end onestepcheckout_index_add_coupon */

    public function onestepcheckoutIndexLoadTotals($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $action = $observer->getEvent()->getControllerAction();
        $shippingMethod = $action->getRequest()->getPost('shipping_method', '');
        if (empty($shippingMethod)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $rate = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
        if (!$rate) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid shipping method.'));
        }
        $this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        //$this->getOnepage()->getQuote()->collectTotals()->save();
        return array();
    }

    public function getAffiliateInfo($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $affilateData = $session->getData('affiliate_coupon_data');
        if (!$affilateData || !is_array($affilateData) || !isset($affilateData['program_id']))
            return $this;

        $account = Mage::getModel('affiliateplus/account')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($affilateData['account_id']);
        if (!$account->getId() || $account->getStatus() != 1 || $account->getId() == Mage::helper('affiliateplus/account')->getAccount()->getId()) {
            return $this;
        }

        if (Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram') && $affilateData['program_id']) {
            $program = Mage::getModel('affiliateplusprogram/program')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($affilateData['program_id']);
            if (!$program->isAvailable() || !$program->getUseCoupon())
                return $this;
            $account->setUsingProgram($program);
        }
        $info = array();
        $account->setUsingCoupon(true);
        $info[$account->getIdentifyCode()] = array(
            'index' => 1,
            'code' => $account->getIdentifyCode(),
            'account' => $account,
        );
        $infoObj = $observer->getEvent()->getInfoObj();
        $infoObj->setInfo($info);
    }

    public function clearAffiliateCookie() {
        $cookie = Mage::getSingleton('core/cookie');
        for ($index = intval($cookie->get('affiliateplus_map_index')); $index > 0; $index--)
            $cookie->delete("affiliateplus_account_code_$index");
        $cookie->delete('affiliateplus_map_index');
        return $this;
    }

    public function addAccountTab($observer) {
        if (!$observer->getEvent()->getId())
            return $this;
        $form = $observer->getEvent()->getForm();
        $form->addTabAfter('affiliateplus_coupon_codes', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'title' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'url' => $form->getUrl('adminhtml/affiliatepluscoupon_account/coupons', array('_current' => true)),
            'class' => 'ajax',
                ), 'form_section');
    }

    public function afterSaveAccount($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $data = $observer->getEvent()->getPostData();
        if (!isset($data['account_coupon']))
            return $this;
        if (!$data['account_coupon'])
            return $this;

        $programCoupons = array();
        parse_str(urldecode($data['account_coupon']), $programCoupons);
        if (!count($programCoupons))
            return $this;

        $account = $observer->getEvent()->getAccount();
        $accountId = $account->getId();
        $coupon = Mage::getModel('affiliatepluscoupon/coupon')->setCurrentAccountId($accountId);

        foreach ($programCoupons as $pId => $enCoded) {
            $coupon->setId(null)->loadByProgram($pId);
            if (!$coupon->getId())
                continue;
            $codeArr = array();
            $code = '';
            parse_str(base64_decode($enCoded), $codeArr);
            if (isset($codeArr['coupon_code']))
                $code = $codeArr['coupon_code'];
            if ($coupon->getCouponCode() == $code || !$code)
                continue;
            try {
                $coupon->setCouponCode($code)->save();
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addWarning($e->getMessage());
            }
        }
    }

    public function editProgramForm($observer) {
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('coupon_separator', 'text', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Affiliate Coupon'),
        ))->setRenderer(Mage::app()->getLayout()->createBlock('affiliateplus/adminhtml_field_separator'));
        $fieldset->addField('use_coupon', 'select', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Use coupon'),
            'name' => 'use_coupon',
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'onchange' => 'changeCouponOption(this)',
            'after_element_html' => '<script type="text/javascript">
				function changeCouponOption(el){
					if (el.value == 1)
						$(\'affiliateplusprogram_coupon_pattern\').parentNode.parentNode.show();
					else
						$(\'affiliateplusprogram_coupon_pattern\').parentNode.parentNode.hide();
				}
				Event.observe(window,\'load\',function(){
					changeCouponOption($(\'affiliateplusprogram_use_coupon\'));
				});
			</script>',
        ));
        $fieldset->addField('coupon_pattern', 'text', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon code pattern'),
            'name' => 'coupon_pattern',
            'note' => Mage::helper('affiliatepluscoupon')->__('Used to generate coupon codes for Affiliates. Eg:<br/><strong>[A.8] : 8 alpha characters<br/>[N.4] : 4 numeric characters<br/>[AN.6] : 6 alphanumeric characters<br/>AFFILIATE-[A.4]-[AN.6] : AFFILIATE-ADFA-12NF0O</strong>'),
        ));
    }

    public function addFieldTransactionForm($observer) {
        $form = $observer->getEvent()->getForm();
        $transactionData = $form->getTransationData();
        if (!isset($transactionData['coupon_code']) || !$transactionData['coupon_code'])
            return $this;
        $fieldset = $observer->getEvent()->getFieldset();
        $fieldset->addField('coupon_code', 'note', array(
            'label' => Mage::helper('affiliatepluscoupon')->__('Coupon Code'),
            'text' => $transactionData['coupon_code'],
        ));
    }

    /* David */

    public function beforeToHtml($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Onepage_Review || $block instanceof Mage_Checkout_Block_Cart_Coupon
        ) {
            $session = Mage::getSingleton('checkout/session');
            $quote = $session->getQuote();
            $affCode = $session->getData('affiliate_coupon_code');
            if (!$quote->getCouponCode() && $affCode) {
                $quote->setCouponCode($affCode);
                $session->setData('affiliate_coupon_code_flag', true);
            }
        }
    }

    public function afterToHtml($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $block = $observer['block'];
        if ($block instanceof Mage_Checkout_Block_Onepage_Review || $block instanceof Mage_Checkout_Block_Cart_Coupon
        ) {
            $session = Mage::getSingleton('checkout/session');
            if ($session->getData('affiliate_coupon_code_flag')) {
                $session->unsetData('affiliate_coupon_code_flag');
                $session->getQuote()->setCouponCode('');
            }
        }
    }

    public function couponPostDistpatchActionOneStep($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $action = $observer->getEvent()->getControllerAction();
        if ($session->getData('affiliate_coupon_code')) {
            $result = Zend_Json::decode($action->getResponse()->getBody());
            if ($action->getRequest()->getParam('remove')) {
                $result['error'] = true;
            } else {
                $result['error'] = false;
            }
            $action->getResponse()->setBody(Zend_Json::encode($result));
        }
    }

    //hainh 20-07-2014
    public function orderCreateProcessDataBefore($observer) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;

//                $eventData = array(
//            'order_create_model' => $this->_getOrderCreateModel(),
//            'request_model' => $this->getRequest(),
//            'session' => $this->_getSession(),
//        );
        if (!$observer['order_create_model']->getQuote()->isVirtual())
            $quote = $observer['order_create_model']->getQuote();
        else
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $request = $observer['request_model'];
        $order = $observer['request_model']->getPost('order');
        if (!isset($order['coupon']))
            return $this;
        if ((!$order['coupon']['code']) || ($order['coupon']['code'] == ''))
            return $this;
        if ($order['coupon']['code'] == 'removeAffiliateCodeBackend') {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $remove = 1;
        } else {
            $remove = 0;
        }
        $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        if ($this->_checkAffiliateCouponCode($order['coupon']['code'], $session, $remove, $customerId, $quote, TRUE)) {
            $isLifeTimeAdmin = Mage::helper('affiliateplus')->checkLifeTime($customerId);
            $isLifeTime = Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales');
            if (!$isLifeTimeAdmin || $isLifeTime != 1) {
                Mage::getSingleton('adminhtml/session_quote')->addSuccess('Affiliate Coupon code was applied.');
                Mage::getSingleton('adminhtml/session_quote')->setOldQuoteId($quote->getId());
            }
            unset($order['coupon']['code']);
            $request->setPost('order', $order);
        } elseif ($remove) {
            Mage::getSingleton('adminhtml/session_quote')->addSuccess('Affiliate Coupon code removed successfully.');
            unset($order['coupon']['code']);
            $request->setPost('order', $order);
        }
    }

    public function orderCreateProcessData($observer) {
        // $action = Mage::app()->getFrontController()->getAction();
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        if (!$observer['order_create_model']->getQuote()->isVirtual())
            $quote = $observer['order_create_model']->getQuote();
        else
            return $this;
        $session = Mage::getSingleton('checkout/session');
        $request = $observer['request'];
//        Changed By Adam 04/05/2015: Khi tao order trong back-end bi loi: Notice: Undefined index: order in app/code/local/Magestore/Affiliatepluscoupon/Model/Observer.php on line 486
//        $order = $observer['request']['order'];
        $order = isset($request['order']) && $request['order'] ? $request['order'] : null;
        if(!$order) return;
        if (!isset($order['coupon']))
            return $this;
        if ((!$order['coupon']['code']) || ($order['coupon']['code'] == ''))
            return $this;
        if ($order['coupon']['code'] == 'removeAffiliateCodeBackend') {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $remove = 1;
        } else {
            $remove = 0;
        }
        $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomerId();
        if ($this->_checkAffiliateCouponCode($order['coupon']['code'], $session, $remove, $customerId, $quote, TRUE)) {
            $isLifeTimeAdmin = Mage::helper('affiliateplus')->checkLifeTime($customerId);
            $isLifeTime = Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales');
            if (!$isLifeTimeAdmin || $isLifeTime != 1) {
                Mage::getSingleton('adminhtml/session_quote')->addSuccess('Affiliate Coupon code was applied.');
                Mage::getSingleton('adminhtml/session_quote')->setOldQuoteId($quote->getId());
            }
            unset($order['coupon']['code']);
            $request['order'] = $order;
            Mage::app()->getRequest()->setPost('order', $order);
        } elseif ($remove) {
            Mage::getSingleton('adminhtml/session_quote')->addSuccess('Affiliate Coupon code removed successfully.');
            unset($order['coupon']['code']);
            $request['order'] = $order;
            Mage::app()->getRequest()->setPost('order', $order);
        }
    }

    /* don't allow apply coupon code when program was disabled or when multiprogram plugin was disabled - Edit By Jack */

    public function isAllowApplyCoupon($couponCode, $isBackend,$isEdit = null) {
        $couponData = Mage::getModel('affiliatepluscoupon/coupon')->getCollection()
                ->addFieldToFilter('coupon_code', $couponCode)
                ->getFirstItem();
        $programName = $couponData->getProgramName();
        $programId = $couponData->getProgramId();
        $checkProgramByConfig = Mage::getStoreConfig('affiliateplus/program/enable');
        if ($checkProgramByConfig && $programId && Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram')) {
            $programData = Mage::getModel('affiliateplusprogram/program')->load($programId);
            if ($programData->getStatus() != 1 || $programData->getUseCoupon() == 0)
                $programStatus = false;
        }
        /* check apply coupon by Store View - Edit By Jack */
        $accountId = Mage::getModel('affiliatepluscoupon/coupon')->getCollection()
                ->addFieldToFilter('coupon_code', $couponCode)
                ->getFirstItem()
                ->getAccountId();
        $customerId = Mage::getModel('affiliateplus/account')->load($accountId)->getCustomerId();
        $storeViewId = Mage::getModel('customer/customer')->load($customerId)->getStoreId();
        $isApply = false;
        if ($isBackend)
            $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        else
            $storeId = Mage::app()->getStore()->getId();
        if(!$isEdit){
            if ($storeViewId == 0)
                $isApply = TRUE;
            else {
                if ($storeViewId == $storeId)
                    $isApply = TRUE;
                else {
                    $customerScop = Mage::getStoreConfig('customer/account_share/scope');
                    if ($customerScop == 0)
                        $isApply = TRUE;
                    else
                        return FALSE;
                }
            }
        }
        /* */
        if ($isApply) {
            if (($programName == 'Affiliate Program' && $programStatus == '') || $programName == '')
                return true;
            if (!Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram') || !Mage::helper('affiliateplus')->isAllowUseCoupon($couponCode))
                return false;
            if (isset($programStatus) && $programStatus != 1)
                return false;
        }
        return true;
    }

    /* end don't allow apply coupon code  */

    public function _checkAffiliateCouponCode($code, $session, $remove, $customerId, $quote, $isBackend) {
        // Changed By Adam 31/07/2014
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled())
            return $this;
        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        /* allow apply coupon code OR NOT - Edit By Jack */
        if (!$this->isAllowApplyCoupon($code, $isBackend) && $remove != 1) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            return FALSE;
        }
        /* end check */
        /* check if lifetime commission, coupon will be not applied - Edit by Jack */
        $isLifeTime = Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales');
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $isLifeTimeAdmin = Mage::helper('affiliateplus')->checkLifeTime($customerId);
//        $couponCodeCollections = Mage::getModel('affiliatepluscoupon/coupon')->getCollection();
//        $couponArrays = array();
//        foreach ($couponCodeCollections as $couponCodeCollection) {
//            $couponArrays[] = $couponCodeCollection->getCouponCode();
//        }
        if (count($affiliateInfo) > 0 && $remove != 1 && !$isBackend) {
            foreach ($affiliateInfo as $aff)
                // Toi uu code cua Jack
                if (isset($aff['code']) && $aff['code'] && $isLifeTimeAdmin && $isLifeTime == 1 && ($account && $account->getId())) {
                    $session->addError(Mage::helper('affiliatepluscoupon')->__('Can not apply this coupon because another affiliate was gotten lifetime commission !'));
                    return $this;
                }
        } else if ($isBackend && $isLifeTime == 1 && $remove != 1 && $isLifeTimeAdmin && ($account && $account->getId())) {
            Mage::getSingleton('adminhtml/session_quote')->addError(Mage::helper('affiliatepluscoupon')->__('Can not apply this coupon because another affiliate was gotten lifetime commission !'));
            return $this;
        }
        /* end check  */
        if (($remove == 1)) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
                $quote->collectTotals()->save();
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode()));
                return TRUE;
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return FALSE;
            }
        } else {
            if ((!$account->getId()) || ($account->getStatus() == 2)) {
                if (!Mage::getStoreConfig('affiliateplus/coupon/parallel') && $session->getData('affiliate_coupon_code')) {
                    $session->unsetData('affiliate_coupon_code');
                    $session->unsetData('affiliate_coupon_data');
                    $this->clearAffiliateCookie();
                }
                return FALSE;
            } elseif ($account->getCustomerId() == $customerId) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
                return FALSE;
            }

            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();


            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid() && Mage::helper('core')->isModuleEnabled('Magestore_Affiliateplusprogram')) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                            ->addFieldToFilter('program_id', $account->getCouponPid())
                            ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        if (!$isBackend)
                            $quote->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                if (!$isBackend)
                    $quote->collectTotals()->save();
                // }
            }
            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available || $isBackend) {
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code));
                return TRUE;
            } else {
                // $session->unsetData('affiliate_coupon_code');
                //$session->unsetData('affiliate_coupon_data');
                // $session->addError(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code));
                $session->addSuccess(Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code));
                return TRUE;
            }
        }
        return FALSE;
    }

    /* get program and account data Edit By Jack */

    public function getProgramAndAccountData($observer) {
        $couponCodeBySession = $observer->getEvent()->getCouponCodeBySession();
        $programAndAccountData = $observer->getEvent()->getProgramAndAccountData();
        $couponData = Mage::getModel('affiliatepluscoupon/coupon')->getCollection()
                ->addFieldToFilter('coupon_code', $couponCodeBySession)
                ->getFirstItem();
        $programId = $couponData->getProgramId();
        $programName = $couponData->getProgramName();
        $accountId = $couponData->getAccountId();
        $accountInfo = Mage::getModel('affiliateplus/account')->load($accountId);
        $programAndAccountData->setAccountInfo($accountInfo);
        $programAndAccountData->setProgramId($programId);
        $programAndAccountData->setProgramName($programName);
        return $this;
    }

    /* edit by Jack 30/09 */

    public function controllerActionPredispatchSalesOrderCreateIndex($observer) {
        $session = Mage::getSingleton('checkout/session');
        $oldQuoteId = Mage::getSingleton('adminhtml/session_quote')->getOldQuoteId();
        $currentQuoteId = Mage::getSingleton('adminhtml/session_quote')->getQuoteId();
        if (($oldQuoteId != $currentQuoteId) || (!$oldQuoteId && !$currentQuoteId)) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
        }
    }

    /* end Edit */

    public function controllerActionPredispatchAdminhtmlSalesOrderEditStart($observer) {
        // Changed By Adam 31/07/2014
        $session = Mage::getSingleton('checkout/session');
        if (!Mage::helper('affiliatepluscoupon')->isPluginEnabled() || !Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            return $this;
        }
        $action = $observer->getEvent()->getControllerAction();
        $orderId = $action->getRequest()->getParam('order_id');
        if ($orderId && ($orderId != '')) {
            $transaction = Mage::getModel('affiliateplus/transaction')->getCollection()->addFieldToFilter('order_id', $orderId)->getFirstItem();
            $couponCode = $transaction->getCouponCode();
            /* edit By Jack 02/10 */
            if (!$this->isAllowApplyCoupon($couponCode, true,true)) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                return $this;
            }
            /* */
            if ($couponCode && ($couponCode != '')) {
                $coupon = Mage::getModel('affiliatepluscoupon/coupon')->getCollection()->addFieldToFilter('coupon_code', $couponCode)->getFirstItem();
                $session->setData('affiliate_coupon_code', $couponCode);
                $session->setData('affiliate_coupon_data', array(
                    'account_id' => $coupon->getAccountId(),
                    'program_id' => $coupon->getProgramId(),
                ));
                return;
            }
        }
        $session->unsetData('affiliate_coupon_code');
        $session->unsetData('affiliate_coupon_data');
    }

    //hainh end editing
    /**fix bug the conflict with Idev_Onestepcheckout */
    public function couponPostActionIdevOneStep($observer) {

        if (!Mage::getStoreConfig('affiliateplus/coupon/enable'))
            return $this;

        $action = $observer->getEvent()->getControllerAction();

        $code = trim($action->getRequest()->getParam('code'));

        if (!$code)
            return $this;

        $session = Mage::getSingleton('checkout/session');


        $account = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($code);
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$account->getId()) {
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel')
                && $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $this->clearAffiliateCookie();
            }
            if ($action->getRequest()->getParam('remove') != 1) {
                return $this;
            }  elseif(!Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                return $this;
            }
        } elseif ($account->getCustomerId() == $customerId) {
            $session->unsetData('affiliate_coupon_code');
            $session->unsetData('affiliate_coupon_data');
            $this->clearAffiliateCookie();
            return $this;
        }

        if ($action->getRequest()->getParam('remove') == 1) {
            if ($account->getCouponCode() == $session->getData('affiliate_coupon_code')) {
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $quote->setCouponCode('')->collectTotals()
                    ->save();
                // if($quote->getCouponCode())
                $error = false;
                $success = true;
                $this->clearAffiliateCookie();
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was canceled.', $account->getCouponCode());
            } elseif (Mage::getStoreConfig('affiliateplus/coupon/parallel')) {
                //return $this;
                $quote = $session->getQuote();
                if ($quote->getCouponCode()) {
                    return $this;
                } else {
                    $quote->setCouponCode('')->collectTotals()->save();
                    $error = true;
                    $success = false;
                }
            }
        } else {
            $error = false;
            $success = true;
            $session->setData('affiliate_coupon_code', $account->getCouponCode());
            $session->setData('affiliate_coupon_data', array(
                'account_id' => $account->getId(),
                'program_id' => $account->getCouponPid(),
            ));
            $this->clearAffiliateCookie();

            $quote = Mage::getSingleton('checkout/cart')->getQuote();
            if (!Mage::getStoreConfig('affiliateplus/coupon/parallel'))
                $quote->setCouponCode('');
            if ($account->getCouponPid()) {
                $program = Mage::getModel('affiliateplusprogram/program')->setStoreId(Mage::app()->getStore()->getId())->load($account->getCouponPid());
                if ($program->isAvailable()) {
                    $accountProgramCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                        ->addFieldToFilter('program_id', $account->getCouponPid())
                        ->addFieldToFilter('account_id', $account->getId())
                    ;
                    if ($accountProgramCollection->getSize())
                        $quote->setCouponCode($code)->collectTotals()->save();
                }
            }
            if ($account->getCouponPid() == 0) {
                // if (Mage::helper('affiliateplus/config')->getGeneralConfig('show_default')) {
                $quote->setCouponCode($code)->collectTotals()->save();
                // }
            }

            $available = false;
            foreach ($quote->getAddressesCollection() as $address)
                if (!$address->isDeleted() && $address->getAffiliateplusDiscount()) {
                    $available = true;
                    break;
                }
            if ($available) {
                $error = false;
                $success = true;
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" was applied.', $code);
            } else {
                $error = true;
                $success = false;
                $session->unsetData('affiliate_coupon_code');
                $session->unsetData('affiliate_coupon_data');
                $message = Mage::helper('affiliatepluscoupon')->__('Coupon code "%s" is not valid.', $code);
            }
        }

        // Add updated totals HTML to the output
        $shipping_method_html = $observer->getEvent()->getControllerAction()->getLayout()
            ->createBlock('checkout/onepage_shipping_method_available')
            ->setTemplate('onestepcheckout/shipping_method.phtml')
            ->toHtml()
        ;

        $payment_method_html = $observer->getEvent()->getControllerAction()->getLayout()
            ->createBlock('checkout/onepage_payment_methods','choose-payment-method')
            ->setTemplate('onestepcheckout/payment_method.phtml')
            ->toHtml()
        ;
        $html = $observer->getEvent()->getControllerAction()->getLayout()
            ->createBlock('onestepcheckout/summary')
            ->setTemplate('onestepcheckout/summary.phtml')
            ->toHtml();

        $result = array(
            'error' => $error,
            'success' => $success,
            'message' => $message,
            'shipping_method' => $shipping_method_html,
            'payment_method'  => $payment_method_html,
            'review_html' => $html
        );
        $result['summary'] = $html;
        $action->getResponse()->setBody(Zend_Json::encode($result));
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        return $result;
    }
}
