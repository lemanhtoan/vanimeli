<?php

class Magestore_Affiliateplus_Helper_Cookie extends Mage_Core_Helper_Abstract {

    protected $_affiliateInfo = null;
    protected $_numberOrdered = null;

    public function getAffiliateInfo() {
        if (!is_null($this->_affiliateInfo))
            return $this->_affiliateInfo;
        $info = array();
        $storeId = Mage::app()->getStore()->getId();

        //hainh 22-07-2014
        if (Mage::getSingleton('affiliateplus/session')->getTopAffiliateIndentifyCode()) {
            $accountCode=Mage::getSingleton('affiliateplus/session')->getTopAffiliateIndentifyCode();
               $account = Mage::getModel('affiliateplus/account')->setStoreId(Mage::app()->getStore()->getId())->loadByIdentifyCode($accountCode);
//                Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
                if ($account && $account->getId() && $account->getStatus() == 1
                    && (Mage::helper('affiliateplus/config')->allowAffiliateToGetCommissionFromHisPurchase($storeId) || Mage::helper('affiliateplus/account')->getAccount() && $account->getId() != Mage::helper('affiliateplus/account')->getAccount()->getId())) {
                    $info[$accountCode] = array(
                        'index' => 1,
                        'code' => $accountCode,
                        'account' => $account,
                    );
                }
                $infoObj = new Varien_Object(array(
                    'info' => $info,
                ));
                $this->_affiliateInfo = $infoObj->getInfo();
                return $this->_affiliateInfo;
        }
//end edit
         // Check Life-Time sales commission
        if (Mage::helper('affiliateplus/config')->getCommissionConfig('life_time_sales')) {
            $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer && $customer->getId()) {
                $tracksCollection->getSelect()
                        ->where("customer_id = {$customer->getId()} OR customer_email = ?", $customer->getEmail());
            } else {
                /* hainh update 25-04-2014 */
                if (Mage::getSingleton('checkout/session')->hasQuote()) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $customerEmail = $quote->getCustomerEmail();
                } else {
                    $customerEmail = "";
                }
                $tracksCollection->addFieldToFilter('customer_email', $customerEmail);
                /* end update */
            }
            $track = $tracksCollection->getFirstItem();
            if ($track && $track->getId()) {
                $account = Mage::getModel('affiliateplus/account')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($track->getAccountId());
                if($account && $account->getStatus() == 1){
                    $info[$account->getIdentifyCode()] = array(
                     'index' => 1,
                     'code' => $account->getIdentifyCode(),
                     'account' => $account,
                    );
                    $this->_affiliateInfo = $info;
                    return $this->_affiliateInfo;
               }
            }
        }

        $cookie = Mage::getSingleton('core/cookie');
        $map_index = $cookie->get('affiliateplus_map_index');
        $flag = false;

        for ($i = $map_index; $i > 0; $i--) {
            $accountCode = $cookie->get("affiliateplus_account_code_$i");
            $account = Mage::getModel('affiliateplus/account')->setStoreId(Mage::app()->getStore()->getId())->loadByIdentifyCode($accountCode);
//          Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
            if ($account && $account->getStatus() == 1) {
                $info[$accountCode] = array(
                    'index' => $i,
                    'code' => $accountCode,
                    'account' => $account,
                );
                $flag = true;
            }
        }
//          Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
        if(!$flag) {

    //      Changed By Adam (29/08/2016): check if allow the affiliate to get commission from his purchase
            if(Mage::helper('affiliateplus/config')->allowAffiliateToGetCommissionFromHisPurchase($storeId)){
                $account = Mage::getSingleton('affiliateplus/session')->getAccount();
                if($account && $account->getStatus() == 1) {
                    $info[$accountCode] = array(
                        'index' => 1,
                        'code' => $account->getIdentifyCode(),
                        'account' => $account,
                    );
                }
            }
        }
        $infoObj = new Varien_Object(array(
            'info' => $info,
        ));
        Mage::dispatchEvent('affiliateplus_get_affiliate_info', array(
            'cookie' => $cookie,
            'info_obj' => $infoObj,
        ));

        $this->_affiliateInfo = $infoObj->getInfo();
        return $this->_affiliateInfo;
    }

    public function getNumberOrdered() {
        if (is_null($this->_numberOrdered)) {
            $orderCollection = Mage::getResourceModel('sales/order_collection');
            $customer = Mage::getSingleton('customer/session')->getCustomer();
			/* edit by Jack 04/10 */
                if ($customer && $customer->getId()) {
                    $orderCollection->addFieldToFilter('customer_id', $customer->getId());
                }
			/* end edit */
			else {
                /* edit by blanka */
                if (Mage::getSingleton('checkout/session')->hasQuote()) {
                    $quote = Mage::getSingleton('checkout/session')->getQuote();
                    $orderCollection->addFieldToFilter('customer_email', $quote->getCustomerEmail());
                } 
                /* Edit By Jack */
                else if(Mage::getSingleton('adminhtml/session_quote')->getQuote()->getCustomerEmail()){
                    $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
                    $currentOrderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
                    $orderCollection->addFieldToFilter('customer_email', $quote->getCustomerEmail())
                                    ->addFieldToFilter('status', array('nin' => array('canceled')))
                                    ->setOrder('entity_id','ASC');
                    if($currentOrderId && ($currentOrderId == $orderCollection->getFirstItem()->getId()))
                    {
                        $this->_numberOrdered = 1;
                        return $this->_numberOrdered;
                    }
                }
                /* End Edit by Jack */
                else {
                    $this->_numberOrdered = 0;
                    return $this->_numberOrdered;
                }
                /* end edit by blanka */
            }
            $this->_numberOrdered = $orderCollection->getSize();
        }
        return $this->_numberOrdered;
    }

    //hainh 23-07-2014
    public function saveCookie($accountCode, $expiredTime, $toTop = false, $controller = null) {
        $cookie = Mage::getSingleton('core/cookie');
        $request = Mage::app()->getRequest();
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
        if ($toTop) {
            $datenow = date('Y-m-d');
            $cookie->set($accountCode, $datenow);
        }
    }

}
