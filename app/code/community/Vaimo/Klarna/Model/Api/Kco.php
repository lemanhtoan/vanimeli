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

/*
 *
 * This is the only file in the module that loads and uses the Klarna library folder
 * It should never be instantiated by itself, it can, but for readability one should not
 * No Klarna specific variables, constants or functions should be used outside this class
 *
 */

class Vaimo_Klarna_Model_Api_Kco extends Vaimo_Klarna_Model_Api_Abstract
{
    protected $_klarnaOrder = NULL;
    protected $_useKlarnaOrderSessionCache = false;

    public function setApiVersion($apiVersion)
    {
        $this->_apiVersion = $apiVersion;
        return $this;
    }

    public function getApiVersion()
    {
        return ($this->_apiVersion);
    }

    protected function _getLocationOrderId()
    {
        $res = $this->_klarnaOrder->getLocation();
        $arr = explode('/', $res);
        if (is_array($arr)) {
            $res = $arr[sizeof($arr)-1];
        }
        return $res;
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/cart')->getQuote();
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
            Mage::helper('klarna')->logDebugInfo('Quote Id: ' . $quote->getId(), null, $checkoutId);
            $quote->setKlarnaCheckoutId($checkoutId);
            $quote->save();
            Mage::helper('klarna')->updateKlarnacheckoutHistory($checkoutId, $message, $quote->getId());
        }

