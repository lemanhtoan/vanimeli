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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsBehavior Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsBehavior
 * @author      Magestore Developer
 */
class Magestore_RewardPointsBehavior_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * tweeting on twitter success
     */
    public function tweetAction() {        
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(!$customer) return;
        $link = $this->getRequest()->getParam('link');
        if(strpos($link,'?k='))
            $link = substr($link, 0, strpos($link,'?k='));
        if(!Mage::helper('rewardpointsbehavior')->canEarnPoint('twitter', $customer, $link)){
            echo Mage::helper('rewardpointsbehavior')->__('You can not earn points for this action.');
            return;
        }
        $earnPoint = Mage::helper('rewardpointsbehavior')->getEarnPoint('twitter', $customer);
        $tw_point = array(
            'tw_earn' => $earnPoint
        );
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'tweeting', $customer, $tw_point, $link
            );
            echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for tweeting this via Twitter.', Mage::helper('rewardpoints/point')->format($earnPoint));
            return;
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }
    }
    
    public function faceshareAction() {        
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(!$customer) return;
        $link = $this->getRequest()->getParam('link');
        if(strpos($link,'?k='))
            $link = substr($link, 0, strpos($link,'?k='));
        if(!Mage::helper('rewardpointsbehavior')->canEarnPoint('facebook_share', $customer, $link)){
            echo Mage::helper('rewardpointsbehavior')->__('You can not earn points for this action.');
            return;
        }
        $earnPoint = Mage::helper('rewardpointsbehavior')->getEarnPoint('facebook_share', $customer);
        $share_point = array(
            'share_earn' => $earnPoint
        );
        try {
            Mage::helper('rewardpoints/action')->addTransaction(
                    'fbshare', $customer, $share_point, $link
            );
            echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for sharing this via Facebook.', Mage::helper('rewardpoints/point')->format($earnPoint));
            return;
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }
    }

    /**
     * action earn point for facebook
     * @return type
     */
    public function facebookAction() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(!$customer) return;
        $link = $this->getRequest()->getParam('link');
        if(strpos($link,'?k='))
            $link = substr($link, 0, strpos($link,'?k='));
        $remove = $this->getRequest()->getParam('remove');
        if(!$remove){
            if(!Mage::helper('rewardpointsbehavior')->canEarnPoint('facebook', $customer, $link)){
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn points for this action.');
                return;
            }
            $earnPoint = Mage::helper('rewardpointsbehavior')->getEarnPoint('facebook', $customer);
            $fb_point = array(
                'fb_earn' => $earnPoint,
            );
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'fblike', $customer, $fb_point, $link
                );
                echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for liking this via Facebook.', Mage::helper('rewardpoints/point')->format($earnPoint));
                return;
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }
        } else {
            $earnedlike = Mage::helper('rewardpointsbehavior')->getSocialEarned('fblike', $customer->getId(), $link);
            $earnedRefund = $earnedlike->getFieldTotal();
            if (!$earnedRefund)
                return;
            $earned = $earnedlike->getFirstItem();
            if ($earned->getStatus() == Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED)
                return;
            $earnedlike->getFirstItem()->cancelTransaction();
            return;
        }
    }

    /**
     * action earn point for google+
     * @return type
     */
    public function googleplusAction() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if(!$customer) return;
        $link = $this->getRequest()->getParam('link');
        if(strpos($link,'?k='))
            $link = substr($link, 0, strpos($link,'?k='));
        $remove = $this->getRequest()->getParam('remove');
        if($remove == 'on'){
            if(!Mage::helper('rewardpointsbehavior')->canEarnPoint('googleplus', $customer, $link)){
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn points for this action.');
                return;
            }
            $earnPoint = Mage::helper('rewardpointsbehavior')->getEarnPoint('googleplus', $customer);
            $gg_point = array(
                'gg_earn' => $earnPoint,
            );
            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                        'ggplus', $customer, $gg_point, $link
                );
                echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for +1 via Google+.', Mage::helper('rewardpoints/point')->format($earnPoint));
                return;
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }
        } else {
            $earnedlike = Mage::helper('rewardpointsbehavior')->getSocialEarned('ggplus', $customer->getId(), $link);
            $earnedRefund = $earnedlike->getFieldTotal();
            if (!$earnedRefund)
                return;
            $earned = $earnedlike->getFirstItem();
            if ($earned->getStatus() == Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED)
                return;
            $earnedlike->getFirstItem()->cancelTransaction();
            return;
        }
    }
    
//    public function linkedInAction(){
//        
//    }

     /**
     * xuanbinh 31-07-2015
     * Pinterest
     */
    public function pinAction() { 
        // validate the user agent ("Pinterest/0.1 +http://pinterest.com/" as of 2015-07-31)
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $data = Mage::helper('rewardpointsbehavior')->decrypt($_GET['data']);
	$this->_redirectUrl($data['image']);
		
        // if user pinned the item
        if (preg_match('/^Pinterest+.*$/', $userAgent)) {
            $customerId = $data['cus'];
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if(!$customer) return;
            $link = $data['share'];
            if(strpos($link,'?k='))
                $link = substr($link, 0, strpos($link,'?k='));
            if(!Mage::helper('rewardpointsbehavior')->canEarnPoint('pinterest', $customer, $link)){
                echo Mage::helper('rewardpointsbehavior')->__('You can not earn points for this action.');
                return;
            }

            $earnPoint = Mage::helper('rewardpointsbehavior')->getEarnPoint('pinterest', $customer);
            $pinterest_point = array(
                    'pinnterest_earn' => $earnPoint
            );

            try {
                Mage::helper('rewardpoints/action')->addTransaction(
                                'pin', $customer, $pinterest_point, $link
                );

                echo Mage::helper('rewardpointsbehavior')->__('You have just earned %s for pin this via Pinterest.', Mage::helper('rewardpoints/point')->format($earnPoint));
                return;
            } catch (Exception $exc) {
                echo $exc->getMessage();
                return;
            }
        }
    }
}
