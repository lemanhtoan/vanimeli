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
 * RewardPoints Adminhtml Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Model_Adminhtml_Observer {

    /**
     * process event: adminhtml_customer_save_after
     * 
     * @param type $observer
     */
    public function customerSaveAfter($observer) {
        $customer = $observer['customer'];
        $params = Mage::app()->getRequest()->getParam('rewardpoints');
        if (empty($params['admin_editing'])) {
            return;
        }

        // Update reward account settings
        $rewardAccount = Mage::getModel('rewardpoints/customer')->load($customer->getId(), 'customer_id');
        $rewardAccount->setCustomerId($customer->getId());
        if (!$rewardAccount->getId()) {
            $rewardAccount->setData('point_balance', 0)
                    ->setData('holding_balance', 0)
                    ->setData('spent_balance', 0);
        }
        $params['is_notification'] = empty($params['is_notification']) ? 0 : 1;
        $params['expire_notification'] = empty($params['expire_notification']) ? 0 : 1;
        $rewardAccount->setData('is_notification', $params['is_notification'])
                ->setData('expire_notification', $params['expire_notification']);
        $rewardAccount->save();

        // Create transactions for customer if need
        if (!empty($params['change_balance'])) {
            try {
                Mage::helper('rewardpoints/action')->addTransaction('admin', $customer, new Varien_Object(array(
                    'point_amount' => $params['change_balance'],
                    'title' => $params['change_title'],
                    'expiration_day' => (int) $params['expiration_day'],
                        ))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('rewardpoints')->__("An error occurred while changing the customer's point balance.")
                );
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    /**
     * process event to force create credit memo when purchase order by points
     * 
     * @param type $observer
     */
    public function salesOrderLoadAfter($observer) {
        $order = $observer['order'];
        if ($order->getRewardpointsSpent() < 0.0001 || $order->getState() === Mage_Sales_Model_Order::STATE_CLOSED || $order->isCanceled() || $order->canUnhold()
        ) {
            return $this;
        }
        if ($order->getTotalItemCount()) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItemId())
                    continue;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        if (($child->getQtyInvoiced() - $child->getQtyRefunded() - $child->getQtyCanceled()) > 0) {
                            $order->setForcedCanCreditmemo(true);
                            return $this;
                        }
                    }
                } elseif ($item->getRewardpointsSpent()) {
                    if (($item->getQtyInvoiced() - $item->getQtyRefunded() - $item->getQtyCanceled()) > 0) {
                        $order->setForcedCanCreditmemo(true);
                        return $this;
                    }
                }
            }
        }
    }

    /**
     * process event to turn off forced credit memo of order
     * 
     * @param type $observer
     */
    public function salesOrderCreditmemoRefund($observer) {
        $creditmemo = $observer['creditmemo'];
        $order = $creditmemo->getOrder();
        if ($order->getRewardpointsSpent() && $order->getForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(false);
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