        Mage::getSingleton('checkout/session')->setKlarnaCheckoutId($checkoutId);
    }

    /**
     * Get quote items and totals
     *
     * @return array
     */
    protected function _getCartItems()
    {
        $quote = $this->_getQuote();
        $items = array();
        $qtyMultiplierArray = array();

        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            if (Mage::helper('klarna')->shouldItemBeIncluded($quoteItem)==false) {
                continue;
            }

            $shouldSumsBeZero = Mage::helper('klarna')->checkBundles($quoteItem);
            if ($shouldSumsBeZero) {
                $qtyMultiplierArray[$quoteItem->getItemId()] = $quoteItem->getQty();
            }

            if ($quoteItem->getTaxPercent() > 0) {
                $taxRate = $quoteItem->getTaxPercent();
            } elseif ($quoteItem->getRowTotal() != 0) {
                $taxRate = $quoteItem->getTaxAmount() / $quoteItem->getRowTotal() * 100;
            } else {
                $taxRate = 0;
            }

            $price = $quoteItem->getPriceInclTax();
            $totalInclTax = $quoteItem->getRowTotalInclTax();
            $taxAmount = $quoteItem->getTaxAmount();
            if ($shouldSumsBeZero) {
                $price = 0;
                $totalInclTax = 0;
                $taxAmount = 0;
            }
            $qty = $quoteItem->getQty();
            if (isset($qtyMultiplierArray[$quoteItem->getParentItemId()])) {
                $qty = $qty * $qtyMultiplierArray[$quoteItem->getParentItemId()];
            }
            
            $reference = $quoteItem->getSku();
            if ($this->_skuNotUnique($quote->getAllItems(), $quoteItem)) {
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
                'reference' => $reference,
                'name' => $quoteItem->getName(),
                'quantity' => round($qty),
                'unit_price' => round($price * 100),
//                'discount_rate' => round($quoteItem->getDiscountPercent() * 100),
                'tax_rate' => round($taxRate * 100),
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
                        $amount_incl_tax = $amount + $taxAmount + $hiddenTaxAmount;
                        $items[] = array(
                            'type' => 'shipping_fee',
                            'reference' => Mage::helper('klarna')->__('shipping'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round(($amount_incl_tax) * 100),
                            'discount_rate' => 0,
                            'tax_rate' => round($taxRate * 100),
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
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => Mage::helper('klarna')->__('discount'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => -round(($amount + $taxAmount) * 100),
                            'discount_rate' => 0,
                            'tax_rate' => round($taxRate * 100),
                        );
                    }
                    break;
                case 'giftcardaccount':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => Mage::helper('klarna')->__('gift_card'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                        );
                    }
                    break;
                case 'ugiftcert':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => Mage::helper('klarna')->__('gift_card'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => -round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                        );
                    }
                    break;
                case 'reward':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => Mage::helper('klarna')->__('reward'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                        );
                    }
                    break;
                case 'customerbalance':
                    if ($total->getValue() != 0) {
                        $items[] = array(
                            'type' => 'discount',
                            'reference' => Mage::helper('klarna')->__('customer_balance'), // $total->getCode()
                            'name' => $total->getTitle(),
                            'quantity' => 1,
                            'unit_price' => round($total->getValue() * 100),
                            'discount_rate' => 0,
                            'tax_rate' => 0,
                        );
                    }
                    break;
            }
        }

        return $items;
    }

    /**
     * Get create request
     *
     * @return array
     */
    protected function _getCreateRequest()
    {
        $create = array();
        $create['purchase_country'] = Mage::helper('klarna')->getDefaultCountry();
        $create['purchase_currency'] = $this->_getQuote()->getQuoteCurrencyCode();
        $create['locale'] = str_replace('_', '-', $this->_klarnaSetup->getLocaleCode());
        $create['merchant']['id'] = $this->_klarnaSetup->getMerchantId();
        $terms = Mage::helper('klarna')->getTermsUrl($this->_klarnaSetup->getTermsUrl());
        if ($terms) {
            $create['merchant']['terms_uri'] = $terms;
        }
        if ($this->_getTransport()->getConfigData('recreate_cart_on_failed_validate')) {
            $create['merchant']['checkout_uri'] = Mage::getUrl('checkout/klarna', array('_nosid' => true, 'quote_id' => $this->_getQuote()->getId()));
        } else {
            $create['merchant']['checkout_uri'] = Mage::getUrl('checkout/klarna');
        }
        $create['merchant']['confirmation_uri'] = Mage::getUrl('checkout/klarna/success');
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

        $pushUrl = Mage::getUrl('checkout/klarna/push?klarna_order={checkout.order.id}', array('_nosid' => true));
        if (substr($pushUrl, -1, 1) == '/') {
            $pushUrl = substr($pushUrl, 0, strlen($pushUrl) - 1);
        }

        $create['merchant']['push_uri'] = $pushUrl;

        if ($this->_getTransport()->getConfigData('enable_validation')) {
            $validateUrl = Mage::getUrl('checkout/klarna/validate?klarna_order={checkout.order.id}', array('_nosid' => true));
            if (substr($validateUrl, -1, 1) == '/') {
                $validateUrl = substr($validateUrl, 0, strlen($validateUrl) - 1);
            }
            if (substr($validateUrl, 0, 5) == 'https') {
                $create['merchant']['validation_uri'] = $validateUrl;
            }
        }

        $create['cart']['items'] = $this->_getCartItems();

        if ($data = $this->_getBillingAddressData()) {
            $create['shipping_address'] = $data;
        }

        if ($data = $this->_getCustomerData()) {
            $create['customer'] = $data;
        }

        Mage::helper('klarna')->logDebugInfo('_getCreateRequest', $create);
        $request = new Varien_Object($create);
        Mage::dispatchEvent('klarnacheckout_get_create_request', array('request' => $request));

        return $request->getData();
    }

    /**
     * Get update request
     *
     * @return array
     */
    protected function _getUpdateRequest()
    {
        $update = array();
        $update['cart']['items'] = $this->_getCartItems();
//        $update['gui']['layout'] = 'desktop';

        if ($data = $this->_getBillingAddressData()) {
            $update['shipping_address'] = $data;
        }

        if ($data = $this->_getCustomerData()) {
            $update['customer'] = $data;
        }

        Mage::helper('klarna')->logDebugInfo('_getUpdateRequest', $update);
        $request = new Varien_Object($update);
        Mage::dispatchEvent('klarnacheckout_get_update_request', array('request' => $request));

        return $request->getData();
    }

    protected function _getBillingAddressData()
    {
        if (!$this->_getTransport()->getConfigData('auto_prefil')) return NULL;

        /** @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $result = array();
            $address = $session->getCustomer()->getPrimaryBillingAddress();
            if ($session->getCustomer()->getEmail()) {
                $result['email'] = $session->getCustomer()->getEmail();
            }
            if ($address) {
                if ($address->getPostcode()) {
                    $result['postal_code'] = $address->getPostcode();
                }
            }
            if ($this->_getTransport()->moreDetailsToKCORequest()) {
                if ($address &&
                    ( preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $address->getStreet(1), $tmp) )) {
                    $streetName = $tmp[1];
                    $streetNumber = $tmp[2];
                }

                if ($gender = $session->getCustomer()->getGender()) {
                    switch ($gender) {
                        case 1:
                            $gender = Mage::helper('klarna')->__('Male');
                            break;
                        case 2:
                            $gender = Mage::helper('klarna')->__('Female');
                            break;
                    }
                }
                if ($streetName) {
                    $result['street_name'] = $streetName;
                }
                if ($streetNumber) {
                    $result['street_number'] = $streetNumber;
                }
                if ($address) {
                    if ($address->getFirstname()) {
                        $result['given_name'] = $address->getFirstname();
                    }
                    if ($address->getLastname()) {
                        $result['family_name'] = $address->getLastname();
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
                    if ($gender) {
                        $result['title'] = $gender;
                    }
                }
            }
            return $result;
        }

        return NULL;
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

    public function init($klarnaSetup)
    {
        $this->_klarnaSetup = $klarnaSetup;
        if ($this->_klarnaSetup->getHost()=='LIVE') {
            Klarna_Checkout_Order::$baseUri = 'https://checkout.klarna.com/checkout/orders';
        } else {
            Klarna_Checkout_Order::$baseUri = 'https://checkout.testdrive.klarna.com/checkout/orders';
        }
        Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";
    }

    /**
     * Get connector
     *
     * @return Klarna_Checkout_Connector
     */
    protected function _getConnector()
    {
        $secret = $this->_klarnaSetup->getSharedSecret();

        if (method_exists('Mage', 'getEdition')) {
            $magentoEdition = Mage::getEdition();
        } else {
            if (Mage::helper('klarna')->isEnterpriseAndHasClass()) {
                $magentoEdition = "Enterprise";
            } else {
                $magentoEdition = "Community";
            }
        }
        $magentoVersion = Mage::getVersion();
        $module = (string)Mage::getConfig()->getNode()->modules->Vaimo_Klarna->name;
        $version = (string)Mage::getConfig()->getNode()->modules->Vaimo_Klarna->version;
        $module_info = array('Application' => array(
                             'name' => 'Magento ' . '_' . $magentoEdition,
                             'version' => $magentoVersion),
                             'Module' => array(
                             'name' => $module,
                             'version' => $version),
                             );
        return Klarna_Checkout_Connector::create($secret, $module_info);
    }

    public function setKlarnaOrderSessionCache($value)
    {
        $this->_useKlarnaOrderSessionCache = $value;
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
            $this->_klarnaOrder = new Klarna_Checkout_Order($this->_getConnector(), Klarna_Checkout_Order::$baseUri . '/' . $checkoutId);
            $this->_klarnaOrder->fetch();
            $order = new Varien_Object($this->_klarnaOrder->marshal());
            return $order;
        }
        return NULL;
    }

    /**
     * @param null $checkoutId
     * @param bool $createIfNotExists
     * @param bool $updateItems
     * @param string $quoteId
     * @return bool
     * @throws Exception
     */
    public function initKlarnaOrder($checkoutId = null, $createIfNotExists = false, $updateItems = false, $quoteId = '')
    {
        $message = '';
        if ($checkoutId) {
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder quote id: ' . $quoteId, null, $checkoutId);
            $loadf = true;
            if (!$updateItems) {
                if ($this->_useKlarnaOrderSessionCache) {
                    if ($this->_klarnaOrder) {
                        $loadf = false;
                    }
                }
            }
            if ($loadf) {
                $this->_klarnaOrder = new Klarna_Checkout_Order($this->_getConnector(), Klarna_Checkout_Order::$baseUri . '/' . $checkoutId);
                if ($updateItems) {
                    try {
                        $this->_klarnaOrder->update($this->_getUpdateRequest());
                    } catch (Exception $e) {
                        $this->_updateLastError($e->getMessage());
                        if ($this->_getLastError('http_status_code')=='403' &&
                            $this->_getLastError('http_status_message')=='Forbidden') {
                            // We should probably redirect to success or display error... as this means the order is Done in Klarna...
                            $message = 'initKlarnaOrder trying to update, but it is forbidden, so we skip it and just use fetch';
                            Mage::helper('klarna')->logKlarnaApi($message);
                        } elseif ($this->_getLastError('http_status_code')=='0' &&
                                  $this->_getLastError('http_status_message')==null) {
                            Mage::helper('klarna')->logDebugInfo('initKlarnaOrder possible timeout in communication with Klarna');
                            return false;
                        } else {
                            throw $e;
                        }
                    }
                }
                $this->_klarnaOrder->fetch();
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
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder res: ' . $res);
            return $res;
        }

        if ($klarnaCheckoutId = $this->_getKlarnaCheckoutId()) {
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder klarnaCheckoutId id: ' . $klarnaCheckoutId . ' (quote id: ' . $quoteId . ')');
            $this->_klarnaOrder = new Klarna_Checkout_Order($this->_getConnector(), Klarna_Checkout_Order::$baseUri . '/' . $klarnaCheckoutId);
            if ($updateItems) {
                try {
                    $this->_klarnaOrder->update($this->_getUpdateRequest());
                } catch (Exception $e) {
                    $this->_updateLastError($e->getMessage());
                    if ($this->_getLastError('http_status_code')=='403' &&
                        $this->_getLastError('http_status_message')=='Forbidden') {
                        // We should probably redirect to success or display error... as this means the order is Done in Klarna...
                        Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder trying to update, but it is forbidden, so we skip it and just use fetch');
                    } elseif ($this->_getLastError('http_status_code')=='0' &&
                              $this->_getLastError('http_status_message')==null) {
                        Mage::helper('klarna')->logDebugInfo('initKlarnaOrder possible timeout in communication with Klarna');
                        return false;
                    } else {
                        throw $e;
                    }
                }
            }
            $this->_klarnaOrder->fetch();
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
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder res: ' . $res);
            return $res;
        }

        if ($createIfNotExists) {
            Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder create');
            $this->_klarnaOrder = new Klarna_Checkout_Order($this->_getConnector());
            $this->_klarnaOrder->create($this->_getCreateRequest());
            $this->_klarnaOrder->fetch();
            $res = $this->_klarnaOrder!=NULL;
            if ($res) {
                $klarnaCheckoutId = $this->_getLocationOrderId();
                if ($klarnaCheckoutId) {
                    $this->_setKlarnaCheckoutId($klarnaCheckoutId);
                    Mage::helper('klarna')->logKlarnaApi('initKlarnaOrder created, klarnaCheckoutId id: ' . $klarnaCheckoutId . ' (quote id: ' . $quoteId . ')');
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

        return false;
    }

    public function prepareTaxAndShippingReply()
    {
        return '';
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
            return $this->_klarnaOrder->marshal();
        }
        return array();
    }

    public function getKlarnaCheckoutGui()
    {
        if ($this->_klarnaOrder) {
            if ($this->_klarnaOrder->offsetExists('gui')) {
                $gui = $this->_klarnaOrder->offsetGet('gui');
                return isset($gui['snippet']) ? $gui['snippet'] : '';
            }
        }
        return '';
    }

    public function getKlarnaCheckoutStatus()
    {
        if ($this->_klarnaOrder) {
            if ($this->_klarnaOrder->offsetExists('status')) {
                return $this->_klarnaOrder->offsetGet('status');
            }
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
        $this->initKlarnaOrder($checkoutId);
        if ($this->_klarnaOrder) {
            $order = new Varien_Object($this->_klarnaOrder->marshal());
            if ($order) {
                return $order;
            }
        }
        return NULL;
    }

    public function updateKlarnaOrder($order, $repeatCall = false)
    {
        if ($this->_klarnaOrder) {
            if ($repeatCall) {
                Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder AGAIN for order no: ' . $order->getIncrementId());
            } else {
                Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder order no: ' . $order->getIncrementId());
            }
            // Update Klarna
            $update = array(
                'status' => 'created',
                'merchant_reference' => array('orderid1' => $order->getIncrementId()),
            );

            // Add extra attribute to order
            $orderid2Code = trim($this->_getTransport()->getConfigData('extra_order_attribute'));
            if ($orderid2Code && $orderid2Code!='' && $order->getData($orderid2Code)) {
                $orderid2Value = $order->getData($orderid2Code);
                $update['merchant_reference']['orderid2'] = $orderid2Value;
            }

            $this->_klarnaOrder->update($update);
            Mage::helper('klarna')->logKlarnaApi('updateKlarnaOrder success');
            return true;
        }
        return false;
    }

    public function sanityTestQuote($createdKlarnaOrder, $quote)
    {
        $res = null;

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $foundf = false;
            $data = $createdKlarnaOrder->getData();
            // $data is actually an object
            if (isset($data['cart'])) {
                if (isset($data['cart']['items'])) {
                    $reference = Mage::helper('klarna')->getProductReference(
                        $quoteItem->getSku(),
                        $quoteItem->getAdditionalData()
                    );
                    foreach ($data['cart']['items'] as $klarnaItem) {
                        if ($klarnaItem['reference'] == substr($reference, 0, 64) &&
                            $klarnaItem['quantity'] == $quoteItem->getQty()
                        ) {
                            $foundf = true;
                            continue;
                        }
                    }
                    if ($foundf) {
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
