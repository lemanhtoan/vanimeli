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

class Vaimo_Klarna_Model_Api_Rest extends Vaimo_Klarna_Model_Api_Abstract
{
    protected $_url = NULL;

    protected $_curlHeaders;
    protected $_klarnaOrder = null;
    protected $_useKlarnaOrderSessionCache = false;
    protected $_request = NULL;
    protected $_apiVersion = NULL;

    /**
     * Would have been solved better with two separate files, inheriting Rest.php
     * But, this works as well
     *
     * @param $apiVersion
     * @return $this
     */
    public function setApiVersion($apiVersion)
    {
        $this->_apiVersion = $apiVersion;
        return $this;
    }

    public function getApiVersion()
    {
        return ($this->_apiVersion);
    }

    protected function _isUSA()
    {
        return $this->_apiVersion == Vaimo_Klarna_Helper_Data::KLARNA_KCO_API_VERSION_USA;
    }

    public function init($klarnaSetup)
    {
        $this->_klarnaSetup = $klarnaSetup;
        if ($this->_klarnaSetup->getHost() == 'BETA') {
            if ($this->_isUSA()) {
                $this->_url = 'https://api-na.playground.klarna.com';
            } else {
                $this->_url = 'https://api.playground.klarna.com';
            }
        } else {
            if ($this->_isUSA()) {
                $this->_url = 'https://api-na.klarna.com';
            } else {
                $this->_url = 'https://api.klarna.com';
            }
        }
    }

    protected function _getUrl()
    {
        return $this->_url;
    }

    public function curlHeader($ch, $str)
    {
//        Mage::helper('klarna')->logDebugInfo('curlHeader rest str = ' . $str);
        if (strpos($str, ': ') !== false) {
            list($key, $value) = explode(': ', $str, 2);
            $this->_curlHeaders[$key] = trim($value);
        }

        return strlen($str);
    }

    protected function _getLocationOrderId($location = NULL)
    {
        if ($location) {
            $res = $location;
        } else {
            $res = $this->_klarnaOrder->getLocation();
        }
        $arr = explode('/', $res);
        if (is_array($arr)) {
            $res = $arr[sizeof($arr)-1];
        }
        return $res;
    }

    protected function _getAddressData($useTransport = false, $type = Mage_Sales_Model_Quote_Address::TYPE_BILLING)
    {
        if (!$this->_getTransport()->getConfigData('auto_prefil')) return NULL;
        $result = NULL;
        $address = NULL;

        /** @var $session Mage_Customer_Model_Session */
        if ($useTransport) {
            $result = array();
            $result['email'] = $this->_getTransport()->getQuote()->getCustomerEmail();
            if ($type==Mage_Sales_Model_Quote_Address::TYPE_BILLING) {
                $address = $this->_getTransport()->getBillingAddress();
            } else {
                $address = $this->_getTransport()->getShippingAddress();
            }
        } else {
            $session = Mage::getSingleton('customer/session');
            if ($session->isLoggedIn()) {
                if ($type==Mage_Sales_Model_Quote_Address::TYPE_BILLING) {
                    $address = $session->getCustomer()->getPrimaryBillingAddress();
                } else {
                    $address = $session->getCustomer()->getPrimaryShippingAddress();
                }
                $result = array();
                if ($session->getCustomer()->getEmail()) {
                    $result['email'] = $session->getCustomer()->getEmail();
                }
            }
        }
        if ($address) {
            if ($address->getPostcode()) {
                $result['postal_code'] = $address->getPostcode();
            }
            if ($address->getFirstname()) {
                $result['given_name'] = $address->getFirstname();
            }
            if ($address->getLastname()) {
                $result['family_name'] = $address->getLastname();
            }
            if ($address->getStreet(1)) {
                $result['street_address'] = $address->getStreet(1);
            }
            if ($address->getCity()) {
                $result['city'] = $address->getCity();
            }
            if ($address->getTelephone()) {
                $result['phone'] = $address->getTelephone();
            }
            if ($address->getCountryId()) {
                $result['country'] = $address->getCountryId();
            }
            if ($address->getRegionCode()) {
                $result['region'] = $address->getRegionCode();
            }
        }
        return $result;
    }

    protected function _getCustomerData()
    {
        if (!$this->_getTransport()->getConfigData('auto_prefil')) return NULL;

        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            if ($this->_getTransport()->needDateOfBirth()) {
                if ($session->getCustomer()->getDob()) {
                    $result = array(
                        'date_of_birth' => substr($session->getCustomer()->getDob(),0,10),
                    );
                    return $result;
                }
            }
        }

