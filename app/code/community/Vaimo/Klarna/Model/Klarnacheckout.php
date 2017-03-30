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

class Vaimo_Klarna_Model_Klarnacheckout extends Vaimo_Klarna_Model_Klarnacheckout_Abstract
{
    /* @var Vaimo_Klarna_Model_Api_Abstract $_api */
    protected $_api = NULL;

    protected $_orderMessages = array();

    public function __construct($setStoreInfo = true, $moduleHelper = NULL, $coreHttpHelper = NULL, $coreUrlHelper = NULL, $customerHelper = NULL)
    {
        parent::__construct($setStoreInfo, $moduleHelper, $coreHttpHelper, $coreUrlHelper, $customerHelper);
        $this->_getHelper()->setFunctionNameForLog('klarnacheckout');
    }

    /**
     * Function added for Unit testing
     *
     * @param Vaimo_Klarna_Model_Api_Abstract $apiObject
     */
    public function setApi($apiObject)
    {
        $this->_api = $apiObject;
    }

    /**
     * Will return the API object, it set, otherwise null
     */
    public function getApi()
    {
        return $this->_api;
    }

    /**
     * Could have been added to getApi, but I made it separate for Unit testing
     *
     * @param $storeId
     * @param $method
     * @param $functionName
     */
    protected function _initApi($storeId, $method, $functionName)
    {
        if (!$this->getApi()) {
            $this->setApi(Mage::getModel('klarna/api')->getApiInstance($storeId, $method, $functionName));
        }
    }

    /**
     * Init funcition
     *
     * @todo If storeid is null, we need to find first store where Klarna is active, not just trust that default store has it active...
     */
    protected function _init($functionName)
    {
        $this->_initApi($this->_getStoreId(), $this->getMethod(), $functionName);
        $this->_api->init($this->getKlarnaSetup());
        $this->_api->setTransport($this->_getTransport());
    }


    public function getKlarnaOrderHtml($checkoutId = null, $createIfNotExists = false, $updateItems = false)
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        $this->_getHelper()->logKlarnaCheckoutFunctionStart($checkoutId, Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        if ($this->getQuote()) {
            $quote = $this->getQuote();
            if ($quote->getKlarnaCheckoutId()) {
                if ($checkoutId!=$quote->getKlarnaCheckoutId()) {
                    $this->_getHelper()->logDebugInfo('POTENTIAL ERROR. getKlarnaOrderHtml on quote: ' . $quote->getKlarnaCheckoutId(), null, $checkoutId);
                }
            }
            $this->_api->initKlarnaOrder($checkoutId, $createIfNotExists, $updateItems, $quote->getId());
        } else {
            $this->_api->initKlarnaOrder($checkoutId, $createIfNotExists, $updateItems);
        }
        $res = $this->_api->getKlarnaCheckoutGui();
        $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
        return $res;
    }

    /**
     * When we call this function, order is already done and complete. We can then cache
     * the information we get from Klarna so when we call initKlarnaOrder again (from
     * phtml files) we can use the cached order instead of fetching it again.
     *
     * @param string $checkoutId
     *
     * @return string
     */
    public function getCheckoutStatus($checkoutId = null, $useCurrentSession = true)
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        $this->_getHelper()->logKlarnaCheckoutFunctionStart($checkoutId, Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        $this->_api->setKlarnaOrderSessionCache($useCurrentSession);
        if ($this->getQuote()) {
            $quote = $this->getQuote();
            if ($quote->getKlarnaCheckoutId()) {
                if ($checkoutId!=$quote->getKlarnaCheckoutId()) {
                    $this->_getHelper()->logDebugInfo('POTENTIAL ERROR. getKlarnaOrderHtml on quote: ' . $quote->getKlarnaCheckoutId(), null, $checkoutId);
                }
            }
            $this->_api->initKlarnaOrder($checkoutId, false, false, $quote->getId());
        } else {
            $this->_api->initKlarnaOrder($checkoutId);
        }
        $res = $this->_api->getKlarnaCheckoutStatus();
        $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
        return $res;
    }

    /*
     * Not happy with this, but I guess we can't solve it in other ways.
     *
     */
    public function getActualKlarnaOrder()
    {
        return $this->_api->getActualKlarnaOrder();
    }

