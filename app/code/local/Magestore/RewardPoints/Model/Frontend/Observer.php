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
 * RewardPoints Frontend Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Frontend_Observer {

    /**
     * add speding points block into payment method
     * 
     * @param type $observer
     */
    public function rewardpointsPaymentMethod($observer) {
        $block = $observer['block'];
        if (($block instanceof Mage_Checkout_Block_Onepage_Payment_Methods ||  $block instanceof Magestore_Webpos_Block_Onepage_Payment_Methods)  && Mage::helper('rewardpoints')->isEnableOutput()) {
            $requestPath = $block->getRequest()->getRequestedRouteName()
                    . '_' . $block->getRequest()->getRequestedControllerName()
                    . '_' . $block->getRequest()->getRequestedActionName();
            if ($requestPath == 'checkout_onepage_index' &&  ! Mage::helper('core')->isModuleOutputEnabled('Amasty_Scheckout')) {
                return;
            }
//            hiepdd use max points default
            $checkoutSession = Mage::getSingleton('checkout/session');
            $spendingHelper = Mage::helper('rewardpoints/calculation_spending');
            if ($checkoutSession->getData('use_max') !==0 && $spendingHelper->isUseMaxPointsDefault()) {
                $checkoutSession->setData('use_point', 1);
                $checkoutSession->setData('use_max', 1);
            } 
//            hiepdd end
            $transport = $observer['transport'];
            $html_addrewardpoints = $block->getLayout()->createBlock('rewardpoints/checkout_onepage_payment_rewrite_methods')->renderView();
            $html = $transport->getHtml();
//            if (version_compare(Mage::getVersion(), '1.8.0', '>=') && Mage::app()->getRequest()->getRouteName() == 'checkout') {
//                $html = '<dl class="sp-methods" id="checkout-payment-method-load">' . $html . '</dl>';
//            }
            $html .= '<script type="text/javascript">checkOutLoadRewardpoints(' . Mage::helper('core')->jsonEncode(array('html' => $html_addrewardpoints)) . ');</script>';

            $transport->setHtml($html);
        }
    }

    /**
     * transfer reward points discount to Paypal gateway
     * 
     * @param type $observer
     */
    public function paypalPrepareLineItems($observer) {
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            if ($paypalCart = $observer->getPaypalCart()) {
                $salesEntity = $paypalCart->getSalesEntity();

                $baseDiscount = $salesEntity->getRewardpointsBaseDiscount();
                if ($baseDiscount < 0.0001 && $salesEntity instanceof Mage_Sales_Model_Quote) {
                    $helper = Mage::helper('rewardpoints/calculation_spending');
                    $baseDiscount = $helper->getPointItemDiscount();
                    $baseDiscount += $helper->getCheckedRuleDiscount();
                    $baseDiscount += $helper->getSliderRuleDiscount();
                }
                //$baseDiscount -= $salesEntity->getRewardpointsBaseHiddenTaxAmount();
                if ($baseDiscount > 0.0001) {
                    $paypalCart->updateTotal(
                            Mage_Paypal_Model_Cart::TOTAL_DISCOUNT, (float) $baseDiscount, Mage::helper('rewardpoints')->__('Use points on spend')
                    );
                }
            }
            return $this;
        }
        $salesEntity = $observer->getSalesEntity();
        $additional = $observer->getAdditional();
        if ($salesEntity && $additional) {
            $baseDiscount = $salesEntity->getRewardpointsBaseDiscount();
            if ($baseDiscount < 0.0001 && $salesEntity instanceof Mage_Sales_Model_Quote) {
                $helper = Mage::helper('rewardpoints/calculation_spending');
                $baseDiscount = $helper->getPointItemDiscount();
                $baseDiscount += $helper->getCheckedRuleDiscount();
                $baseDiscount += $helper->getSliderRuleDiscount();
            }

            if ($baseDiscount > 0.0001) {
                $items = $additional->getItems();
                $items[] = new Varien_Object(array(
                    'name' => Mage::helper('rewardpoints')->__('Use points on spend'),
                    'qty' => 1,
                    'amount' => -(float) $baseDiscount,
                ));
                $additional->setItems($items);
            }
        }
    }

}