        return NULL;
    }

    /**
     * Get active Klarna checkout id
     *
     * @return string
     */
    protected function _getKlarnaCheckoutId()
    {
        return $this->_getQuote()->getKlarnaCheckoutId();
    }

    /**
     * Put Klarna checkout id to quote
     *
     * @param $checkoutId string
     */
    protected function _setKlarnaCheckoutId($checkoutId)
    {
        $quote = $this->_getQuote();
        $message = null;

        if ($quote->getId() && $quote->getKlarnaCheckoutId() != $checkoutId) {
            if ($quote->getKlarnaCheckoutId()) {
                if (!$quote->getIsActive()) {
                    Mage::throwException(Mage::helper('klarna')->__('Attempting to change checkout id on closed quote') . ' ' . $quote->getId());
                } else {
                    $message = 'POTENTIAL ERROR. _setKlarnaCheckoutId: Old checkout id: ' . $quote->getKlarnaCheckoutId();
                    Mage::helper('klarna')->logDebugInfo($message, null, $checkoutId);
                }
            }
            Mage::helper('klarna')->logDebugInfo('SET checkout id rest: ' . $checkoutId);
            Mage::helper('klarna')->logDebugInfo('Quote Id rest: ' . $quote->getId());
            $quote->setKlarnaCheckoutId($checkoutId);
            $quote->save();
            Mage::helper('klarna')->updateKlarnacheckoutHistory($checkoutId, $message, $quote->getId());
        }

        Mage::getSingleton('checkout/session')->setKlarnaCheckoutId($checkoutId);
    }

    protected function _getCartItems($useTransport = false)
    {
        if ($useTransport) {
            $quote = $this->_getTransport()->getQuote();
        } else {
            $quote = $this->_getQuote();
        }
        $items = array();
        $calculator = Mage::getSingleton('tax/calculation');

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getTaxPercent() > 0) {
                $taxRate = $quoteItem->getTaxPercent();
            } elseif ($quoteItem->getRowTotal() != 0) {
                $taxRate = $quoteItem->getTaxAmount() / $quoteItem->getRowTotal() * 100;
            } else {
                $taxRate = 0;
            }
            $taxAmount = $calculator->calcTaxAmount($quoteItem->getRowTotalInclTax(), $taxRate, true);

            if ($this->_isUSA()) {
//                $unitPrice = $quoteItem->getPrice();
                $totalAmount = $quoteItem->getRowTotalInclTax() - $taxAmount;
                if ($quoteItem->getQty() != 0) {
                    $unitPrice = $totalAmount / $quoteItem->getQty();
                } else {
                    $unitPrice = 0;
                }
                $taxRate = 0;
                $taxAmount = 0;
            } else {
                $unitPrice = $quoteItem->getPriceInclTax();
                $totalAmount = $quoteItem->getRowTotalInclTax();
            }

            $reference = $quoteItem->getSku();
            if ($this->_skuNotUnique($quote->getAllVisibleItems(), $quoteItem)) {
                $reference = substr($quoteItem->getSku(), 0, 53) . ':' . str_pad($quoteItem->getId(), 10, '0', STR_PAD_LEFT);
                $additionalData = unserialize($quoteItem->getAdditionalData());
                if (!$additionalData) {
                    $additionalData = array();
                }
                $additionalData['klarna_reference'] = $reference;
                $quoteItem->setAdditionalData(serialize($additionalData));
                $quoteItem->save();
            }

            $items[] = array(
                'type' => 'physical',
                'reference' => $reference,
                'name' => $quoteItem->getName(),
                'quantity' => round($quoteItem->getQty()),
                'quantity_unit' => 'pcs',
                'unit_price' => round($unitPrice * 100),
                'discount_rate' => 0,
//                'discount_rate' => round($quoteItem->getDiscountPercent() * 100),
                'tax_rate' => round($taxRate * 100),
                'total_amount' => round($totalAmount * 100),
                'total_tax_amount' => round($taxAmount * 100),
            );
        }

        foreach ($quote->getTotals() as $key => $total) {
            switch ($key) {
                case 'shipping':
                    if ($total->getValue() != 0) {
                        $amount_incl_tax = $total->getAddress()->getShippingInclTax();
                        $amount = $total->getAddress()->getShippingAmount();
                        $taxAmount = $total->getAddress()->getShippingTaxAmount();
                        $hiddenTaxAmount = $total->getAddress()->getShippingHiddenTaxAmount();
                        //if (Mage::helper('klarna')->isShippingInclTax($quote->getStoreId())) {
                        if (($amount_incl_tax > 0) && (round($amount_incl_tax, 2) == round($amount, 2))) {
                            $amount = $amount - $taxAmount - $hiddenTaxAmount;
                        }
                        if ($amount == 0) {
                            continue;
                        }
                        $taxRate = ($taxAmount + $hiddenTaxAmount) / $amount * 100;
                        if ($this->_isUSA()) {
                            $unitPrice = $amount;
                            $totalAmount = $amount;
                            $taxRate = 0;
                            $taxAmount = 0;
                        } else {
                            $unitPrice = $amount + $taxAmount + $hiddenTaxAmount;
                            $totalAmount = $amount + $taxAmount + $hiddenTaxAmount;
                        }
                        $items[] = array(
                            'type' => 'shipping_fee',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($unitPrice * 100),
                            'discount_rate' => 0,
                            'tax_rate' => round($taxRate * 100),
                            'total_amount' => round($totalAmount * 100),
                            'total_tax_amount' => round($taxAmount * 100),
                        );
                    }
                    break;
                case 'discount':
                    if ($total->getValue() != 0) {
                        // ok, this is a bit shaky here, i know...
                        // but i don't have discount tax anywhere but in hidden_tax_amount field :(
                        // and I have to send discount also with tax rate to klarna
                        // otherwise the total tax wouldn't match
                        $taxAmount = $total->getAddress()->getHiddenTaxAmount();
                        $amount = -$total->getAddress()->getDiscountAmount() - $taxAmount;
                        if ($amount == 0) {
                            continue;
                        }
                        $taxRate = $taxAmount / $amount * 100;
                        if ($this->_isUSA()) {
                            $unitPrice = $amount;
                            $totalAmount = $amount;
                            $taxRate = 0;
                            $taxAmount = 0;
                        } else {
                            $unitPrice = $amount + $taxAmount;
                            $totalAmount = $amount + $taxAmount;
                        }
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => -round($unitPrice * 100),
                            'discount_rate' => 0,
                            'tax_rate' => round($taxRate * 100),
                            'total_amount' => -round($totalAmount * 100),
                            'total_tax_amount' => -round($taxAmount * 100),
                        );
                    }
                    break;
                case 'giftcardaccount':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                            'total_amount' => round($total->getValue() * 100),
                            'total_tax_amount' => 0,
                        );
                    }
                    break;
                case 'ugiftcert':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => -round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                            'total_amount' => round($total->getValue() * 100),
                            'total_tax_amount' => 0,
                        );
                    }
                    break;
                case 'reward':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                            'total_amount' => round($total->getValue() * 100),
                            'total_tax_amount' => 0,
                        );
                    }
                    break;
                case 'customerbalance':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => $total->getCode(),
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                            'total_amount' => round($total->getValue() * 100),
                            'total_tax_amount' => 0,
                        );
                    }
                    break;
            }
        }
        if ($this->_isUSA()) {
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['total_amount'];
            }
            $totalTax = (($quote->getGrandTotal() * 100) - $totalAmount) / 100;

            $items[] = array(
                'type' => 'sales_tax',
                'reference' => Mage::helper('klarna')->__('Sales Tax'),
                'name' => Mage::helper('klarna')->__('Sales Tax'),
                'quantity' => 1,
                'unit_price' => round($totalTax * 100),
                'discount_rate' => 0,
                'tax_rate' => 0,
                'total_amount' => round($totalTax * 100),
                'total_tax_amount' => 0,
            );
        }

        return $items;
    }

    protected function _getCreateRequest()
    {
        $create = array();
        $create['purchase_country'] = Mage::helper('klarna')->getDefaultCountry();
        $create['purchase_currency'] = $this->_getQuote()->getQuoteCurrencyCode();
        $create['locale'] = str_replace('_', '-', $this->_klarnaSetup->getLocaleCode());
        $create['gui']['layout'] = $this->_isMobile() ? 'mobile' : 'desktop';
        if ($this->_getTransport()->getConfigData('enable_auto_focus')==false) {
            $create['gui']['options'] = array('disable_autofocus');
        }
        if ($this->_getTransport()->AllowSeparateAddress()) {
            $create['options']['allow_separate_shipping_address'] = true;
        }
        if ($this->_getTransport()->getConfigData('force_phonenumber')) {
            $create['options']['phone_mandatory'] = true;
        }
        if ($this->_getTransport()->getConfigData('packstation_enabled')) {
            $create['options']['packstation_enabled'] = true;
        }

        $this->_addUserDefinedVariables($create);

        if ($data = $this->_getAddressData()) {
            $create['billing_address'] = $data;
        }

        if ($data = $this->_getCustomerData()) {
            $create['customer'] = $data;
        }

        $create['order_amount'] = round($this->_getQuote()->getGrandTotal() * 100);
        $create['order_tax_amount'] = 0;
        $create['order_lines'] = $this->_getCartItems();

        foreach ($create['order_lines'] as $line) {
            if ($this->_isUSA()) {
                if (isset($line['type']) && $line['type']=='sales_tax') {
                    $create['order_tax_amount'] += $line['total_amount'];
                }
            } else {
                $create['order_tax_amount'] += $line['total_tax_amount'];
            }
        }

        if ($this->_getTransport()->getConfigData('other_countries')) {
            $shippingCountries = $this->_getTransport()->getConfigData('shipping_countries');
            if ($shippingCountries) {
                $create['shipping_countries'] = explode(',', $shippingCountries);
            }
        }

        if ($this->_getTransport()->getConfigData('shipping_options')) {
            $shippingOptions = array();
            $address = $this->_getQuote()->getShippingAddress();
            $originalShippingMethod = $address->getShippingMethod();
            $methods = Mage::getModel('checkout/cart_shipping_api')->getShippingMethodsList($this->_getQuote()->getId());
            foreach ($methods as $method) {
                $address->setShippingMethod($method['code']);
                $this->_getQuote()->setTotalsCollectedFlag(false)->collectTotals();
                if ($address->getShippingAmount()>0) {
                    $taxRate = ($address->getShippingAmount() + $address->getShippingTaxAmount()) / $address->getShippingAmount() * 100;
                } else {
                    $taxRate = 0;
                }
                if ($method['code']==$originalShippingMethod) {
                    $preSelected = true;
                } else {
                    $preSelected = false;
                }
                $shippingOptions[] = array(
                    'id' => $method['code'],
                    'name' => $method['method_title'],
                    'description' => $method['carrier_title'],
                    'price' => (int)($address->getShippingAmount() + $address->getShippingTaxAmount()) * 100,
                    'tax_amount' => (int)$address->getShippingTaxAmount() * 100,
                    'tax_rate' => (int)($taxRate * 100) * 100,
                    'preselected' => $preSelected,
                );
            }
            $address->setShippingMethod($originalShippingMethod);
            $this->_getQuote()->setTotalsCollectedFlag(false)->collectTotals();
            $create['shipping_options'] = $shippingOptions;
            $create['selected_shipping_option'] = array(
                'id' => $address->getShippingMethod(),
                'name' => $address->getShippingDescription(),
            );
        }

        $pushUrl = Mage::getUrl('checkout/klarna/push?klarna_order={checkout.order.id}', array('_nosid' => true));
        if (substr($pushUrl, -1, 1) == '/') {
            $pushUrl = substr($pushUrl, 0, strlen($pushUrl) - 1);
        }

        $create['merchant_urls']['terms'] = Mage::helper('klarna')->getTermsUrl($this->_klarnaSetup->getTermsUrl());
        if ($this->_getTransport()->getConfigData('recreate_cart_on_failed_validate')) {
            $create['merchant_urls']['checkout'] = Mage::getUrl('checkout/klarna', array('_nosid' => true, 'quote_id' => $this->_getQuote()->getId()));
        } else {
            $create['merchant_urls']['checkout'] = Mage::getUrl('checkout/klarna');
        }
        $create['merchant_urls']['confirmation'] = Mage::getUrl('checkout/klarna/success');
        $create['merchant_urls']['push'] = $pushUrl;

        if ($this->_getTransport()->getConfigData('enable_validation')) {
            $validateUrl = Mage::getUrl('checkout/klarna/validate?klarna_order={checkout.order.id}', array('_nosid' => true));
            if (substr($validateUrl, -1, 1) == '/') {
                $validateUrl = substr($validateUrl, 0, strlen($validateUrl) - 1);
            }
            if (substr($validateUrl, 0, 5) == 'https') {
                $create['merchant_urls']['validation'] = $validateUrl;
            }
        }
        if ($this->_getTransport()->getConfigData('enable_postcode_update')) {
            $validateUrl = Mage::getUrl('checkout/klarna/taxshippingupdate?klarna_order={checkout.order.id}', array('_nosid' => true));
            if (substr($validateUrl, -1, 1) == '/') {
                $validateUrl = substr($validateUrl, 0, strlen($validateUrl) - 1);
            }
            if (substr($validateUrl, 0, 5) == 'https') {
                $create['merchant_urls']['shipping_address_update'] = $validateUrl;
            }
        }

        Mage::helper('klarna')->logDebugInfo('_getCreateRequest rest', $create);

        $request = new Varien_Object($create);
        Mage::dispatchEvent('klarnacheckout_get_create_request', array('request' => $request, 'api_version' => $this->getApiVersion()));

        return $request->getData();
    }

    public function prepareTaxAndShippingReply()
    {
        $update = array();

        if ($data = $this->_getAddressData(true, Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)) {
            $update['shipping_address'] = $data;
        }

        $update['order_amount'] = round($this->_getTransport()->getQuote()->getGrandTotal() * 100);
        $update['order_tax_amount'] = 0;
        $update['order_lines'] = $this->_getCartItems(true);

        foreach ($update['order_lines'] as $line) {
            if ($this->_isUSA()) {
                if (isset($line['type']) && $line['type']=='sales_tax') {
                    $update['order_tax_amount'] += $line['total_amount'];
                }
            } else {
                $update['order_tax_amount'] += $line['total_tax_amount'];
            }
        }

        $request = new Varien_Object($update);
        Mage::dispatchEvent('klarnacheckout_get_update_request', array('request' => $request, 'api_version' => $this->getApiVersion()));

        return $request->getData();
    }

    protected function _createOrder()
    {
        $this->_curlHeaders = array();

        $request = $this->_getCreateRequest();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/checkout/v3/orders');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_createOrder rest response = ' . $response . ' status = ' . $status);

        if ($status != 201) {
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException('Error creating order: ' . $status);
        }

        if (isset($this->_curlHeaders['Location'])) {
            return $this->_getLocationOrderId($this->_curlHeaders['Location']);
        }

        return false;
    }

    protected function _fetchOrder($checkoutId, $silentFail = false)
    {
        $ch = curl_init();
        $location = $this->_getUrl() . '/checkout/v3/orders/' . $checkoutId;
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_fetchOrder rest response = ' . $response . ' status = ' . $status);

        if ($status != 200) {
            $this->_updateLastError($response, array('status' => $status));
            if ($silentFail) return;
            Mage::throwException('Error fetching order: ' . $status);
        }

        $this->_klarnaOrder = new Varien_Object(json_decode($response, true));
        $this->_klarnaOrder->setLocation($location);
    }

    protected function _updateOrder($checkoutId)
    {
        $ch = curl_init();
        $location = $this->_getUrl() . '/checkout/v3/orders/' . $checkoutId;
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        $request = json_encode($this->_getCreateRequest());
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_updateOrder rest response = ' . $response . ' status = ' . $status);

        if ($status != 200) {
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException('Error updating order: ' . $status);
        }

        $this->_klarnaOrder = new Varien_Object(json_decode($response, true));
        $this->_klarnaOrder->setLocation($location);
    }

    protected function _fetchCreatedOrder($checkoutId, $silentFail = false)
    {
        $ch = curl_init();
        $location = $this->_getUrl() . '/ordermanagement/v1/orders/' . $checkoutId;
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_fetchCreatedOrder rest response = ' . $response . ' status = ' . $status);

        if ($status != 200) {
            $this->_updateLastError($response, array('status' => $status));
            if ($silentFail) return;
            Mage::throwException('Error fetching order: ' . $status);
        }

        $this->_klarnaOrder = new Varien_Object(json_decode($response, true));
        $this->_klarnaOrder->setLocation($location);
    }

    protected function _acknowledgeOrder($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/acknowledge');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_acknowledgeOrder rest response = ' . $response . ' status = ' . $status);
    }

    protected function _updateMerchantReferences($orderId, $reference1, $reference2 = null)
    {
        $request = array(
            'merchant_reference1' => $reference1
        );

        if ($reference2) {
            $request['merchant_reference2'] = $reference2;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/merchant-references');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('_updateMerchantReferences rest response = ' . $response . ' status = ' . $status);
    }

    protected function _buildMessageFromResponse($initialMessage, $response)
    {
        $message = $initialMessage;
        $responseArr = json_decode($response, true);
        if (isset($responseArr['error_code'])) {
            $message .= '; Code: ' . $responseArr['error_code'];
        }
        if (isset($responseArr['error_messages']) && is_array($responseArr['error_messages'])) {
            foreach ($responseArr['error_messages'] as $value) {
                $message .= '; ' . $value;
            }
        }
        return $message;
    }
    
    public function capture($orderId, $amount, $sendEmailf)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/captures');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->_request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('capture rest response = ' . $response . ' status = ' . $status);

        if ($status != 201) {
            $message = $this->_buildMessageFromResponse('Error capturing order: ' . $status, $response);
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException($message);
        }

        $capture_id = "";
        if (isset($this->_curlHeaders['Location'])) {
            $location = $this->_curlHeaders['Location'];
            $parts = explode('/', $location);
            $prev_part = "";
            foreach ($parts as $part) {
                if ($prev_part=='captures') {
                    $capture_id = $part;
                    break;
                }
                $prev_part = $part;
            }
        }

        $res = array(
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_STATUS => $status,
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_TRANSACTION_ID => $orderId,
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_KCO_CAPTURE_ID => $capture_id,
        );
        return $res;
    }

    public function refund($amount, $reservation_no)
    {
        $tmp = explode('/', $reservation_no);
        if (sizeof($tmp)>0) {
            $orderId = $tmp[0];
        } else {
            $orderId = $reservation_no;
        }
        $this->_setGoodsListRefund($amount);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/refunds');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->_request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('refund rest response = ' . $response . ' status = ' . $status);

        if ($status != 204) {
            $message = $this->_buildMessageFromResponse('Error refunding order: ' . $status, $response);
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException($message);
        }
        $res = array(
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_STATUS => $status,
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_TRANSACTION_ID => $reservation_no,
        );
        return $res;
    }

    /**
     * Cancel an authorized order. For a cancellation to be successful, there must be no captures on the order.
     * The authorized amount will be released and no further updates to the order will be allowed.
     *
     * @param $orderId
     */
    public function cancel($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/cancel');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('cancel rest response = ' . $response . ' status = ' . $status);

        if ($status != 204) {
            $message = $this->_buildMessageFromResponse('Error canceling order: ' . $status, $response);
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException($message);
        }
        $res = array(
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_STATUS => $status,
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_TRANSACTION_ID => $orderId,
        );
        return $res;
    }

    /**
     * Signal that there is no intention to perform further captures.
     *
     * @param $orderId
     */
    public function release($orderId)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_getUrl() . '/ordermanagement/v1/orders/' . $orderId . '/release-remaining-authorization');
        curl_setopt($ch, CURLOPT_USERPWD, $this->_getTransport()->getConfigData('merchant_id') . ':' . $this->_getTransport()->getConfigData('shared_secret'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        Mage::helper('klarna')->logDebugInfo('release rest response = ' . $response . ' status = ' . $status);

        if ($status != 204) {
            $message = $this->_buildMessageFromResponse('Error canceling order: ' . $status, $response);
            $this->_updateLastError($response, array('status' => $status));
            Mage::throwException($message);
        }
        $res = array(
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_STATUS => $status,
            Vaimo_Klarna_Helper_Data::KLARNA_API_RESPONSE_TRANSACTION_ID => $orderId,
        );
        return $res;
    }

    public function setKlarnaOrderSessionCache($value)
    {
        $this->_useKlarnaOrderSessionCache = $value;
    }

    public function initKlarnaOrder($checkoutId = null, $createIfNotExists = false, $updateItems = false, $quoteId = '')
    {
        $message = '';
        if ($checkoutId) {
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder rest checkout id: ' . $checkoutId . ' (quote id: ' . $quoteId . ')');
            $loadf = true;
            if (!$updateItems) {
                if ($this->_useKlarnaOrderSessionCache) {
                    if ($this->_klarnaOrder) {
                        $loadf = false;
                    }
                }
            }
            if ($loadf) {
                if ($updateItems) {
                    try {
                        $this->_updateOrder($checkoutId);
                    } catch (Exception $e) {
                        if ($this->_getLastError('status')=='403' &&
                            $this->_getLastError('error_code')=='READ_ONLY_ORDER') {
                            // We should probably redirect to success or display error... as this means the order is Done in Klarna...
                            $message = 'initKlarnaOrder trying to update, but it is forbidden, so we skip it and just use fetch';
                            Mage::helper('klarna')->logKlarnaApi($message);
                            $this->_fetchOrder($checkoutId);
                        } elseif ($this->_getLastError('status')=='0' &&
                                  $this->_getLastError('error_code')==null) {
                            Mage::helper('klarna')->logDebugInfo('initKlarnaOrder possible timeout in communication with Klarna');
                            return false;
                        } else {
                            throw $e;
                        }
                    }
                } else {
                    $this->_fetchOrder($checkoutId);
                }
            }
            $res = $this->_klarnaOrder!=NULL;
            if ($res) {
                if ($checkoutId!=$this->_getLocationOrderId()) {
                    $message = 'POTENTIAL ERROR. initKlarnaOrder checkoutId: ' . $checkoutId . ' received: ' . $this->_getLocationOrderId();
                    Mage::helper('klarna')->logDebugInfo($message);
                }
                if ($this->_getLocationOrderId()) {
                    $this->_setKlarnaCheckoutId($this->_getLocationOrderId());
                }
                Mage::dispatchEvent('klarnacheckout_init_klarna_order', array('klarna_order' => $this->_klarnaOrder, 'api_version' => $this->getApiVersion()));
                Mage::helper('klarna')->updateKlarnacheckoutHistory(
                    Mage::getSingleton('checkout/session')->getKlarnaCheckoutId(),
                    $message,
                    $quoteId
                );
            }
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder rest true');
            return $res;
        }

        if ($klarnaCheckoutId = $this->_getKlarnaCheckoutId()) {
            try {
                Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder rest klarnaCheckoutId id: ' . $klarnaCheckoutId . ' (quote id: ' . $quoteId . ')');
                if ($updateItems) {
                    try {
                        $this->_updateOrder($klarnaCheckoutId);
                    } catch (Exception $e) {
                        if ($this->_getLastError('status')=='403' &&
                            $this->_getLastError('error_code')=='READ_ONLY_ORDER') {
                            // We should probably redirect to success or display error... as this means the order is Done in Klarna...
                            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder trying to update, but it is forbidden, so we skip it and just use fetch');
                            $this->_fetchOrder($klarnaCheckoutId);
                        } elseif ($this->_getLastError('status')=='0' &&
                                  $this->_getLastError('error_code')==null) {
                            Mage::helper('klarna')->logDebugInfo('initKlarnaOrder possible timeout in communication with Klarna');
                            return false;
                        } else {
                            throw $e;
                        }
                    }
                } else {
                    $this->_fetchOrder($klarnaCheckoutId);
                }
                $res = $this->_klarnaOrder!=NULL;
                if ($res) {
                    if ($checkoutId!=$this->_getLocationOrderId()) {
                        $message = 'POTENTIAL ERROR. initKlarnaOrder checkoutId received: ' . $this->_getLocationOrderId();
                        Mage::helper('klarna')->logDebugInfo($message, null, $checkoutId);
                    }
                    if ($this->_getLocationOrderId()) {
                        $this->_setKlarnaCheckoutId($this->_getLocationOrderId());
                    }
                    Mage::dispatchEvent('klarnacheckout_init_klarna_order', array('klarna_order' => $this->_klarnaOrder, 'api_version' => $this->getApiVersion()));
                    Mage::helper('klarna')->updateKlarnacheckoutHistory(
                        Mage::getSingleton('checkout/session')->getKlarnaCheckoutId(),
                        $message,
                        $quoteId
                    );
                }
                Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder rest true');
                return $res;
            } catch (Exception $e) {
                // when checkout in Klarna was expired, then exception, so we just ignore and create new
                Mage::helper('klarna')->logKlarnaException($e);
            }
        }

        if ($createIfNotExists) {
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder create');
            if ($checkoutId = $this->_createOrder()) {
                $this->_fetchOrder($checkoutId);
                $res = $this->_klarnaOrder!=NULL;
                if ($res) {
                    $klarnaCheckoutId = $this->_getLocationOrderId();
                    if ($klarnaCheckoutId) {
                        $this->_setKlarnaCheckoutId($klarnaCheckoutId);
                        Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder rest created, klarnaCheckoutId id: ' . $klarnaCheckoutId . ' (quote id: ' . $quoteId . ')');
                    }
                    Mage::dispatchEvent('klarnacheckout_init_klarna_order', array('klarna_order' => $this->_klarnaOrder, 'api_version' => $this->getApiVersion()));
                }
                Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder res: ' . $res);
                Mage::helper('klarna')->updateKlarnacheckoutHistory(
                    Mage::getSingleton('checkout/session')->getKlarnaCheckoutId(),
                    $message,
                    $quoteId
                );
                return $res;
            }
        }

        return false;
    }

    /*
     * Not happy with this, but I guess we can't solve it in other ways.
     *
     */
    public function getActualKlarnaOrder()
    {
        if ($this->_klarnaOrder) {
            return $this->_klarnaOrder;
        }
        return NULL;
    }

    public function getActualKlarnaOrderArray()
    {
        if ($this->getActualKlarnaOrder()) {
            return $this->_klarnaOrder->getData();
        }
        return array();
    }

    public function getKlarnaCheckoutGui()
    {
        if ($this->_klarnaOrder) {
            return $this->_klarnaOrder->getHtmlSnippet();
        }

        return '';
    }

    public function getKlarnaCheckoutStatus()
    {
        if ($this->_klarnaOrder) {
            return $this->_klarnaOrder->getStatus();
        }

        return '';
    }

    public function loadQuote()
    {
        if ($this->_klarnaOrder) {
            /** @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::helper('klarna')->findQuote($this->_getLocationOrderId());
            if ($quote && $quote->getId()) {
                return $quote;
            }
        }
        return NULL;
    }

    public function fetchCreatedOrder($checkoutId)
    {
        $this->_fetchCreatedOrder($checkoutId);
        if ($this->_klarnaOrder) {
//            $this->_klarnaOrder->setStatus('created'); // OMG!!
            return $this->_klarnaOrder;
        }
        return NULL;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function updateKlarnaOrder($order, $repeatCall = false)
    {
        if ($this->_klarnaOrder) {
            if ($repeatCall) {
                Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder AGAIN for rest order no: ' . $order->getIncrementId());
            } else {
                Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder rest order no: ' . $order->getIncrementId());
            }

            $this->_acknowledgeOrder($this->_klarnaOrder->getOrderId());
            $this->_updateMerchantReferences($this->_klarnaOrder->getOrderId(), $order->getIncrementId());
            Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder rest success');
            return true;
        }

        return false;
    }

    protected function _setRequestList()
    {
        $default = array(
            "qty" => 0,
            "sku" => "",
            "name" => "",
            "price" => 0,
            "total_amount" => 0,
            "total_tax_amount" => 0,
            "tax" => 0,
            "discount" => 0,
            "quantity_unit" => 'pcs',
        );

        foreach ($this->_getTransport()->getGoodsList() as $array) {
            $values = array_merge($default, array_filter($array));
            $this->_request['order_lines'][] = array(
                'reference' => $values["sku"],
                'type' => 'physical',
                'name' => $values["name"],
                'unit_price' => round($values["price"] * 100),
                'quantity' => round($values["qty"]),
                'total_amount' => round($values["total_amount"] * 100),
                'tax_rate' => round($values["tax"] * 100),
                'total_tax_amount' => round($values["total_tax_amount"] * 100),
                'quantity_unit' => $values["quantity_unit"],
            );
        }
        foreach ($this->_getTransport()->getExtras() as $array) {
            $values = array_merge($default, array_filter($array));
            $this->_request['order_lines'][] = array(
                'reference' => $values["sku"],
                'type' => 'physical',
                'name' => $values["name"],
                'unit_price' => round($values["price"] * 100),
                'quantity' => round($values["qty"]),
                'total_amount' => round($values["total_amount"] * 100),
                'tax_rate' => round($values["tax"] * 100),
                'total_tax_amount' => round($values["total_tax_amount"] * 100),
                'quantity_unit' => $values["quantity_unit"],
            );
        }
    }

    /**
     * Set the goods list for Capture
     * Klarna seems to switch the order of the items in capture, so we simply add them backwards.
     *
     * @return void
     */
    public function setGoodsListCapture($amount)
    {
        $this->_request = array(
            'captured_amount' => round($amount * 100),
        );

        $this->_setRequestList();
    }

    /**
     * Set the goods list for Refund
     *
     * @return void
     */
    protected function _setGoodsListRefund($amount)
    {
        $this->_request = array(
            'refunded_amount' => round($amount * 100),
        );

        $this->_setRequestList();
    }

    public function setAddresses($billingAddress, $shippingAddress, $data)
    {
    }

    public function setShippingDetails($shipmentDetails)
    {
        if ($shipmentDetails) {
            $this->_request['shipping_info'] = $shipmentDetails;
        }
    }

    /*
     * Will return the klarna order or null, if it doesn't find it
     * Not used by this module, but as a service for others.
     *
     */
    public function getKlarnaOrderRaw($checkoutId)
    {
        if ($checkoutId) {
            if ($this->_klarnaOrder) {
                return $this->_klarnaOrder;
            }
            $this->_fetchCreatedOrder($checkoutId, true);
            if ($this->_klarnaOrder) {
                return $this->_klarnaOrder;
            }
            $this->_fetchOrder($checkoutId, true);
            if ($this->_klarnaOrder) {
                return $this->_klarnaOrder;
            }
        }
        return NULL;
    }

    public function sanityTestQuote($createdKlarnaOrder, $quote)
    {
        $res = NULL;

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $foundf = false;
            $data = $createdKlarnaOrder->getData();
            // $data is actually an object
            if (isset($data['order_lines'])) {
                $reference = Mage::helper('klarna')->getProductReference(
                    $quoteItem->getSku(),
                    $quoteItem->getAdditionalData()
                );
                foreach ($data['order_lines'] as $klarnaItem) {
                    if ($klarnaItem['reference']==substr($reference, 0, 64) &&
                        $klarnaItem['quantity']==$quoteItem->getQty()
                    ) {
                        $foundf = true;
                        continue;
                    }
                }
            }
            if (!$foundf) {
                if (!$res) $res = array();
                $res[] = Mage::helper('klarna')->__('Product not the same as on reservation:') . $quoteItem->getSku() . ' ' . $quoteItem->getName();
            }
        }

        return $res;
    }

}