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
 * RewardPoints Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_IndexController extends Mage_Core_Controller_Front_Action {

    /**
     * check customer is logged in
     */
    public function preDispatch() {
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        if (!Mage::helper('rewardpoints')->isEnable()) {
            $this->_redirect('customer/account');
            $this->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            return;
        }
        $action = $this->getRequest()->getActionName();
        if ($action != 'policy' && $action != 'redirectLogin') {
            // Check customer authentication
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                Mage::getSingleton('customer/session')->setAfterAuthUrl(
                        Mage::getUrl($this->getFullActionName('/'))
                );
                $this->_redirect('customer/account/login');
                $this->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            }
        }
    }

    public function redirectLoginAction() {
        if (!Mage::helper('customer')->isLoggedIn()) {
            $url = base64_decode($this->getRequest()->getParam('redirect'));
            if (strpos($url, 'checkout/onepage') !== false) {
                $url = Mage::getUrl('checkout/onepage/index');
            }
            //Mage::getSingleton('customer/session')->setBeforeAuthUrl($url);
            Mage::getSingleton('customer/session')->setAfterAuthUrl($url);
        }
        $this->_redirect('customer/account/login');
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->loadLayout();
        $this->_title(Mage::helper('rewardpoints')->__('My Reward Points'));
        $this->renderLayout();
    }

    /**
     * transaction action
     */
    public function transactionsAction() {
        $this->loadLayout();
        $this->_title(Mage::helper('rewardpoints')->__('Point Transactions'));
        $this->renderLayout();
    }

    /**
     * policy action
     */
    public function policyAction() {
        $this->loadLayout();
        $page = Mage::getSingleton('cms/page');
        if ($page && $page->getId()) {
            $this->_title($page->getContentHeading());
        } else {
            $this->_title(Mage::helper('rewardpoints')->__('Reward Policy'));
        }
        $this->renderLayout();
    }

    /**
     * setting action
     */
    public function settingsAction() {
        $this->loadLayout();
        $this->_title(Mage::helper('rewardpoints')->__('Reward Points Settings'));
        $this->renderLayout();
    }

    /**
     * setting post action
     */
    public function settingsPostAction() {
        if ($this->getRequest()->isPost() && Mage::getSingleton('customer/session')->isLoggedIn()
        ) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $rewardAccount = Mage::getModel('rewardpoints/customer')->load($customerId, 'customer_id');
            if (!$rewardAccount->getId()) {
                $rewardAccount->setCustomerId($customerId)
                        ->setData('point_balance', 0)
                        ->setData('holding_balance', 0)
                        ->setData('spent_balance', 0);
            }
            $rewardAccount->setIsNotification((boolean) $this->getRequest()->getPost('is_notification'))
                    ->setExpireNotification((boolean) $this->getRequest()->getPost('expire_notification'));
            try {
                $rewardAccount->save();
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('rewardpoints')->__('Your settings has been updated successfully.'));
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/settings');
    }

}
