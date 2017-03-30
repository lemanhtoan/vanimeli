<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Klarna
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */


class Vaimo_Klarna_Model_Cron extends Mage_Core_Model_Abstract
{

    public function statusUpdateOfPendingOrders()
    {
        $orders = Mage::getModel("sales/order")->getCollection()
            ->addFieldToFilter("state", array('eq' => Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW))
            ->addFieldToFilter("updated_at", array('gteq' => date("Y-m-d H:i:s", time() - 172800))); // 2 Days
        /* @var Mage_Sales_Model_Order $order */
        foreach ($orders as $order) {
            try {
                $payment = $order->getPayment();
                if (Mage::helper('klarna')->isMethodKlarna($payment->getMethod())) {
                    $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, true);
                    $order->save();
                }
            } catch (Exception $e) {
                // Do nothing?
            }
        }
    }

    public function purgeLogs()
    {
        /** @var Vaimo_Klarna_Model_Resource_Log $logs */
        $logs = Mage::getResourceModel('klarna/log');
        $logs->purgeLog(-180);
        /** @var Vaimo_Klarna_Model_Resource_Log_Data $log_data */
        $log_data = Mage::getResourceModel('klarna/log_data');
        $log_data->purgeLog(-30);
    }
}
