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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Observer {
    /**
     * process customer register succucess for mageno 1.7xx
     * @param type $observer
     */
//    public function customerRegisterSuccess($observer) {
//
//        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
//        $model->setCustomerId($observer->getCustomer()->getId());
//        try {
//            $model->save();
//        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
//        }
//    }

    /**
     *  process customer register succucess for mageno below 1.7xx
     * @param type $observer
     * @return type
     */
    public function customerRegisterSuccessForLow($observer) {
        if (!Mage::helper('rewardpointsreferfriends')->isEnable()) {
            return;
        }
        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');
        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
        if(is_object($refer_cus) &&  $refer_cus->getCustomerId()){  
            $customer_reg = $observer->getCustomer();
            $customer_reg->getCreatedAt();
            $created_at = date('Y-m-d', strtotime($customer_reg->getCreatedAt()));
            $current_date = date('Y-m-d');
            if (strtotime($current_date) != strtotime($created_at)) {
                return;
            }
            if (!$customer_reg->getId() || Mage::app()->getRequest()->getActionName() == 'editPost')
                return;
            $refer_cus = Mage::getModel('customer/customer')->load($refer_cus->getCustomerId());
            $friend_rw = Mage::helper('rewardpoints/customer')->getAccountByCustomer($customer_reg);
            $checkip = $this->checkIpinMonth(Mage::app()->getRequest()->getClientIp(),$customer_reg->getId());
            if (!is_object($friend_rw) || !$friend_rw->getId()) {
                $friend_rw->setCustomerId($customer_reg->getId())
                    ->setData('referal_id',$refer_cus->getId())
                    ->setIpAdress(Mage::app()->getRequest()->getClientIp())
                    ->setCreatedTime(now())
                    ->save();
            }elseif($friend_rw->getReferalId()){
               // return;
            }else{
                $friend_rw->setData('referal_id',$refer_cus->getId())
                    ->setIpAdress(Mage::app()->getRequest()->getClientIp())
                    ->setCreatedTime(now())
                    ->save();
            }
           
            if($checkip || (Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_register_point',$customer_reg->getStoreId())<1))
                return;
			
			$checkTransaction = Mage::getModel('rewardpoints/transaction')->getCollection()
											->addFieldToFilter('action', 'referfriends_registed')
											->addFieldToFilter('customer_id', $refer_cus->getId())
											->addFieldToFilter('extra_content', $customer_reg->getId());
			if(!count($checkTransaction)){
				try {
					Mage::helper('rewardpoints/action')->addTransaction(
							'referfriends_registed', $refer_cus, $customer_reg, array(
						'extra_content' => Mage::helper('rewardpointsreferfriends')->__('Friend Register Success')
							)
					);
				} catch (Exception $exc) {
					echo $exc->getMessage();
				}
			}
    //        return $this;
        }
    }
    
    public function checkIpinMonth($ip,$customerid){
        $pastTime   = date('Y-m-d H:i:s', time() - 30 * 86400);
//        $nowTime      = date('Y-m-d H:i:s', time() + 86400);
        $collection = Mage::getModel('rewardpoints/customer')->getCollection();
        $collection->addFieldToFilter('ip_adress',$ip)
            ->addFieldToFilter('customer_id', array('neq' => $customerid))
            ->addFieldToFilter('created_time', array('notnull' => true))
            ->addFieldToFilter('created_time', array('from' => $pastTime));
//            ->addFieldToFilter('created_time', array('to' => $nowTime));
        if($collection->getSize()){
            return true;
        }else{ return false; }
    }

    public function customerLogin($observer) {
        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
        if(!$model || !$model->getId()){
            $model->setCustomerId($observer->getCustomer()->getId());
            try {
                $model->save();
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
    }

    /**
     * process admin customer save 
     * @param type $observer
     */
    public function customerSaveAfter($observer) {
        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($observer->getCustomer()->getId(), 'customer_id');
        $model->setCustomerId($observer->getCustomer()->getId());
        try {
            $model->save();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_RewardPointsReferFriends_Model_Observer
     */
    public function actionPredispatch($observer) {
        $key = Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key');

        if ($key) {
            $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($customerId == $refer_cus->getCustomerId() && !$refer_cus->validateReferLinkCus())
                Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
        }

        $key = Mage::app()->getRequest()->getParam('k');

        if ($key && Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') != 'coupon') {
            $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByKey($key);

            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if ($refer_cus->getId() && $customerId != $refer_cus->getCustomerId()) {

                if (!Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key') || Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key') != $key) {
                    Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $key);
                }
            }
        }
        $customer_rw = Mage::helper('rewardpoints/customer')->getAccount();
        if(is_object($customer_rw)&&$customer_rw->getReferalId()){
            $refer_cus=Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->load($customer_rw->getReferalId(),'customer_id');
            if(is_object($refer_cus)&&$refer_cus->getKey())
                Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $refer_cus->getKey());
        }
        return $this;
    }

    /**
     * Process order after save
     * 
     * @param type $observer
     * @return Magestore_RewardPoints_Model_Observer
     */
    public function salesOrderSaveAfter($observer) {
        $order = $observer['order'];
        $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        // insert referal_id to reward account
            $customer_order = Mage::getModel('customer/customer')->load($order->getCustomerId());
            if (is_object($customer_order) && $customer_order->getId()) {
                $friend_rw = Mage::helper('rewardpoints/customer')->getAccountByCustomer($customer_order);
                if (!is_object($friend_rw) || !$friend_rw->getId()) {
                    $friend_rw->setCustomerId($customer_order->getId())
                        ->setData('referal_id',$customer->getId())
                        ->setIpAdress(Mage::app()->getRequest()->getClientIp())
                        ->setCreatedTime(now())
                        ->save();
                }elseif($friend_rw->getReferalId()){
                }else{
                    $friend_rw->setData('referal_id',$customer->getId())
                        ->setIpAdress(Mage::app()->getRequest()->getClientIp())
                        ->setCreatedTime(now())
                        ->save();
                }
            }
        
        // Add earning point for customer
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE && $order->getRewardpointsReferalEarn()
        ) {

            $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
            if (!$customer->getId()) {
                return $this;
            }
            $transaction = Mage::getResourceModel('rewardpoints/transaction_collection')
                    ->addFieldToFilter('action', 'referfriends')
                    ->addFieldToFilter('order_id', $order->getId())
                    ->getFirstItem();
            if (!$transaction || !$transaction->getId()) {
                try {
                    Mage::helper('rewardpoints/action')->addTransaction(
                            'referfriends', $customer, $order
                    );
                    return $this;
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }
            }
        }
        // Refun earning point from customer if order is canceled
        if ($order->getState() == Mage_Sales_Model_Order::STATE_CLOSED && $order->getRewardpointsReferalEarn()) {
            $earnedRefund = (int) Mage::getResourceModel('rewardpoints/transaction_collection')
                            ->addFieldToFilter('action', 'referfriends')
                            ->addFieldToFilter('order_id', $order->getId())
                            ->getFieldTotal();

            if ($earnedRefund <= 0) {
                return $this;
            }
            if ($earnedRefund > $order->getRewardpointsReferalEarn()) {
                $earnedRefund = $order->getRewardpointsReferalEarn();
            }
            if ($earnedRefund > 0) {
                $order->setRefundEarnedPoints($earnedRefund);
                if (empty($customer)) {
                    $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
                }
                if (!$customer->getId()) {
                    return $this;
                }
                Mage::helper('rewardpoints/action')->addTransaction(
                        'referfriends_cancel', $customer, $order
                );
            }
        }

        return $this;
    }

    /**
     * process coupon post apply
     * @param type $observer
     * @return Magestore_RewardPointsReferFriends_Model_Observer
     */
    public function couponPost($observer) {
        if (Mage::helper('rewardpointsreferfriends')->getReferConfig('refer_method') == 'link')
            return $this;
        $action = $observer->getEvent()->getControllerAction();
        $code = trim($action->getRequest()->getParam('coupon_code'));
        if(!strlen($code)){
            $this->useDefaultCoupon();
            return $this;
        }

        $refer_cus = Mage::getModel('rewardpointsreferfriends/rewardpointsrefercustomer')->loadByCoupon($code);

        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        if (!$refer_cus->getId() || $refer_cus->getCustomerId() == $customerId) {
            return $this->useDefaultCoupon();
        }
        $allowUseCoupon = Mage::helper('rewardpointsreferfriends/calculation_earning')->checkCustomerGetDiscount(Mage::getSingleton('checkout/session')->getQuote()) && Mage::helper('rewardpointsreferfriends/calculation_earning')->checkNewCustomer(Mage::getSingleton('checkout/session')->getQuote());
        if (!$allowUseCoupon) {
            Mage::getSingleton('checkout/session')->getMessages(true);
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpointsreferfriends')->__('You cannot use this coupon anymore.'));
            $this->useDefaultCoupon();
        } else {
            if (!Mage::getSingleton('checkout/session')->getData('coupon_code'))
                Mage::getSingleton('checkout/session')->setData('coupon_code', $code);
            if ($action->getRequest()->getParam('remove') == 1) {
                if (Mage::getSingleton('checkout/session')->getData('coupon_code'))
                    Mage::getSingleton('checkout/session')->setData('coupon_code', '');
                if ($refer_cus->getKey() == Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')) {
                    Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
                    Mage::getSingleton('checkout/session')->getMessages(true);
                    Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('rewardpointsreferfriends')->__('Coupon code was canceled.'));
                }
            } else {
                Mage::getSingleton('core/cookie')->set('rewardpoints_offer_key', $refer_cus->getKey());
                Mage::getSingleton('checkout/session')->getQuote()->setCouponCode('')
                        ->collectTotals()
                        ->save();
                Mage::getSingleton('checkout/session')->getMessages(true);
                Mage::getSingleton('checkout/session')->setData('coupon_code',$code);
                Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('rewardpointsreferfriends')->__('Coupon code "%s" was applied.', $code));
            }
        }
        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        $action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }
   
    /**
     * use default magento coupon code
     * @return string
     */
    public function useDefaultCoupon() {
        if (Mage::getSingleton('core/cookie')->get('rewardpoints_offer_key')){
            Mage::getSingleton('core/cookie')->delete('rewardpoints_offer_key');
            Mage::getSingleton('checkout/session')->setData('coupon_code', '');
        }
        return;
    }

    /**
     * get data coupon_code
     * @param type $observer
     * @return string
     */
    public function getCouponCode($observer) {
        $coupon = $observer->getContainer();
        if (Mage::getSingleton('checkout/session')->getData('coupon_code')) {
            $coupon->setCouponCode(Mage::getSingleton('checkout/session')->getData('coupon_code'));
        }
        return;
    }
    
    
    /**
     * process invoice save after
     * @param type $observer
     * @return \Magestore_RewardPointsReferFriends_Model_Observer
     */
    public function salesOrderInvoiceSaveAfter($observer) {
        $invoice = $observer['invoice'];
        $order = $invoice->getOrder();
        if ($invoice->getState() != Mage_Sales_Model_Order_Invoice::STATE_PAID || !$order->getRewardpointsReferalEarn()
        ) {
            return $this;
        }
        if (!Mage::getStoreConfigFlag(
                        Magestore_RewardPoints_Helper_Calculation_Earning::XML_PATH_EARNING_ORDER_INVOICE, $order->getStoreId()
                )) {
            return $this;
        }
        if(!$invoice->getRewardpointsReferalEarn())
            return $this;
        $customer = Mage::getModel('customer/customer')->load($order->getRewardpointsReferCustomerId());
        if (!$customer->getId()) {
            return $this;
        }
        $transaction = Mage::getResourceModel('rewardpoints/transaction_collection')
                ->addFieldToFilter('action', 'referfriends')
                ->addFieldToFilter('order_id', $order->getId())
                ->getFirstItem();
        
        if (!$transaction || !$transaction->getId()) {
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'referfriends', $customer, $order
                );
                return $this;
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
        }
        return $this;
    }

}
