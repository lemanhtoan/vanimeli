<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_Klarna_Helper_Data extends Mage_Core_Helper_Abstract
{
    const KLARNA_METHOD_INVOICE  = 'vaimo_klarna_invoice';
    const KLARNA_METHOD_ACCOUNT  = 'vaimo_klarna_account';
    const KLARNA_METHOD_SPECIAL  = 'vaimo_klarna_special';
    const KLARNA_METHOD_CHECKOUT = 'vaimo_klarna_checkout';

    const KLARNA_API_CALL_RESERVE          = 'reserve';
    const KLARNA_API_CALL_CAPTURE          = 'capture';
    const KLARNA_API_CALL_REFUND           = 'refund';
    const KLARNA_API_CALL_CANCEL           = 'cancel';
    const KLARNA_API_CALL_CHECKSTATUS      = 'check_status';
    const KLARNA_API_CALL_ADDRESSES        = 'addresses';
    const KLARNA_API_CALL_PCLASSES         = 'pclasses';
    const KLARNA_API_CALL_CHECKOUTSERVICES = 'checkout_services';

    const KLARNA_API_CALL_KCODISPLAY_ORDER = 'kco_display_order';
    const KLARNA_API_CALL_KCOCREATE_ORDER  = 'kco_create_order';
    const KLARNA_API_CALL_KCOVALIDATE_ORDER = 'kco_validate_order';

    const KLARNA_KCO_QUEUE_RETRY_ATTEMPTS = 10;

    const KLARNA_STATUS_ACCEPTED = 'accepted';
    const KLARNA_STATUS_PENDING  = 'pending';
    const KLARNA_STATUS_DENIED   = 'denied';

    const KLARNA_INFO_FIELD_FEE                         = 'vaimo_klarna_fee';
    const KLARNA_INFO_FIELD_FEE_TAX                     = 'vaimo_klarna_fee_tax';
    const KLARNA_INFO_FIELD_BASE_FEE                    = 'vaimo_klarna_base_fee';
    const KLARNA_INFO_FIELD_BASE_FEE_TAX                = 'vaimo_klarna_base_fee_tax';
    const KLARNA_INFO_FIELD_FEE_CAPTURED_TRANSACTION_ID = 'klarna_fee_captured_transaction_id';
    const KLARNA_INFO_FIELD_FEE_REFUNDED                = 'klarna_fee_refunded';

    const KLARNA_INFO_FIELD_RESERVATION_STATUS  = 'klarna_reservation_status';
    const KLARNA_INFO_FIELD_RESERVATION_ID      = 'klarna_reservation_id';
    const KLARNA_INFO_FIELD_CANCELED_DATE       = 'klarna_reservation_canceled_date';
    const KLARNA_INFO_FIELD_REFERENCE           = 'klarna_reservation_reference';
    const KLARNA_INFO_FIELD_ORDER_ID            = 'klarna_reservation_order_id';
    const KLARNA_INFO_FIELD_INVOICE_LIST        = 'klarna_invoice_list';
    const KLARNA_INFO_FIELD_INVOICE_LIST_STATUS = 'invoice_status';
    const KLARNA_INFO_FIELD_INVOICE_LIST_ID     = 'invoice_id';
    const KLARNA_INFO_FIELD_INVOICE_LIST_KCO_ID = 'invoice_kco_id';
    const KLARNA_INFO_FIELD_HOST                = 'klarna_reservation_host';
    const KLARNA_INFO_FIELD_MERCHANT_ID         = 'merchant_id';
    const KLARNA_INFO_FIELD_NOTICE              = 'klarna_notice';

    const KLARNA_INFO_FIELD_PAYMENT_PLAN              = 'payment_plan';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_TYPE         = 'payment_plan_type';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHS       = 'payment_plan_months';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_START_FEE    = 'payment_plan_start_fee';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_INVOICE_FEE  = 'payment_plan_invoice_fee';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_TOTAL_COST   = 'payment_plan_total_cost';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHLY_COST = 'payment_plan_monthly_cost';
    const KLARNA_INFO_FIELD_PAYMENT_PLAN_DESCRIPTION  = 'payment_plan_description';

    const KLARNA_FORM_FIELD_PHONENUMBER = 'phonenumber';
    const KLARNA_FORM_FIELD_PNO         = 'pno';
    const KLARNA_FORM_FIELD_ADDRESS_ID  = 'address_id';
    const KLARNA_FORM_FIELD_DOB_YEAR    = 'dob_year';
    const KLARNA_FORM_FIELD_DOB_MONTH   = 'dob_month';
    const KLARNA_FORM_FIELD_DOB_DAY     = 'dob_day';
    const KLARNA_FORM_FIELD_CONSENT     = 'consent';
    const KLARNA_FORM_FIELD_GENDER      = 'gender';
    const KLARNA_FORM_FIELD_EMAIL       = 'email';

    const KLARNA_API_RESPONSE_STATUS         = 'response_status';
    const KLARNA_API_RESPONSE_TRANSACTION_ID = 'response_transaction_id';
    const KLARNA_API_RESPONSE_FEE_REFUNDED   = 'response_fee_refunded';
    const KLARNA_API_RESPONSE_FEE_CAPTURED   = 'response_fee_captured';
    const KLARNA_API_RESPONSE_KCO_CAPTURE_ID = 'response_kco_capture_id';
    const KLARNA_API_RESPONSE_KCO_LOCATION   = 'response_kco_location';

    const KLARNA_LOGOTYPE_TYPE_INVOICE  = 'invoice';
    const KLARNA_LOGOTYPE_TYPE_ACCOUNT  = 'account';
    const KLARNA_LOGOTYPE_TYPE_CHECKOUT = 'checkout';
    const KLARNA_LOGOTYPE_TYPE_BOTH     = 'unified';
    const KLARNA_LOGOTYPE_TYPE_BASIC    = 'basic';

    const KLARNA_FLAG_ITEM_NORMAL = "normal";
    const KLARNA_FLAG_ITEM_SHIPPING_FEE = "shipping";
    const KLARNA_FLAG_ITEM_HANDLING_FEE = "handling";

    const KLARNA_REFUND_METHOD_FULL = "full";
    const KLARNA_REFUND_METHOD_PART = "part";
    const KLARNA_REFUND_METHOD_AMOUNT = "amount";

    const KLARNA_LOGOTYPE_POSITION_FRONTEND = 'frontend';
    const KLARNA_LOGOTYPE_POSITION_PRODUCT  = 'product';
    const KLARNA_LOGOTYPE_POSITION_CHECKOUT = 'checkout';

    const KLARNA_DISPATCH_RESERVED = 'vaimo_paymentmethod_order_reserved';
    const KLARNA_DISPATCH_CAPTURED = 'vaimo_paymentmethod_order_captured';
    const KLARNA_DISPATCH_REFUNDED = 'vaimo_paymentmethod_order_refunded';
    const KLARNA_DISPATCH_CANCELED = 'vaimo_paymentmethod_order_canceled';

    const KLARNA_LOG_START_TAG = '---------------START---------------';
    const KLARNA_LOG_END_TAG = '----------------END----------------';

    const KLARNA_EXTRA_VARIABLES_GUI_OPTIONS = 0;
    const KLARNA_EXTRA_VARIABLES_GUI_LAYOUT  = 1;
    const KLARNA_EXTRA_VARIABLES_OPTIONS     = 2;

    const KLARNA_KCO_API_VERSION_STD = 2;
    const KLARNA_KCO_API_VERSION_UK  = 3;
    const KLARNA_KCO_API_VERSION_USA = 4;

    const KLARNA_LOG_LEVEL_FULL = 0;
    const KLARNA_LOG_LEVEL_MODERATE = 1;
    const KLARNA_LOG_LEVEL_MINMIAL = 2;
    const KLARNA_LOG_LEVEL_NONE = 3;


    public static $isEnterprise;


    protected $_supportedMethods = array(
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_INVOICE,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_ACCOUNT,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_SPECIAL,
        Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT
    );

    protected $_klarnaFields = array(
        self::KLARNA_INFO_FIELD_FEE,
        self::KLARNA_INFO_FIELD_FEE_TAX,
        self::KLARNA_INFO_FIELD_BASE_FEE,
        self::KLARNA_INFO_FIELD_BASE_FEE_TAX,
        self::KLARNA_INFO_FIELD_FEE_CAPTURED_TRANSACTION_ID,
        self::KLARNA_INFO_FIELD_FEE_REFUNDED,

        self::KLARNA_INFO_FIELD_RESERVATION_STATUS,
        self::KLARNA_INFO_FIELD_RESERVATION_ID,
        self::KLARNA_INFO_FIELD_CANCELED_DATE,
        self::KLARNA_INFO_FIELD_REFERENCE,
        self::KLARNA_INFO_FIELD_ORDER_ID,
        self::KLARNA_INFO_FIELD_INVOICE_LIST,
        self::KLARNA_INFO_FIELD_INVOICE_LIST_STATUS,
        self::KLARNA_INFO_FIELD_INVOICE_LIST_ID,
        self::KLARNA_INFO_FIELD_HOST,
        self::KLARNA_INFO_FIELD_MERCHANT_ID,

        self::KLARNA_INFO_FIELD_PAYMENT_PLAN,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_TYPE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHS,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_START_FEE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_INVOICE_FEE,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_TOTAL_COST,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_MONTHLY_COST,
        self::KLARNA_INFO_FIELD_PAYMENT_PLAN_DESCRIPTION,

        self::KLARNA_FORM_FIELD_PHONENUMBER,
        self::KLARNA_FORM_FIELD_PNO,
        self::KLARNA_FORM_FIELD_ADDRESS_ID,
        self::KLARNA_FORM_FIELD_DOB_YEAR,
        self::KLARNA_FORM_FIELD_DOB_MONTH,
        self::KLARNA_FORM_FIELD_DOB_DAY,
        self::KLARNA_FORM_FIELD_CONSENT,
        self::KLARNA_FORM_FIELD_GENDER,
        self::KLARNA_FORM_FIELD_EMAIL,

    );

    const KLARNA_CHECKOUT_ENABLE_NEWSLETTER          = 'payment/vaimo_klarna_checkout/enable_newsletter';
    const KLARNA_CHECKOUT_EXTRA_ORDER_ATTRIBUTE      = 'payment/vaimo_klarna_checkout/extra_order_attribute';
    const KLARNA_CHECKOUT_ENABLE_CART_ABOVE_KCO      = 'payment/vaimo_klarna_checkout/enable_cart_above_kco';

    const KLARNA_CHECKOUT_NEWSLETTER_DISABLED       = 0;
    const KLARNA_CHECKOUT_NEWSLETTER_SUBSCRIBE      = 1;
    const KLARNA_CHECKOUT_NEWSLETTER_DONT_SUBSCRIBE = 2;

    const KLARNA_CHECKOUT_ALLOW_ALL_GROUP_ID = 99;

    const KLARNA_DISPATCH_VALIDATE = 'vaimo_klarna_validate_cart';
    const KLARNA_VALIDATE_ERRORS = 'klarna_validate_errors';

    const ENCODING_MAGENTO = 'UTF-8';
    const ENCODING_KLARNA = 'ISO-8859-1';

    /**
     * The name in SESSION variable of the function currently executing, only used for logs
     */
    const LOG_FUNCTION_SESSION_NAME = 'klarna_log_function_name';

    protected static $_logFunctionNameArray = array();

    protected static $_klarnaCheckoutId;

    public function setCheckoutId($checkoutId)
    {
        self::$_klarnaCheckoutId = $checkoutId;
    }

    /**
     * Convert into ASCII with some translation of special characters to pure text
     *
     * @param $str
     * @param null $from
     * @param null $to
     * @return string
     */
    protected function _cleanAndConvert($str, $from = null, $to = null)
    {
        $res = iconv($from, 'ASCII//TRANSLIT', $str);
        $this->logKlarnaDebug('_cleanAndConvert: ' . $res);
        return $res;
    }
    
    
    /**
     * Encode the string to klarna encoding
     *
     * @param string $str  string to encode
     * @param string $from from encoding
     * @param string $to   target encoding
     *
     * @return string
     */
    public function encode($str, $from = null, $to = null)
    {
        if ($from === null) {
            $from = self::ENCODING_MAGENTO;
        }
        if ($to === null) {
            $to = self::ENCODING_KLARNA;
        }
        try {
            $res = iconv($from, $to, $str);
            if ($str && !$res) {
                $res = $this->_cleanAndConvert($str, $from, $to);
            }
        } catch (Exception $e) {
            try {
                $this->logKlarnaDebug('encode exception: ' . $e->getMessage());
                $res = $this->_cleanAndConvert($str, $from, $to);
            } catch (Exception $e) {
                $this->logKlarnaDebug('iconv failed in encode, returning error (' . $str . ')');
            }
        }
        if ($str && !$res) {
            $res = 'encode-error';
        }
        return $res;
    }

    /**
     * Decode the string to the Magento encoding
     *
     * @param string $str  string to decode
     * @param string $from from encoding
     * @param string $to   target encoding
     *
     * @return string
     */
    public function decode($str, $from = null, $to = null)
    {
        if ($from === null) {
            $from = self::ENCODING_KLARNA;
        }
        if ($to === null) {
            $to = self::ENCODING_MAGENTO;
        }
        try {
            $res = iconv($from, $to, $str);
        } catch (Exception $e) {
            $this->logKlarnaDebug('iconv failed in decode, returning error (' . $str . ')');
        }
        if ($str && !$res) {
            $res = 'decode-error';
        }
        return $res;
    }

    public function getSupportedMethods()
    {
        return $this->_supportedMethods;
    }

    public function isKlarnaField($key)
    {
        return (in_array($key,$this->_klarnaFields));
    }

    public function isMethodKlarna($method)
    {
        if (in_array($method, $this->getSupportedMethods())) {
            return true;
        }
        return false;
    }

    public function getInvoiceLink($order, $transactionId)
    {
        $link = "";
        if ($order) {
            $payment = $order->getPayment();
            if ($payment) {
                $host = $payment->getAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_HOST);
                $domain = ($host === 'LIVE') ? 'online': 'testdrive';
                $link = "https://{$domain}.klarna.com/invoices/" . $transactionId . ".pdf";
            }
        }
        return $link;
    }

    public function shouldItemBeIncluded($item)
    {
        if ($item->getParentItemId()>0 && $item->getPriceInclTax()==0) return false;
        if ($item->getOrderItemId()) {
            $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            if ($orderItem->getParentItemId()>0 && $item->getPriceInclTax()==0) return false;
        }
        return true;
    }

    public function isShippingInclTax($storeId)
    {
        return Mage::getSingleton('tax/config')->displaySalesShippingInclTax($storeId);
    }

    /**
     * Check if OneStepCheckout is activated or not
     * It also checks if OneStepCheckout is activated, but it's currently using
     * standard checkout
     *
     * @return bool
     */
    public function isOneStepCheckout($store = null)
    {
        $res = false;
        if (Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links', $store)) {
            $res = true;
            $request = Mage::app()->getRequest();
            $requestedRouteName = $request->getRequestedRouteName();
            $requestedControllerName = $request->getRequestedControllerName();
            if ($requestedRouteName == 'checkout' && $requestedControllerName == 'onepage') {
                $res = false;
            }
        }
        return $res;
    }

    /**
     * Returns checkout/cart unless specific redirect specified
     *
     */
    public function getKCORedirectToCartUrl($store = null)
    {
        $res = Mage::getStoreConfig('payment/vaimo_klarna_checkout/cart_redirect', $store);
        if (!$res) {
            $res = 'checkout/cart';
        }
        return $res;
    }

    /**
     * Check if FireCheckout is activated or not
     *
     * @return bool
     */
    public function isFireCheckout($store = null)
    {
        $res = false;
        if (Mage::getStoreConfig('firecheckout/general/enabled', $store)) {
            $res = true;
        }
        return $res;
    }

    /**
     * Check if VaimoCheckout is activated or not
     *
     * @return bool
     */
    public function isVaimoCheckout($store = null)
    {
        $res = false;
        if (Mage::getStoreConfig('checkout/options/vaimo_checkout_enabled', $store)) {
            $res = true;
        }
        return $res;
    }

    /**
     * Check if Vaimo_QuickCheckout is activated or not
     *
     * @return bool
     */
    public function isQuickCheckout($store = null)
    {
        $res = false;
        try {
            $node = Mage::getConfig()->getNode("modules/Icommerce_QuickCheckout");
            if ($node) {
                if ($node->active=='true'){
                    $res = true;
                }
            }
        } catch (Exception $e) {
        }
        return $res;
    }

    /*
     * Last minute change. We were showing logotype instead of title, but the implementation was not
     * as good as we wanted, so we reverted it and will make it a setting. This function will be the
     * base of that setting. If it returns false, we should show the logotype together with the title
     * otherwise just show the title.
     */
    public function showTitleAsTextOnly()
    {
        return true;
    }

    /**
     * Check if OneStepCheckout displays their prises with the tax included
     *
     * @return bool
     */
    public function isOneStepCheckoutTaxIncluded()
    {
        return (bool) Mage::getStoreConfig( 'onestepcheckout/general/display_tax_included' );
    }

    protected function _feePriceIncludesTax($store = null)
    {
        $config = Mage::getSingleton('klarna/tax_config');
        return $config->klarnaFeePriceIncludesTax($store);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param null $store
     * @return mixed
     */
    protected function _getVaimoKlarnaFeeForMethod($quote, $store, $force = false)
    {
        /** @var Mage_Sales_Model_Quote_Payment $payment */
        $payment = $quote->getPayment();
        $method = $payment->getMethod();
        if (!$method && !$force) {
            return 0;
        }

        $fee = 0;
        if ($force || $method==Vaimo_Klarna_Helper_Data::KLARNA_METHOD_INVOICE) {
            $fee = Mage::getStoreConfig('payment/vaimo_klarna_invoice/invoice_fee', $store);
        }
        return $fee;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param $store
     * @return int
     */
    protected function _getVaimoKlarnaFee($quote, $store, $force = false, $inBaseCurrency = true)
    {
        $localFee = 0;
        $fee = $this->_getVaimoKlarnaFeeForMethod($quote, $store, $force);
        if ($fee) {
            if (!$inBaseCurrency && $store->getCurrentCurrency() != $store->getBaseCurrency()) {
                $rate = $store->getBaseCurrency()->getRate($store->getCurrentCurrency());
                $curKlarnaFee = $fee * $rate;
            } else {
                $curKlarnaFee = $fee;
            }
            $localFee = $store->roundPrice($curKlarnaFee);
        }
        return $localFee;
    }

    /**
     * Returns the label set for fee
     *
     * @param $store
     * @return string
     */
    public function getKlarnaFeeLabel($store = NULL)
    {
        return $this->__(Mage::getStoreConfig('payment/vaimo_klarna_invoice/invoice_fee_label', $store));
    }

    /**
     * Returns the tax class for invoice fee
     *
     * @param $store
     * @return string
     */
    public function getTaxClass($store)
    {
        $config = Mage::getSingleton('klarna/tax_config');
        return $config->getKlarnaFeeTaxClass($store);
    }

    /**
     * Returns the payment fee excluding VAT
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return float
     */
    public function getVaimoKlarnaFeeExclVat($shippingAddress)
    {
        $quote = $shippingAddress->getQuote();
        $store = $quote->getStore();
        $fee = $this->_getVaimoKlarnaFee($quote, $store);
        if ($fee && $this->_feePriceIncludesTax($store)) {
            $fee -= $this->getVaimoKlarnaFeeVat($shippingAddress);
        }
        return $fee;
    }

    /**
     * Returns the payment fee tax for the payment fee
     *
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @return float
     */
    public function getVaimoKlarnaFeeVat($shippingAddress)
    {
        $paymentTax = 0;
        $quote = $shippingAddress->getQuote();
        $store = $quote->getStore();
        $fee = $this->_getVaimoKlarnaFee($quote, $store);
        if ($fee) {
            $custTaxClassId = $quote->getCustomerTaxClassId();
            $taxCalculationModel = Mage::getSingleton('tax/calculation');
            $request = $taxCalculationModel->getRateRequest($shippingAddress, $quote->getBillingAddress(), $custTaxClassId, $store);
            $paymentTaxClass = $this->getTaxClass($store);
            $rate = $taxCalculationModel->getRate($request->setProductClassId($paymentTaxClass));
            if ($rate) {
                $paymentTax = $taxCalculationModel->calcTaxAmount($fee, $rate, $this->_feePriceIncludesTax($store), true);
            }
        }
        return $paymentTax;
    }

    /**
     * Returns the payment fee tax rate
     *
     * @param Mage_Sales_Model_Order $order
     * @return float
     */
    public function getVaimoKlarnaFeeVatRate($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $store = $order->getStore();
        $custTaxClassId = $order->getCustomerTaxClassId();

        $taxCalculationModel = Mage::getSingleton('tax/calculation');
        $request = $taxCalculationModel->getRateRequest($shippingAddress, $order->getBillingAddress(), $custTaxClassId, $store);
        $paymentTaxClass = $this->getTaxClass($store);
        $rate = $taxCalculationModel->getRate($request->setProductClassId($paymentTaxClass));

        return $rate;
    }

    /**
     * Returns the payment fee including VAT, this function doesn't care about method or shipping address country
     * It's striclty for informational purpouses
     *
     * @return float
     */
    public function getVaimoKlarnaFeeInclVat($quote, $inBaseCurrency = true)
    {
        $shippingAddress = $quote->getShippingAddress();
        $store = $quote->getStore();
        $fee = $this->_getVaimoKlarnaFee($quote, $store, true, $inBaseCurrency);
        if ($fee && !$this->_feePriceIncludesTax($store)) {
            $custTaxClassId = $quote->getCustomerTaxClassId();
            $taxCalculationModel = Mage::getSingleton('tax/calculation');
            $request = $taxCalculationModel->getRateRequest($shippingAddress, $quote->getBillingAddress(), $custTaxClassId, $store);
            $paymentTaxClass = $this->getTaxClass($store);
            $rate = $taxCalculationModel->getRate($request->setProductClassId($paymentTaxClass));
            if ($rate) {
                $tax = $taxCalculationModel->calcTaxAmount($fee, $rate, $this->_feePriceIncludesTax($store), true);
                $fee += $tax;
            }

        }
        return $fee;
    }

    /*
     * The following functions shouldn't really need to exist...
     * Either I have done something wrong or the versions have changed how they work...
     *
     */

    /*
     * Add tax to grand total on invoice collect or not
     */
    public function collectInvoiceAddTaxToInvoice()
    {
        $currentVersion = Mage::getVersion();
        if ((version_compare($currentVersion, '1.10.0')>=0) && (version_compare($currentVersion, '1.12.0')<0)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Call parent of quote collect or not
     */
    public function collectQuoteRunParentFunction()
    {
        return false; // Seems the code was wrong, this function is no longer required
        $currentVersion = Mage::getVersion();
        if (version_compare($currentVersion, '1.11.0')>=0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Use extra tax in quote instead of adding to Tax, I don't know why this has to be
     * different in EE, but it clearly seems to be...
     */
    public function collectQuoteUseExtraTaxInCheckout()
    {
        return false; // Seems the code was wrong, this function is no longer required
        $currentVersion = Mage::getVersion();
        if (version_compare($currentVersion, '1.11.0')>=0) {
            return true;
        } else {
            return false;
        }
    }


// KLARNA CHECKOUT FROM NOW

    protected function _addressMatch(array $address1, array $address2)
    {
        $compareFields = array(
            'firstname',
            'lastname',
            'company',
            'street',
            'postcode',
            'city',
            'telephone',
            'country_id',
        );

        // fix street address: sometimes street is array
        if (isset($address1['street']) && is_array($address1['street'])) {
            $address1['street'] = implode("\n", $address1['street']);
        }

        if (isset($address2['street']) && is_array($address2['street'])) {
            $address2['street'] = implode("\n", $address2['street']);
        }

        foreach ($compareFields as $field) {
            $field1 = (isset($address1[$field]) ? $address1[$field] : '');
            $field2 = (isset($address2[$field]) ? $address2[$field] : '');

            if ($field1 != $field2) {
                return false;
            }
        }

        return true;
    }

    public function getCustomerAddressId($customer, $addressData)
    {
        if (!$customer) {
            return false;
        }

        $billingAddress = $customer->getDefaultBillingAddress();

        if ($this->_addressMatch($addressData, $billingAddress->getData())) {
            return $billingAddress->getEntityId();
        }

        $shippingAddress = $customer->getDefaultShippingAddress();

        if ($this->_addressMatch($addressData, $shippingAddress->getData())) {
            return $shippingAddress->getEntityId();
        }

        $additionalAddresses = $customer->getAdditionalAddresses();

        foreach ($additionalAddresses as $additionalAddress) {
            if ($this->_addressMatch($addressData, $additionalAddress->getData())) {
                return $additionalAddress->getEntityId();
            }
        }

        return false;
    }

    public function getExtraOrderAttributeCode()
    {
       return Mage::getStoreConfig(self::KLARNA_CHECKOUT_EXTRA_ORDER_ATTRIBUTE);
    }

    public function excludeCartInKlarnaCheckout()
    {
        if (Mage::getStoreConfig(self::KLARNA_CHECKOUT_ENABLE_CART_ABOVE_KCO)) {
            $res = false;
        } else {
            $res = true;
        }
        return $res;
    }

    /*
     *
     *
     */
    public function dispatchReserveInfo($order, $pno)
    {
        Mage::dispatchEvent( 'vaimo_klarna_pno_used_to_reserve', array(
            'store_id' => $order->getStoreId(),
            'order_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId(),
            'pno' => $pno
            ));
    }

    /*
     * Whenever a refund, capture, reserve or cancel is performed, we send out an event
     * This can be listened to for financial reconciliation
     *
     * @return void
     */
    public function dispatchMethodEvent($order, $eventcode, $amount, $method)
    {
        Mage::dispatchEvent( $eventcode, array(
            'store_id' => $order->getStoreId(),
            'order_id' => $order->getIncrementId(),
            'method' => $method,
            'amount' => $amount
            ));

        // Vaimo specific dispatch
        $event_name = NULL;
        switch ($eventcode) {
            case self::KLARNA_DISPATCH_RESERVED:
                $event_name = 'ic_order_success';
                break;
            case self::KLARNA_DISPATCH_CAPTURED:
                $event_name = 'ic_order_captured';
                break;
            case self::KLARNA_DISPATCH_REFUNDED:
                break;
            case self::KLARNA_DISPATCH_CANCELED:
                $event_name = 'ic_order_cancel';
                break;
        }
        if ($event_name) {
            Mage::dispatchEvent( $event_name, array("order" => $order) );
        }
    }

    public function SplitJsonStrings($json)
    {
        $q = false;
        $len = strlen($json);
        for($l=$c=$i=0; $i<$len; $i++) {
            $json[$i] == '"' && ($i>0 ? $json[$i-1] : '') != '\\' && $q = !$q;
            if (!$q && in_array($json[$i], array(" ", "\r", "\n", "\t"))){
                continue;
            }
            in_array($json[$i], array('{', '[')) && !$q && $l++;
            in_array($json[$i], array('}', ']')) && !$q && $l--;
            (isset($objects[$c]) && $objects[$c] .= $json[$i]) || $objects[$c] = $json[$i];
            $c += ($l == 0);
        }
        return $objects;
    }

    public function JsonDecode($json)
    {
        $res = array();
        $jsonArr = $this->SplitJsonStrings($json);
        if ($jsonArr) {
            foreach ($jsonArr as $jsonStr) {
                $decoded = json_decode($jsonStr, true);
                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        $res = array_merge_recursive($res, $decoded); // array_merge
                        break;
                    case JSON_ERROR_DEPTH:
                        $res = 'Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $res = 'Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $res = 'Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $res = 'Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $res = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $res = 'Unknown error';
                        break;
                }
            }
        }
       return $res;
    }

    public function getTermsUrlLink($url)
    {
        if ($url) {
            if (stristr($url, 'http')) {
                $_termsLink = '<a href="' . $url . '" target="_blank">' . $this->__('terms and conditions') . '</a>';
            } else {
                $_termsLink = '<a href="' . Mage::getSingleton('core/url')->getUrl($url) . '" target="_blank">' . $this->__('terms and conditions') . '</a>';
            }
        } else {
            $_termsLink = '<a href="#" target="_blank">' . $this->__('terms and conditions') . '</a>';
        }
        return $_termsLink;
    }

    public function getTermsUrl($url)
    {
        if ($url) {
            if (stristr($url, 'http')) {
                $_termsLink = $url;
            } else {
                $_termsLink = Mage::getSingleton('core/url')->getUrl($url);
            }
        } else {
            $_termsLink = '';
        }
        return $_termsLink;
    }

    /**
     * Sets the function name, which is used in logs. This is set in each class construct
     *
     * @param string $functionName
     *
     * @return void
     */
    public function setFunctionNameForLog($functionName)
    {
        if (in_array($functionName, self::$_logFunctionNameArray)) {
            return;
        }
        // When klarna/klarna is loaded, it should not add a tag for klarna if klarnacheckout is called initially
        if ($functionName=='klarna' && in_array('klarnacheckout', self::$_logFunctionNameArray)) {
            return;
        }
        self::$_logFunctionNameArray[] = $functionName;
    }

    /**
     * Returns the function name set by the constructors in each class
     *
     * @return string
     */
    public function getFunctionNameForLog()
    {
        return implode('-', self::$_logFunctionNameArray);
    }

    public function getKlarnaVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->Vaimo_Klarna->version;
    }

    public function logKlarnaActionStart($method, $name)
    {
        $this->setFunctionNameForLog($method);
        $this->setFunctionNameForLog($name);
        $this->logKlarnaApi(self::KLARNA_LOG_START_TAG);
    }

    public function logKlarnaActionEnd()
    {
        $this->logKlarnaApi(self::KLARNA_LOG_END_TAG);
        array_pop(self::$_logFunctionNameArray); // name
        array_pop(self::$_logFunctionNameArray); // method
    }

    public function logKlarnaClearFunctionName()
    {
        self::$_logFunctionNameArray = array();
    }

    public function logKlarnaCheckoutFunctionStart($checkoutId, $name)
    {
        $this->setFunctionNameForLog($name);
        $this->logKlarnaDebug('logKlarnaCheckoutFunctionStart', null, $checkoutId);
        if ($checkoutId) {
            $this->logKlarnaApi('Checkout ID ' . $checkoutId);
        } else {
            $this->logKlarnaApi('Checkout ID NULL');
        }
    }

    public function logKlarnaCheckoutFunctionEnd()
    {
        $this->logKlarnaApi('Complete');
        array_pop(self::$_logFunctionNameArray);
    }

    /**
     * Log function that does the writing to log file
     *
     * @param string $filename  What file to write to, will be placed in site/var/log/ folder
     * @param string $msg       Text to log
     *
     * @return void
     */
    protected function _log($filename, $msg)
    {
        Mage::log('PID(' . getmypid() . '): ' . $this->getKlarnaVersion() . ' ' . $this->getFunctionNameForLog() . ': ' . $msg, null, $filename, true);
    }

    /**
     * Log function that does the writing to log file
     *
     * @param string $filename  What file to write to, will be placed in site/var/klarna/ folder
     * @param string $msg       Text to log
     *
     * @return void
     */
    protected function _logAlways($filename, $msg)
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS;
        $logFile = $logDir . $filename;

        try {
            if (!is_dir($logDir)) {
                mkdir($logDir);
                chmod($logDir, 0777);
            }
            if ( file_exists($logFile) ){
                $fp = fopen( $logFile, "a" );
            } else {
                $fp = fopen( $logFile, "w" );
            }
            if ( !$fp ) return null;
            fwrite( $fp, date("Y/m/d H:i:s") . ' ' . $this->getFunctionNameForLog() . ': ' . $msg . "\n" );
            fclose( $fp );
        } catch( Exception $e ) {
            return;
        }
    }

    /**
     * Log function used for various debug log information, array is optional
     *
     * @param string $info  Header of what is being logged
     * @param array $arr    The array to be logged
     *
     * @return void
     */
    protected function _logToDatabase($info, $arr = NULL, $tag = null)
    {
        if (is_null($tag)) {
            $tag = self::$_klarnaCheckoutId;
        }
        if ($arr) {
            $data = is_string($arr) ? $arr : json_encode($arr);
        } else {
            $data = null;
        }
        $log = Mage::getModel('klarna/log')
            ->setProcess(getmypid())
            ->setFunction($this->getFunctionNameForLog())
            ->setTag($tag)
            ->setMessage($info)
            ->setExtra($data)
            ->save();
        /*
        if ($arr) {
            Mage::getModel('klarna/log_data')
                ->setParent($log->getEntityId())
                ->setExtra($data)
                ->save();
        }
        */
    }
    /**
     * Log function that logs all Klarna API calls and replies, this to see what functions are called and what reply they get
     *
     * @param string $comment Text to log
     *
     * @return void
     */
    public function logKlarnaApi($comment)
    {
        //$this->_log('klarnaapi.log', $comment);
        $level = $this->getLogLevel();
        if ($level == self::KLARNA_LOG_LEVEL_NONE) {
            return;
        }
        $this->_logToDatabase($comment);
    }

    /**
     * Log function used for various debug log information, array is optional
     *
     * @param string $info  Header of what is being logged
     * @param array $arr    The array to be logged
     *
     * @return void
     */
    public function logKlarnaDebug($info, $arr = NULL, $tag = null)
    {
        $level = $this->getLogLevel();
        if ($level == self::KLARNA_LOG_LEVEL_NONE || $level == self::KLARNA_LOG_LEVEL_MINMIAL) {
            return;
        }
        if ($level == self::KLARNA_LOG_LEVEL_MODERATE) {
            $arr = null;
        }
        $this->_logToDatabase($info, $arr, $tag);
    }

    /**
     * Will log to debug, but also include a backtrace
     *
     * @param string $info  Header of what is being logged
     * @param array $arr    The array to be logged
     *
     * @return void
     */
    public function logKlarnaDebugBT($info, $arr = NULL, $tag = null)
    {
        $this->logKlarnaDebug($info, $arr, $tag);
        $bt = mageDebugBacktrace(true, true, true);
        $this->logKlarnaDebug('Backtrace', $bt);
    }

    /**
     * Not sure why I named it like this, it should be called logKlarnaDebug, kept this for compability...
     *
     * @param $info
     * @param null $arr
     */
    public function logDebugInfo($info, $arr = NULL, $tag = null)
    {
        $this->logKlarnaDebug($info, $arr, $tag);
    }

    protected function _logMagentoException($e)
    {
        Mage::logException($e);
    }

    /**
     * If there is an exception, this log function should be used
     * This is mainly meant for exceptions concerning klarna API calls, but can be used for any exception
     *
     * Logic for log level is this:
     * Magento log is always updated, but never with special status code responses from Klarna
     * Klarna special statu ressponses are logged to errorlog file only if full log level is selected
     * In case of moderate and minimal, those special status errors, are logged to DB (when it can)
     * If level is none, no exceptions are logged
     *
     *
     * @param Exception $e
     *
     * @return void
     */
    public function logKlarnaException($e)
    {
        $level = $this->getLogLevel();
        $logException = $this->_logThisException($e);
        if ($logException) {
            $this->_logMagentoException($e);
        }
        $errstr = 'Exception:';
        if ($e->getCode()) $errstr = $errstr . ' Code: ' . $e->getCode();
        if ($e->getMessage()) $errstr = $errstr . ' Message: ' . $e->getMessage();
        if ($e->getLine()) $errstr = $errstr . ' Row: ' . $e->getLine();
        if ($e->getFile()) $errstr = $errstr . ' File: ' . $e->getFile();
        switch ($level) {
            case self::KLARNA_LOG_LEVEL_FULL:
                $logException = true;
                break;
            case self::KLARNA_LOG_LEVEL_MODERATE:
                if (!$logException) {
                    // If there is a resulting exception, this write will be rolled back
                    $this->_logToDatabase($errstr);
                }
                break;
            case self::KLARNA_LOG_LEVEL_NONE:
                $logException = false;
                break;
        }
        if (!$logException) {
            return;
        }
        $this->_logAlways('klarnaerror.log', $errstr);
    }

    public function getLogLevel()
    {
        return Mage::getStoreConfig('dev/vaimo_klarna_debug/vaimo_klarna_log_level');
    }

    /**
     * @param Exception $e
     *
     * @return boolean
     */
    protected function _logThisException($e)
    {
        $res = true;
        if ($e->getCode()) {
            $errSkipRange = array(
                // KPM
                array(2101, 2110),
                array(2201, 2206),
                array(2301, 2307),
                array(2401, 2406),
                array(2501, 2503),
                array(2999, 2999),
                array(3101, 3111),
                array(3201, 3221),
                array(3301, 3305),
                array(3999, 3999),
                array(6101, 6106),
                array(6999, 6999),
                array(7999, 7999),
                array(8101, 8115),
                array(8999, 8999),
                array(9101, 9131),
                array(9191, 9191),
                array(9291, 9241),
                // KCO
                array(400, 401),
                array(402, 406),
                array(415, 415),

            );
            foreach ($errSkipRange as $range) {
                if ($e->getCode()>=$range[0] && $e->getCode()<=$range[1]) {
                    $res = false;
                    break;
                }
            }
        }
        return $res;
    }

    public function getDefaultCountry($store = NULL)
    {
/* For shipping this should be called...
        $taxCalculationModel = Mage::getSingleton('tax/calculation');
        $request = $taxCalculationModel->getRateRequest();
        x = $request->getCountryId();
        y = $request->getRegionId();
        z = $request->getPostcode();
*/
        if (version_compare(Mage::getVersion(), '1.6.2', '>=')) {
            $res = Mage::helper('core')->getDefaultCountry($store);
        } else {
            $res = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_COUNTRY, $store);
        }
        return $res;
    }

    public function isEnterpriseAndHasClass($class = null)
    {
        $res = false;
        try {
            $isEE = self::isEnterprise();

            if ($class && $isEE) {
                if (class_exists($class, true)) {
                    $res = true;
                }
            }
        } catch (Exception $e) {
        }
        return $res;
    }

    /**
     * Escape quotes inside html attributes
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     */
    public function quoteEscape($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }

    public function findQuote($klarna_id)
    {
        if (!$klarna_id) {
            Mage::helper('klarna')->logKlarnaApi('findQuote no klarna_id provided!');
            return null;
        }
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $read */
        $read = $resource->getConnection('core_read');
        /** @var Varien_Db_Select $select */
        $select = $read->select()->from($resource->getTableName('sales/quote'), Array('entity_id', 'store_id'))
            ->where('klarna_checkout_id=?', $klarna_id);
        $r = $read->fetchAll($select);
        if (count($r) < 1) {
            Mage::helper('klarna')->logKlarnaApi('findQuote no checkout quote found! ' . $klarna_id);
            return null;
        }
        else if (count($r) > 1) {
            Mage::helper('klarna')->logKlarnaApi('findQuote more than one quote found! ' . $klarna_id);
        }
        $r = $r[0];
        Mage::app()->setCurrentStore($r['store_id']);
        $quote = Mage::getModel('sales/quote')
            ->setStoreId($r['store_id'])
            ->load($r['entity_id']);

        $quote->setTotalsCollectedFlag(true);
        return $quote;
    }

    /**
     * Check if a product is a dynamic bundle product and reset a price.
     *
     * @param $item // Might not be Mage_Sales_Model_Quote_Item...
     * @param  Mage_Catalog_Model_Product|null $product
     * @return bool
     */
    public function checkBundles(&$item, $product = null)
    {
        $res = false;
        if (!$product) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $productType = $item->getProductType();
        if ($productType===NULL) {
            if ($item->getOrderItemId()) {
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $productType = $orderItem->getProductType();
            }
        }
        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
            && $product->getPriceType() == Mage_Bundle_Model_Product_Price::PRICE_TYPE_DYNAMIC) {
            $res = true;
        }
        return $res;
    }

    public static function isEnterprise()
    {
        if (!isset(self::$isEnterprise)) {
            if (method_exists('Mage', 'getEdition')) {
                self::$isEnterprise = Mage::getEdition() == Mage::EDITION_ENTERPRISE;
            } else {
                self::$isEnterprise = (boolean) Mage::getConfig()->getModuleConfig('Enterprise_Enterprise');
            }
        }

        return self::$isEnterprise;
    }


    protected function _isAdminUserLoggedIn()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }


    public function prepareVaimoKlarnaFeeRefund(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if (!$this->_isAdminUserLoggedIn() || $creditmemo->hasVaimoKlarnaFeeRefund()) {
            return $this;
        }

        $data = $this->_getRequest()->getParam('creditmemo', array());
        if (!isset($data['vaimo_klarna_fee_refund'])) {
            return $this;
        }
        $refundAmount = empty($data['vaimo_klarna_fee_refund']) ? 0 : $data['vaimo_klarna_fee_refund'];

        $store = $creditmemo->getOrder()->getStore();
        $baseRefundAmount = $store->convertPrice($refundAmount, false);

        $creditmemo->setVaimoKlarnaFeeRefund($refundAmount)
            ->setVaimoKlarnaBaseFeeRefund($baseRefundAmount);

        return $this;
    }

    public function checkPaymentMethod($quote, $logf = false)
    {
        if ($quote->getPayment()->getMethod() != Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT) {
            if ($logf) {
                $this->logKlarnaDebug('_createTheOrder quote ' . $quote->getId() .
                    ' not proper method (' . $quote->getPayment()->getMethod() .
                    '), changing to ' .
                    Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            }
            if ($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod(Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            } else {
                $quote->getShippingAddress()->setPaymentMethod(Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            }
            $quote->getPayment()->setMethod(Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            return true;
        }
        return false;
    }

    /**
     * Check if exception is triggered by local XML-RPC library
     *
     * @param  Exception $e
     * @return boolean
     */
    public function isXmlRpcException(Exception $e)
    {
        if (empty($GLOBALS['xmlrpcerr']) || empty($GLOBALS['xmlrpcstr'])) {
            return false;
        }

        $xmlRpcErrorCode = array_search($e->getCode(), $GLOBALS['xmlrpcerr']);
        return ($xmlRpcErrorCode !== false)
            && (strpos($e->getMessage(), $GLOBALS['xmlrpcstr'][$xmlRpcErrorCode]) === 0);
    }

    public function updateKlarnacheckoutHistory($checkoutId, $message = null, $quoteId = null, $orderId = null, $reservationId = null)
    {
        $history = Mage::getModel('klarna/klarnacheckout_history')->loadByIdAndQuote($checkoutId, $quoteId);
        $history->updateKlarnacheckoutHistory($checkoutId, $message, $quoteId, $orderId, $reservationId);
    }

    public function getProductReference($sku, $additionalData)
    {
        $reference = $sku;
        $additionalData = unserialize($additionalData);
        if ($additionalData) {
            if (isset($additionalData['klarna_reference'])) {
                $reference = $additionalData['klarna_reference'];
            }
        }
        return $reference;
    }

}