    public function getActualKlarnaOrderArray()
    {
        return $this->_api->getActualKlarnaOrderArray();
    }

    /*
     * Will return the klarna order or null, if it doesn't find it
     * Not used by this module, but as a service for others.
     *
     */
    public function getKlarnaOrderRaw($checkoutId)
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        $this->_getHelper()->logKlarnaCheckoutFunctionStart($checkoutId, Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCODISPLAY_ORDER);
        $res = $this->_api->getKlarnaOrderRaw($checkoutId);
        $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
        return $res;
    }

    /**
     * Checks if items has enough stock, if not, it will remove the item (if adjustFlag is set)
     *
     * @param $quote
     * @param bool $adjustFlag
     * @return array|null
     */
    protected function _checkItems($quote, $adjustFlag = false)
    {
        $res = null;
        $simpleQty = array();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getItemsCollection() as $item) {
            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                /** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
                if ($stockItem->getId()) {
                    if (isset($simpleQty[$item->getSku()])) {
                        $simpleQty[$item->getSku()] += $item->getQty();
                    } else {
                        $simpleQty[$item->getSku()] = $item->getQty();
                    }
                    if (!$stockItem->checkQty($simpleQty[$item->getSku()])) {
                        if (!$res) $res = array();
                        $res[] = $this->_getHelper()->__('The requested quantity for "%s" is not available.', $item->getName());
                        $this->_getHelper()->logDebugInfo('The requested quantity ' . $simpleQty[$item->getSku()] . ', available ' . $stockItem->getQty() . ' for SKU ' . $item->getSku(), $item->getData());
                        if ($adjustFlag) {
                            $this->_orderMessages[] = $this->_getHelper()->__(
                                'The requested quantity (%s) for SKU "%s" is not available. Product deleted from this order, but it still exists on the Klarna reservation.',
                                $simpleQty[$item->getSku()],
                                $item->getSku()
                            );
                            if ($item->getParentItemId()) {
                                $quote->removeItem($item->getParentItemId());
                            } else {
                                $quote->removeItem($item->getId());
                            }
                            $quote->setTotalsCollectedFlag(false);
                        }
                    }
                }
            }
        }
        return $res;
    }

    public function validateQuote($checkoutId, $createOrderOnValidate = NULL, $createdKlarnaOrder = NULL, $logInfo = 'validate')
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCOVALIDATE_ORDER);
        $this->_getHelper()->logKlarnaCheckoutFunctionStart($checkoutId, Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCOVALIDATE_ORDER);

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getQuote();

        if (!$quote->getId()) {
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote could not get quote', null, $checkoutId);
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return $this->_getHelper()->__('could not get quote');
        }

        if (!$quote->hasItems()) {
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote has no items', null, $checkoutId);
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return $this->_getHelper()->__('has no items');
        }

        $result = $this->_checkItems($quote);

        if ($result) {
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return implode("\n", $result);
        }

        Mage::dispatchEvent(Vaimo_Klarna_Helper_Data::KLARNA_DISPATCH_VALIDATE, array('quote' => $quote) );

        if ($quote->getHasError()) {
            /** @var Mage_Core_Model_Message_Error $error */
            foreach ($quote->getErrors() as $error) {
                $result[] = $error->getText();
            }
            if (sizeof($result)==0) {
                $result = array('Unknown error');
            }
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote errors: ' . implode(" ", $result), null, $checkoutId);
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return implode("\n", $result);
        }

        if (!$quote->validateMinimumAmount()) {
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote below minimum amount', null, $checkoutId);
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return $this->_getHelper()->__('minimum amount');
        }

        $orderId = $this->_findAlreadyCreatedOrder($quote->getId());
        if ($orderId>0) {
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote order already created ' . $orderId, null, $checkoutId);
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return $this->_getHelper()->__('order already created');
        }

        if ($createdKlarnaOrder) {
            $noticeTextArr = $this->_checkQuote($quote, $createdKlarnaOrder);
            if ($noticeTextArr!=NULL) {
                $this->_getHelper()->logDebugInfo($logInfo . 'Quote failed in checkQuote', $noticeTextArr, $checkoutId);
                $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
                return $this->_getHelper()->__('not matching cart');
            }
        }

        if ($createOrderOnValidate && $createdKlarnaOrder) {
            // As validation is ok, creating the order should work, if it doesn't, it's
            // probably a temporary reason and we should reserve ID and await the push
            $order = $this->_createOrderFromValidate($quote, $createdKlarnaOrder, $logInfo);
            if ($order && $order->getId()) {
                $this->_getHelper()->logDebugInfo($logInfo . 'Quote created order id: ' . $order->getId(), null, $checkoutId);
            } else {
                $this->_getHelper()->logDebugInfo($logInfo . 'Quote failed to created order', null, $checkoutId);
                $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
                return $this->_getHelper()->__('failed to created order');
                //$quote->reserveOrderId()->save(); // Must be wrong...
                //$this->_getHelper()->logDebugInfo($logInfo . 'Quote reserved order id: ' . $quote->getReservedOrderId());
            }
        } else {
            $quote->collectTotals();
            $quote->reserveOrderId()->save();
            $this->_getHelper()->logDebugInfo($logInfo . 'Quote reserved order id: ' . $quote->getReservedOrderId(), null, $checkoutId);
        }

        $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
        return true;
    }

    /**
     * @param string $checkoutId
     * @param bool $createOrderOnSuccess
     * @param Varien_Object $createdKlarnaOrder
     *
     * @return bool|string
     * @deprecated Use successActionForQuote instead
     */
    public function successQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder)
    {
        return $this->successActionForQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder);
    }

    /**
     * @param string $checkoutId
     * @param bool $createOrderOnSuccess
     * @param Varien_Object $createdKlarnaOrder
     *
     * @return bool|string
     */
    public function successActionForQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder)
    {
        $res = true;
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getQuote();
        // Moving the close of quote to always take place instantly in successAction, even if validateQuote will
        // close it when it creates the order, but it might take a little bit of time AND it might fail...
        $this->_getHelper()->logDebugInfo('successAction closing quote id: ' . $quote->getId(), null, $checkoutId);
        // Why not these lines?
        //$quote->setIsActive(false);
        //$quote->save();
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $read->update($resource->getTableName('sales/quote'), array('is_active' => 0), 'entity_id = ' . $quote->getId());
        if ($createOrderOnSuccess) {
            try {

                $res = $this->validateQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder, 'success');

            } catch (Exception $e) {
                $res = $this->_getHelper()->__('failed to created order on success') . ': ' . $e->getMessage();
            }

        } else {
            // Why don't we set reserve order id on Quote??
        }
        return $res;
    }
    /**
     * This function checks valid shippingMethod
     *
     * There must be a better way...
     *
     * @return $this
     *
     */
    public function checkShippingMethod()
    {
        // set shipping method
        $res = false;
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        if (!$quote->isVirtual() && $shippingAddress && !$shippingAddress->getShippingMethod()) {
            $quote->setRemoteIp($quote->getRemoteIp());
            $taxCalculationModel = Mage::getSingleton('tax/calculation');
            $request = $taxCalculationModel->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                NULL,
                $quote->getStoreId()
            );
            $shippingAddress->setCountryId($request->getCountryId());
            $shippingAddress->setRegionId($request->getRegionId());
            $shippingAddress->setPostcode($request->getPostcode());
            // Massive workaround... because Shipping Origin is per website, not store...
            /*
              Changing country at this point is not accurate, it was set to that country
              for a reason, the fact that it lacks region and postocode, should not cause
              a country change..

              Possibly, it should default to some region if the country demands one...

            if ($this->_getHelper()->getDefaultCountry()!=$shippingAddress->getCountryId()) {
                if (!$shippingAddress->getRegionId() && !$shippingAddress->getPostcode()) {
                    $shippingAddress->setCountryId($this->_getHelper()->getDefaultCountry());
                }
            }

            */
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->collectTotals();
            $shippingAddress->collectShippingRates();
            $rates = $shippingAddress->getGroupedAllShippingRates();
            foreach ($rates as $carrierRates) {
                foreach ($carrierRates as $rate) {
                    $shippingAddress->setShippingMethod($rate->getCode());
                    $quote->setTotalsCollectedFlag(false);
                    break;
                }
                break;
            }
            $shippingAddress->save();
            $res = $this->_getHelper()->__('Shipping method was not set on cart, setting default to allow the order');
        }
        if ($shippingAddress->getShippingMethod() && !$shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())) {
            $shippingAddress->setCollectShippingRates(true);
            $shippingAddress->collectTotals();
            $shippingAddress->collectShippingRates();
            $shippingAddress->save();
            if (!$res) {
                $res = $this->_getHelper()->__('Shipping rate was not properly set, recalculating');
            }
        }

        return $res;
    }


    protected  function _checkQuote($quote, $createdKlarnaOrder)
    {
        $res = NULL;

        try {

            $itemNotices = $this->_checkItems($quote, true);
            $klarnaNotices = $this->_api->sanityTestQuote($createdKlarnaOrder, $quote);
            $shippingNotice = $this->checkShippingMethod();

            if ($itemNotices || $klarnaNotices || $shippingNotice) {
                if (!$itemNotices) $itemNotices = array();
                if (!$klarnaNotices) $klarnaNotices = array();
                $res = array_merge($itemNotices, $klarnaNotices);
                $res[] = $shippingNotice;
            }

            $quote->collectTotals();

        } catch(Exception $e) {
            if (!$res) $res = array();
            $res[] = $e->getMessage();
        }
        if ($res) {
            $this->_getHelper()->logDebugInfo('_checkQuote return', $res, $createdKlarnaOrder->getId());
        }
        return $res;
    }

    protected function _updateKlarnaOrderAddress($createdKlarnaOrder)
    {
        $billingStreetAddress   = $createdKlarnaOrder->getBillingAddress('street_address');
        $billingStreetAddress2  = $createdKlarnaOrder->getBillingAddress('street_address2');
        $billingStreetName      = $createdKlarnaOrder->getBillingAddress('street_name');
        $billingStreetNumber    = $createdKlarnaOrder->getBillingAddress('street_number');
        $shippingStreetAddress  = $createdKlarnaOrder->getShippingAddress('street_address');
        $shippingStreetAddress2 = $createdKlarnaOrder->getShippingAddress('street_address2');
        $shippingStreetName     = $createdKlarnaOrder->getShippingAddress('street_name');
        $shippingStreetNumber   = $createdKlarnaOrder->getShippingAddress('street_number');

        if (!$billingStreetAddress && $billingStreetName && $billingStreetNumber) {
            $streetAddress = $createdKlarnaOrder->getBillingAddress();
            $streetAddress['street_address'] = $billingStreetName . ' ' . $billingStreetNumber;
            $createdKlarnaOrder->setBillingAddress($streetAddress);
        }
        if ($billingStreetAddress2) {
            $streetAddress = $createdKlarnaOrder->getBillingAddress();
            $streetAddress['street_address'] = array($streetAddress['street_address'], $billingStreetAddress2);
            $createdKlarnaOrder->setBillingAddress($streetAddress);
        }

        if (!$shippingStreetAddress && $shippingStreetName && $shippingStreetNumber) {
            $streetAddress = $createdKlarnaOrder->getShippingAddress();
            $streetAddress['street_address'] = $shippingStreetName . ' ' . $shippingStreetNumber;
            $createdKlarnaOrder->setShippingAddress($streetAddress);
        }
        if ($shippingStreetAddress2) {
            $streetAddress = $createdKlarnaOrder->getShippingAddress();
            $streetAddress['street_address'] = array($streetAddress['street_address'], $shippingStreetAddress2);
            $createdKlarnaOrder->setShippingAddress($streetAddress);
        }

    }

    protected function _createTheOrder($quote, $createdKlarnaOrder, $updatef, $pushf, $noticeTextArr = NULL)
    {
        $this->_getHelper()->logDebugInfo('_createTheOrder quote ID: ' . $quote->getId());

        $this->_getHelper()->checkPaymentMethod($quote, true);

        $autoRegisterGuest = $this->getConfigData('auto_register_guest');
        $isAllowedGuestCheckout = Mage::helper('checkout')->isAllowedGuestCheckout($quote);

        if ($updatef==false) {
            $isNewCustomer = false;

            $billingRegionCode  = $createdKlarnaOrder->getBillingAddress('region');
            $shippingRegionCode = $createdKlarnaOrder->getShippingAddress('region');

            if ($quote->getCustomerId()) {
                $customer = $this->_loadCustomer($quote->getCustomerId());
                $quote->setCustomer($customer);
                $quote->setCheckoutMethod('customer');
            } else {
                /** @var $customer Mage_Customer_Model_Customer */
                $customer = $this->_loadCustomerByEmail($createdKlarnaOrder->getBillingAddress('email'), $quote->getStore());
                if ($autoRegisterGuest || !$isAllowedGuestCheckout) {
                    if ($customer->getId()) {
                        $quote->setCustomer($customer);
                        $quote->setCheckoutMethod('customer');
                    } else {
                        $quote->setCheckoutMethod('register');
                        $isNewCustomer = true;
                    }
                } else {
                    $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
                }
            }

            $billingAddress = $quote->getBillingAddress();
            $billingAddress->setFirstname($createdKlarnaOrder->getBillingAddress('given_name'));
            $billingAddress->setLastname($createdKlarnaOrder->getBillingAddress('family_name'));
            $billingAddress->setCareOf($createdKlarnaOrder->getBillingAddress('care_of'));
            $billingAddress->setStreet($createdKlarnaOrder->getBillingAddress('street_address'));
            $billingAddress->setPostcode($createdKlarnaOrder->getBillingAddress('postal_code'));
            $billingAddress->setCity($createdKlarnaOrder->getBillingAddress('city'));
            $billingAddress->setCountryId(strtoupper($createdKlarnaOrder->getBillingAddress('country')));
            $billingAddress->setEmail($createdKlarnaOrder->getBillingAddress('email'));
            $billingAddress->setTelephone($createdKlarnaOrder->getBillingAddress('phone'));
            $billingAddress->setSaveInAddressBook(1);
            if ($billingRegionCode) {
                $billingRegionId = Mage::getModel('directory/region')->loadByCode($billingRegionCode, $billingAddress->getCountryId());
                $billingAddress->setRegionId($billingRegionId->getId());
            }

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setFirstname($createdKlarnaOrder->getShippingAddress('given_name'));
            $shippingAddress->setLastname($createdKlarnaOrder->getShippingAddress('family_name'));
            $shippingAddress->setCareOf($createdKlarnaOrder->getShippingAddress('care_of'));
            $shippingAddress->setStreet($createdKlarnaOrder->getShippingAddress('street_address'));
            $shippingAddress->setPostcode($createdKlarnaOrder->getShippingAddress('postal_code'));
            $shippingAddress->setCity($createdKlarnaOrder->getShippingAddress('city'));
            $shippingAddress->setCountryId(strtoupper($createdKlarnaOrder->getShippingAddress('country')));
            $shippingAddress->setEmail($createdKlarnaOrder->getShippingAddress('email'));
            $shippingAddress->setTelephone($createdKlarnaOrder->getShippingAddress('phone'));
            $shippingAddress->setSaveInAddressBook(0);
            if ($shippingRegionCode) {
                $shippingRegionId = Mage::getModel('directory/region')->loadByCode($shippingRegionCode, $shippingAddress->getCountryId());
                $shippingAddress->setRegionId($shippingRegionId->getId());
            }

            if ($this->getConfigData('packstation_enabled')) {
                $shippingAddress->setSameAsBilling(0);
            } else {
                if ($this->getConfigData('allow_separate_address')) {
                    if (!$this->_addressIsSame($billingAddress, $shippingAddress)) {
                        $shippingAddress->setSameAsBilling(0);
                        $shippingAddress->setSaveInAddressBook(1);
                    } else {
                        $shippingAddress->setSameAsBilling(1);
                    }
                } else {
                    $shippingAddress->setSameAsBilling(1);
                }
            }

            $quote->getBillingAddress()->setShouldIgnoreValidation(true);
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);

            switch ($quote->getCheckoutMethod()) {
                case Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST:
                    $this->_prepareGuestCustomerQuote($quote);
                    $quote->setCustomerIsGuest(1);
                    $quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
                    break;
                case 'register':
                    $this->_prepareNewCustomerQuote($quote);
                    break;
                case 'login_in':
                case 'customer':
                    $this->_prepareCustomerQuote($quote);
                    break;
            }

            // Some variables are not saved, so we can not trust it was done some other time
            // Before we create the order, we NEED to run this to collectTotals...
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            //$quote->save();

            Mage::dispatchEvent('klarnacheckout_quote_before_create_order', array(
                    'quote' => $quote,
                    'klarna_order' => $createdKlarnaOrder,
                    'is_push' => $pushf
                ));

            $service = $this->_getServiceQuote($quote);
            $service->submitAll();

            if ($isNewCustomer) {
                try {
                    $this->_involveNewCustomer($quote);
                } catch (Exception $e) {
                    $this->_getHelper()->logKlarnaException($e);
                }
            }

            $quote->save();
        }

        $reservation = $createdKlarnaOrder->getReservation();
        if ($createdKlarnaOrder->getOrderId()) {
            $reservation = $createdKlarnaOrder->getOrderId();
        }

        // Update Order
        /** @var $order Mage_Sales_Model_Order */
        $order = $this->_loadOrderByKey($quote->getId());

        if (!$order->getId()) {
            Mage::throwException($this->_getHelper()->__('Order cannot be created, cart not valid') . ' ' . $quote->getId());
        }

        $this->_getHelper()->logDebugInfo('_createTheOrder order ID: ' . $order->getId());

        if ($pushf) {
            if ($order->getState()==Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_NEW,
                    $this->getConfigData('order_status'),
                    $this->_getHelper()->__('Confirmation received')
                );
                $order->save();
            }
        }

        $payment = $order->getPayment();

        if (!$payment) {
            $this->_getHelper()->logDebugInfo('_createTheOrder payment is null');
        }

        if ($createdKlarnaOrder->getReference()) {
            $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_REFERENCE, $createdKlarnaOrder->getReference());
        } else if ($createdKlarnaOrder->getKlarnaReference()) {
            $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_REFERENCE, $createdKlarnaOrder->getKlarnaReference());
        }

        if ($reservation) {
            $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_RESERVATION_ID, $reservation);
        }

        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_FORM_FIELD_PHONENUMBER, $createdKlarnaOrder->getBillingAddress('phone'));
        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_FORM_FIELD_EMAIL, $createdKlarnaOrder->getBillingAddress('email'));

        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_HOST, $this->getConfigData("host") );
        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_MERCHANT_ID, $this->getConfigData("merchant_id") );

        if ($noticeTextArr) {
            $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_INFO_FIELD_NOTICE, $noticeTextArr );
        }

        if ($pushf) {
            $payment->setTransactionId($reservation)
                ->setIsTransactionClosed(0)
                ->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
            if ($transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH)) {
                $transaction->save();
            }
        }
        $payment->save();

        $this->_getHelper()->logDebugInfo('_createTheOrder payment ID: ' . $payment->getId());

        if ($this->_orderMessages) {
            foreach ($this->_orderMessages as $message) {
                $order->addStatusHistoryComment($message);
            }
            $order->save();
        }

        if ($pushf) {
            // send new order email
            if ($order->getCanSendNewEmailFlag()) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    $this->_getHelper()->logKlarnaException($e);
                }
            }

            // Subscribe customer to newsletter
            try {
                if ($quote->getKlarnaCheckoutNewsletter()) {
                    $this->_addToSubscription($createdKlarnaOrder->getBillingAddress('email'));
                }
            } catch(Exception $e) {
                $this->_getHelper()->logKlarnaException($e);
            }

            try {
                $this->_getHelper()->dispatchMethodEvent($order, Vaimo_Klarna_Helper_Data::KLARNA_DISPATCH_RESERVED, $order->getTotalDue(), $this->getMethod());

                // This will not help GA at least, since no layout block is defined in here, since it's a callback.
                // But I will leave it, if someone else has used it
                // Most modules listening to this event, expects there to be a front controller available, when there isn't one it crashes
                // So I'm taking this out
                //Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($order->getId())) );

                $this->_getHelper()->logDebugInfo('_createTheOrder order number: ' . $order->getIncrementId());

            } catch(Exception $e) {
                $this->_getHelper()->logKlarnaException($e);
            }
        }
        return $order;

    }

    protected function _createOrderFromValidate($quote, $createdKlarnaOrder, $logInfo)
    {
        $this->_updateKlarnaOrderAddress($createdKlarnaOrder);

        $order = $this->_createTheOrder($quote, $createdKlarnaOrder, false, false);

        $order->setState(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            true,
            $this->_getHelper()->__('Order created during %s, waiting for confirmation', $logInfo)
        );
        $order->save();

        return $order;
    }

    /**
     * @param null|string $checkoutId
     * @param bool $force
     *
     * @return array
     * @deprecated Use createOrderFromPush instead
     */
    public function createOrder($checkoutId = null, $force = true)
    {
        return $this->createOrderFromPush($checkoutId, $force);
    }

    /**
     * @param null|string $checkoutId
     * @param bool $force
     *
     * @return array
     */
    public function createOrderFromPush($checkoutId = NULL, $force = true)
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCOCREATE_ORDER);
        $this->_getHelper()->logKlarnaCheckoutFunctionStart($checkoutId, Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCOCREATE_ORDER);
        $createdKlarnaOrder = $this->_api->fetchCreatedOrder($checkoutId);
        $this->_getHelper()->logKlarnaDebug('createOrderFromPush', $createdKlarnaOrder->getData(), $checkoutId);
        if (!$createdKlarnaOrder) {
            $this->_getHelper()->logDebugInfo('createOrderFromPush could not fetch createdKlarnaOrder');
            $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
            return array(
                'status' => 'retry',
                'message' => 'could not fetch createdKlarnaOrder'
                );
        }

        $quote = $this->getQuote();
        if ($quote == null) {
            $quote = $this->_api->loadQuote();
            if (!$quote) {
                $this->_getHelper()->logDebugInfo('createOrderFromPush could not get quote');
                $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
                return array(
                    'status' => 'fail',
                    'message' => 'could not get quote'
                    );
            }
            $this->setQuote($quote);
        }
        $this->_getHelper()->updateKlarnacheckoutHistory($checkoutId, null, $quote->getId(), null, $createdKlarnaOrder->getReservation());

        if (!$quote->hasItems()) {
            return array(
                'status' => 'retry',
                'message' => 'quote has no items'
                );
        }

        $noticeTextArr = $this->_checkQuote($quote, $createdKlarnaOrder);

        $this->_updateKlarnaOrderAddress($createdKlarnaOrder);

        $klarnaStatus = $createdKlarnaOrder->getStatus();
        $this->_getHelper()->logDebugInfo('createOrderFromPush status of Klarna Order: ' . $klarnaStatus);

        if ($klarnaStatus != 'checkout_complete' && $klarnaStatus != 'created' && $klarnaStatus != 'AUTHORIZED') {
            $this->_getHelper()->logDebugInfo('createOrderFromPush status not complete');
            // These statuses are only valid for Rest API, need to test for v2 API codes as well
            if ($klarnaStatus == 'CANCELLED' || $klarnaStatus == 'EXPIRED' || $klarnaStatus == 'CLOSED') {
                $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
                return array(
                    'status' => 'fail',
                    'message' => 'authorization not valid ' . $createdKlarnaOrder->getStatus()
                    );
            } else {
                $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
                return array(
                    'status' => 'retry',
                    'message' => 'status not complete ' . $createdKlarnaOrder->getStatus()
                    );
            }
        }

        $updatef = false;
        $orderId = $this->_findAlreadyCreatedOrder($quote->getId());
        if ($orderId>0) {
            $this->_getHelper()->logDebugInfo('createOrderFromPush order already created, with ID ' . $orderId);
            $updatef = true;
        } else {
            $this->_getHelper()->logDebugInfo('createOrderFromPush will create new order in Magento');
        }
        $order = $this->_createTheOrder($quote, $createdKlarnaOrder, $updatef, true, $noticeTextArr);

        try {
            $this->_api->updateKlarnaOrder($order);

            if ($noticeTextArr) {
                if ($order->canHold()) {
                    $order->hold();
                    $order->save();
                }
            }
        } catch(Exception $e) {
            $this->_getHelper()->logKlarnaException($e);
        }

        $this->_getHelper()->logKlarnaCheckoutFunctionEnd();
        return array(
            'status' => 'success',
            'order' => $order
            );
    }

    public function getKlarnaCheckoutEnabled()
    {
        $remoteAddr = $this->_getCoreHttpHelper()->getRemoteAddr();
        $message = $remoteAddr . ' ' . $this->_getCoreUrlHelper()->getCurrentUrl();

        if (!$this->getConfigData('active')) {
            return false;
        }

        if ($this->getConfigData('activate_ab_testing')) {

            if ($this->_getCustomerHelper()->isLoggedIn() && !$this->getConfigData('allow_when_logged_in')) {
                return false;
            }
            $allowedCustomerGroups = $this->getConfigData('allow_customer_group');
            if (isset($allowedCustomerGroups)) {
                $allowedCustomerGroups = explode(',', $allowedCustomerGroups);

                if (!in_array(Vaimo_Klarna_Helper_Data::KLARNA_CHECKOUT_ALLOW_ALL_GROUP_ID, $allowedCustomerGroups)) {
                    $customerGroupId = $this->_getCustomerSession()->getCustomerGroupId();
                    if (!in_array($customerGroupId, $allowedCustomerGroups)) {
                        return false;
                    }
                }
            }
            if ($allowedIpRange = $this->getConfigData('allowed_ip_range')) {
                $ipParts = explode('.', $remoteAddr);

                if (is_array($ipParts) && count($ipParts) >= 4) {
                    $lastDigit = intval($ipParts[3]);
                } else {
                    $lastDigit = 0;
                }

                list ($allowIpFrom, $allowIpTo) = explode('-', $allowedIpRange, 2);

                if ($lastDigit >= (int)$allowIpFrom && $lastDigit <= (int)$allowIpTo) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    public function updateTaxAndShipping($quote, $data)
    {
        $this->_init(Vaimo_Klarna_Helper_Data::KLARNA_API_CALL_KCOCREATE_ORDER);

        $newAddress = new Varien_Object($data['shipping_address']);
        $this->_getHelper()->logDebugInfo('taxshippingupdate A' . $newAddress->getGivenName());
        $address = $quote->getShippingAddress();
        $address->setFirstname($newAddress->getGivenName());
        $address->setLastname($newAddress->getFamilyName());
        $address->setStreet($newAddress->getStreetAddress());
        $address->setPostcode($newAddress->getPostalCode());
        $address->setCity($newAddress->getCity());
        $address->setTelephone($newAddress->getPhone());
        $address->setCountryId($newAddress->getCountry());
        $regionId = Mage::getModel('directory/region')->loadByCode($newAddress->getRegion(), $address->getCountryId());
        $address->setRegionId($regionId->getId());
        $address->save();

        if (isset($data['billing_address'])) {
            $newAddress = new Varien_Object($data['billing_address']);
        }
        $address = $quote->getBillingAddress();
        $address->setFirstname($newAddress->getGivenName());
        $address->setLastname($newAddress->getFamilyName());
        $address->setStreet($newAddress->getStreetAddress());
        $address->setPostcode($newAddress->getPostalCode());
        $address->setCity($newAddress->getCity());
        $address->setTelephone($newAddress->getPhone());
        $address->setCountryId($newAddress->getCountry());
        $regionId = Mage::getModel('directory/region')->loadByCode($newAddress->getRegion(), $address->getCountryId());
        $address->setRegionId($regionId->getId());
        $address->save();

        $quote->setCustomerEmail($newAddress->getEmail());
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
        $this->setQuote($quote);
        $this->_getHelper()->logDebugInfo('taxshippingupdate A ' . $quote->getId());

        $res = $this->_api->prepareTaxAndShippingReply();
        $this->_getHelper()->logDebugInfo('taxshippingupdate B ' . $res);
        return $res;
    }

    public function checkNewsletter()
    {
        // set newsletter subscribe based on settings
        $res = false;
        $quote = $this->getQuote();
        if ($quote->getKlarnaCheckoutNewsletter() == null) {
            $type = (int)$this->getConfigData('enable_newsletter');
            $checked = (bool)$this->getConfigData('newsletter_checked');

            if (($type == Vaimo_Klarna_Helper_Data::KLARNA_CHECKOUT_NEWSLETTER_SUBSCRIBE && $checked)
                || ($type == Vaimo_Klarna_Helper_Data::KLARNA_CHECKOUT_NEWSLETTER_DONT_SUBSCRIBE && !$checked)) {
                $quote->setKlarnaCheckoutNewsletter(1);
            } else {
                $quote->setKlarnaCheckoutNewsletter(0);
            }
            $res = true;
        }

        return $res;
    }

    protected function _getTransport()
    {
        return $this;
    }
}
