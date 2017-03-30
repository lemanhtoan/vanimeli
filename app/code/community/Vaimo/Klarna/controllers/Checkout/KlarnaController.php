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

class Vaimo_Klarna_Checkout_KlarnaController extends Mage_Core_Controller_Front_Action
{

    /* @var Vaimo_Klarna_Model_Klarnacheckout_Semaphore $_semaphore */
    protected $_semaphore = null;

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _getCoreSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Get current active semaphore instance
     *
     * @return Vaimo_Klarna_Model_Klarnacheckout_Semaphore
     */
    protected function _getSemaphore()
    {
        if (!$this->_semaphore) {
            $this->_semaphore = Mage::getModel('klarna/klarnacheckout_semaphore');
        }
        return $this->_semaphore;
    }

    /**
     * This function checks valid shippingMethod
     *
     * There must be a better way...
     *
     * @return $this
     *
     */
    protected function _checkShippingMethod()
    {
        // set shipping method
        $quote = $this->_getQuote();
        $klarna = Mage::getModel('klarna/klarnacheckout');
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        $res = $klarna->checkShippingMethod();
        if ($res!==false) {
            $res = true;
        }
        return $res;
    }

    protected function _checkNewsletter()
    {
        $quote = $this->_getQuote();
        $klarna = Mage::getModel('klarna/klarnacheckout');
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        $res = $klarna->checkNewsletter();
        return $res;
    }

    public function othermethodAction()
    {

        /* Method set to false so when customer gets to standard checkout,
         * the first payment method is listed. Otherwise klarna_checkout is
         * carried through, makes the return button selected and causes totals
         * not to display until customer manually selects required method
         */
        $quote = $this->_getQuote();
        $quote->getPayment()->setMethod(false);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
        /* end of clear method code */

        $this->_getSession()->setKlarnaUseOtherMethods(true);
        if (Mage::helper('klarna')->isOneStepCheckout()) {
            $this->_redirect('onestepcheckout');
        } else {
            $this->_redirect('checkout/onepage');
        }
    }

    public function kcomethodAction()
    {
        $quote = $this->_getQuote();
        $quote->getPayment()->setMethod(Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $quote->save();
        $this->_getSession()->setKlarnaUseOtherMethods(false);
        $this->_redirect('checkout/klarna');
/*
        if (Mage::helper('klarna')->isOneStepCheckout()) {
            $this->_redirect('onestepcheckout');
        } else {
            $this->_redirect('checkout/onepage');
        }
*/
    }

    protected function _redirectToCart($store = null)
    {
        $path = Mage::helper('klarna')->getKCORedirectToCartUrl($store);
        $this->_redirect($path);
    }

    public function indexAction()
    {
        Mage::helper('klarna')->setFunctionNameForLog('klarnacheckout');
        if (!$this->_getCart()->hasQuote()) {
            // If recreate_cart_on_failed_validate is set to no, this parameter is not included
            $id = $this->getRequest()->getParam('quote_id');
            if ($id) {
                $order = Mage::getModel('sales/order')->load($id, 'quote_id');
                if ($order && $order->getId()) {
                    if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                        $comment = $this->__('Order created by Validate, but was abandoned');
                        Mage::helper('klarna')->logKlarnaApi($comment . ' (' . $order->getIncrementId() . ')');

                        $order->addStatusHistoryComment($comment);
                        $order->cancel();
                        $order->save();

                        $quoteNew = Mage::getModel('sales/quote');
                        $quoteOld = Mage::getModel('sales/quote')->load($id);

                        $quoteNew->setStoreId($quoteOld->getStoreId())
                            ->merge($quoteOld)
                            ->setKlarnaCheckoutId(NULL)
                            ->collectTotals()
                            ->save();
                        $this->_getSession()->replaceQuote($quoteNew);

                        $comment = $this->__('Canceled order and created new cart from original cart');
                        Mage::helper('klarna')->logKlarnaApi($comment . ' (' . $quoteNew->getId() . ')');

                        $order->addStatusHistoryComment($comment);
                        $order->save();

                        $error = $this->__('Payment cancelled or some error occured. Please try again.');
                        $this->_getSession()->addError($error);

                        $this->_redirectToCart($quoteNew->getStoreId());
                        return;
                    }
                }
            }
        }

        $quote = $this->_getQuote();

        if (!$quote->getId() || !$quote->hasItems() || $quote->getHasError()) {
            $this->_redirectToCart($quote->getStoreId());
            return;
        }

        $quote->load($quote->getId());
        $klarna = Mage::getModel('klarna/klarnacheckout');
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        if (!$klarna->getKlarnaCheckoutEnabled()) {
            if (Mage::helper('klarna')->isOneStepCheckout()) {
                $this->_redirect('onestepcheckout');
            } else {
                $this->_redirect('checkout/onepage');
            }
            return;
        }

        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            $this->_getSession()->addError($error);
            $this->_redirectToCart($quote->getStoreId());
            return;
        }

        $updateQuote = false;
        if (Mage::helper('klarna')->checkPaymentMethod($quote)) {
            $updateQuote = true;
        }
        if ($this->_checkShippingMethod()) {
            $updateQuote = true;
        }
        if ($this->_checkNewsletter()) {
            $updateQuote = true;
        }

        if ($updateQuote) {
            $quote->collectTotals();
            $quote->save();
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Klarna Checkout'));
        $this->renderLayout();
    }

    public function subscribeToNewsletterAction()
    {
        $quote = $this->_getQuote();
        $subscribe = $this->getRequest()->getParam('subscribe_to_newsletter');
        $quote->setKlarnaCheckoutNewsletter($subscribe);
        $quote->save();
    }

    protected function _updateAddressField($address, $field, $value)
    {
        $res = false;
        if ($value && $address->getData($field)!=$value) {
            $address->setData($field, $value);
            $res = true;
        }
        return $res;
    }

    public function addressUpdateAction()
    {
        $result = false;
        $quote = $this->_getQuote();

        $firstname = $this->getRequest()->getParam('firstname');
        $lastname = $this->getRequest()->getParam('lastname');
        $street = $this->getRequest()->getParam('street');
        $postcode = $this->getRequest()->getParam('postcode');
        $city = $this->getRequest()->getParam('city');
        $region = strtoupper($this->getRequest()->getParam('region'));
        $telephone = $this->getRequest()->getParam('telephone');
        $country = strtoupper($this->getRequest()->getParam('country'));

        $country_id = NULL;
        $region_id = NULL;

        if ($country) {
            $countryRec = Mage::getModel('directory/country')->loadByCode($country, 'iso3_code');
            if ($countryRec) {
                $country_id = $countryRec->getId();
            }
        }
        if ($region && $country_id) {
            $regionRec = Mage::getModel('directory/region')->loadByCode($region, $country_id);
            if ($regionRec) {
                $region_id = $regionRec->getId();
            }
        }

        $address = $quote->getShippingAddress();

        if ($this->_updateAddressField($address, 'firstname', $firstname)) $result = true;
        if ($this->_updateAddressField($address, 'lastname', $lastname)) $result = true;
        if ($this->_updateAddressField($address, 'street', $street)) $result = true;
        if ($this->_updateAddressField($address, 'postcode', $postcode)) $result = true;
        if ($this->_updateAddressField($address, 'city', $city)) $result = true;
        if ($this->_updateAddressField($address, 'telephone', $telephone)) $result = true;
        if ($this->_updateAddressField($address, 'country_id', $country_id)) $result = true;
        if ($this->_updateAddressField($address, 'region_id', $region_id)) $result = true;
        if ($result) {
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->save();
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function taxshippingupdateAction()
    {
        Mage::helper('klarna')->logKlarnaActionStart('klarnacheckout', 'taxshippingupdate');
        $checkoutId = $this->getRequest()->getParam('klarna_order');
        Mage::helper('klarna')->logKlarnaApi('taxshippingupdate callback received for ID ' . $checkoutId);

        //$quote = Mage::getModel('sales/quote')->load($checkoutId, 'klarna_checkout_id');
        $quote = Mage::helper('klarna')->findQuote($checkoutId);

        if ($quote && $quote->getId()) {
            $klarna = Mage::getModel('klarna/klarnacheckout');
            $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);

            $post_body = file_get_contents('php://input');
            $data = json_decode($post_body, true);
            Mage::helper('klarna')->logDebugInfo('taxshippingupdate data', $data, $checkoutId);

            $result = $klarna->updateTaxAndShipping($quote, $data);
        } else {
            $result = '';
        }

        Mage::helper('klarna')->logDebugInfo('taxshippingupdate response', $result, $checkoutId);
        $this->getResponse()->setBody(Zend_Json::encode($result));

        Mage::helper('klarna')->logKlarnaActionEnd();
    }

    public function validateFailedAction()
    {
        Mage::helper('klarna')->logKlarnaActionStart('klarnacheckout', 'validateFailed');

        $checkoutId = $this->getRequest()->getParam('klarna_order');
        //$quote = Mage::getModel('sales/quote')->load($checkoutId, 'klarna_checkout_id');
        $quote = Mage::helper('klarna')->findQuote($checkoutId);
        if ($quote && $quote->getId()) {
            $payment = $quote->getPayment();
            $errors = $payment->getAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_VALIDATE_ERRORS);
            Mage::helper('klarna')->logKlarnaApi('failedAction errors: ' . $errors);
            if ($errors) {
                $payment->unsAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_VALIDATE_ERRORS);
                $payment->save();
                $this->_getSession()->addError($errors);
            }
        } else {
            $error = $this->__('Cart not available. Please try again');
            $this->_getSession()->addError($error);
        }

        Mage::helper('klarna')->logKlarnaActionEnd();

        $this->_redirectToCart($quote->getStoreId());
        return;
    }

    protected function _initPushOrValidate($checkoutId)
    {
        $quote = Mage::helper('klarna')->findQuote($checkoutId);
        if (!$quote || !$quote->getId()) {
            return NULL;
        }
        if ($quote->getStoreId()!=Mage::app()->getStore()->getId()) {
            Mage::app()->setCurrentStore($quote->getStoreId());
        }
        return $quote;
    }

    public function validateAction()
    {
        /* @var Vaimo_Klarna_Helper_Data $helper */
        $helper = Mage::helper('klarna');
        $helper->logKlarnaActionStart('klarnacheckout', 'validate');

        $checkoutId = $this->getRequest()->getParam('klarna_order');
        if (!$this->_getSemaphore()->addSemaphore($checkoutId)) {
            $helper->logKlarnaApi('Semaphore not acquired, exiting');
            $helper->logKlarnaActionEnd();
            $this->getResponse()
                ->setHttpResponseCode(303)
                ->setHeader('Location', Mage::getUrl('checkout/klarna/validateFailed', array('klarna_order' => $checkoutId)));
            return;
        }
        $quote = $this->_initPushOrValidate($checkoutId);

        $helper->logKlarnaApi('Checkout id: ' . $checkoutId);
        if (!$quote) {
            $this->_getSemaphore()->failedSemaphore(array('message' => 'validate failed ' . 'quote not found'));
            $helper->logKlarnaApi('checkout quote not found!');
            $helper->logKlarnaActionEnd();
            $this->getResponse()
                ->setHttpResponseCode(303)
                ->setHeader('Location', Mage::getUrl('checkout/klarna/validateFailed', array('klarna_order' => $checkoutId)));
            return;
        }

        $this->_getSemaphore()->updateSemaphore(array('quote_id' => $quote->getId()));

        /** @var Vaimo_Klarna_Model_Klarnacheckout $klarna */
        $klarna = Mage::getModel('klarna/klarnacheckout');
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);

        $post_body = file_get_contents('php://input');
        $klarnaOrderData = json_decode($post_body, true);
        $helper->logDebugInfo('klarnaOrderData', $klarnaOrderData, $checkoutId);
        $createdKlarnaOrder = new Varien_Object($klarnaOrderData);

        if (substr($checkoutId, -1, 1) == '/') {
            $checkoutId = substr($checkoutId, 0, strlen($checkoutId) - 1);
        }

        if ($checkoutId) {
            try {
                $createOrderOnValidate = $klarna->getConfigData('create_order_on_validation');

                // validateQuote returns true if successful, a string if failed
                $result = $klarna->validateQuote($checkoutId, $createOrderOnValidate, $createdKlarnaOrder);

                $helper->logKlarnaApi('validateQuote result = ' . $result);

                if ($result !== true) {
                    $this->_getSemaphore()->failedSemaphore(array('message' => 'validate failed ' . $result));
                    $payment = $quote->getPayment();

                    if ($payment->getId()) {
                        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_VALIDATE_ERRORS, $result);
                        $payment->save();
                    }

                    $helper->logKlarnaActionEnd();
                    $this->getResponse()
                        ->setHttpResponseCode(303)
                        ->setHeader('Location', Mage::getUrl('checkout/klarna/validateFailed', array('klarna_order' => $checkoutId)));
                    return;
                }
                $this->_getSemaphore()->deleteSemaphore();
                $this->getResponse()
                    ->setHttpResponseCode(200);
            } catch (Exception $e) {
                $this->_getSemaphore()->failedSemaphore(array('message' => 'validate failed ' . $e->getMessage()));
                if ($quote && $quote->getId()) {
                    $payment = $quote->getPayment();
                    if ($payment && $payment->getId()) {
                        $payment->setAdditionalInformation(Vaimo_Klarna_Helper_Data::KLARNA_VALIDATE_ERRORS, $e->getMessage());
                        $payment->save();
                    }
                }
                $helper->logKlarnaException($e);
                $this->getResponse()
                    ->setHttpResponseCode(303)
                    ->setHeader('Location', Mage::getUrl('checkout/klarna/validateFailed', array('klarna_order' => $checkoutId)));
            }
        }
        $helper->logKlarnaActionEnd();
    }

    public function pushAction()
    {
        /* @var Vaimo_Klarna_Helper_Data $helper */
        $helper = Mage::helper('klarna');

        $checkoutId = $this->getRequest()->getParam('klarna_order');
        $helper->setCheckoutId($checkoutId);
        $helper->logKlarnaActionStart('klarnacheckout', 'push');

        if (!$checkoutId) {
            $helper->logKlarnaApi('klarna_order missing!');
            $helper->logKlarnaActionEnd();
            return;
        }
        if (!$this->_getSemaphore()->addSemaphore($checkoutId)) {
            if (!$this->_getSemaphore()->waitSemaphore($checkoutId)) {
                $helper->logKlarnaApi('Semaphore not acquired, exiting');
                $helper->logKlarnaActionEnd();
                return;
            }
        }
        $quote = $this->_initPushOrValidate($checkoutId);

        $helper->logKlarnaApi('Checkout id: ' . $checkoutId);
        if (!$quote) {
            $this->_getSemaphore()->failedSemaphore(array('message' => 'push failed, quote not found'));
            $helper->logKlarnaApi('checkout quote not found!');
            $helper->logKlarnaActionEnd();
            return;
        }
        $this->_getSemaphore()->updateSemaphore(array('quote_id' => $quote->getId()));

        /** @var Vaimo_Klarna_Model_Klarnacheckout $klarna */
        $klarna = Mage::getModel('klarna/klarnacheckout');
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);

        if (substr($checkoutId, -1, 1) == '/') {
            $checkoutId = substr($checkoutId, 0, strlen($checkoutId) - 1);
        }

        if ($checkoutId) {
            try {
                // createOrderFromPush returns the order if successful, otherwise an error string
                $result = $klarna->createOrderFromPush($checkoutId, false);

                if (is_array($result)) {
                    if ($result['status']=='success') {
                        $this->_getSemaphore()->deleteSemaphore();
                        $helper->logKlarnaApi('order created successfully, order id: ' . $result['order']->getId());
                        $helper->updateKlarnacheckoutHistory($checkoutId, null, $quote->getId(), $result['order']->getId());
                    } elseif ($result['status']=='fail') {
                        $this->_getSemaphore()->deleteSemaphore();
                        $helper->logKlarnaApi($result['message']);
                    } else {
                        $this->_getSemaphore()->failedSemaphore(array('message' => 'push failed ' . $result['message']));
                        $helper->logKlarnaApi($result['message']);
                    }
                } else {
                    $this->_getSemaphore()->failedSemaphore(array('message' => 'push failed ' . 'Unkown error from createOrderFromPush'));
                    $helper->logKlarnaApi('Unkown error from createOrderFromPush');
                }
            } catch (Exception $e) {
                $this->_getSemaphore()->failedSemaphore(array('message' => 'push failed ' . $e->getMessage()));
                $helper->logKlarnaException($e);
            }
        }
        $helper->logKlarnaActionEnd();
    }

    public function successAction()
    {
        /* @var Vaimo_Klarna_Helper_Data $helper */
        $helper = Mage::helper('klarna');
        try {
            $checkoutId = $this->_getSession()->getKlarnaCheckoutId();
            $helper->setCheckoutId($checkoutId);
            $helper->logKlarnaActionStart('klarnacheckout', 'success');
            $semaphoreSkipped = false;
            $revisitedf = false;
            if (!$checkoutId) {
                $checkoutId = $this->_getSession()->getKlarnaCheckoutPrevId();
                if ($checkoutId) {
                    $revisitedf = true;
                }
            }
            if (!$checkoutId) {
                $helper->logKlarnaApi('Checkout id is empty, so we do nothing');
                $helper->logKlarnaActionEnd();
                exit(1);
            }
            if (!$this->_getSemaphore()->addSemaphore($checkoutId)) {
                if (!$this->_getSemaphore()->waitSemaphore($checkoutId, 10)) {
                    $helper->logKlarnaApi('Semaphore not acquired, continuing without order');
                    $semaphoreSkipped = true;
                }
            }
            if (!$revisitedf) {
                $helper->logKlarnaApi('Checkout id: ' . $checkoutId);
            } else {
                $helper->logKlarnaApi('RE-VISITED, Checkout id: ' . $checkoutId);
            }
            //$quote = Mage::getModel('sales/quote')->load($checkoutId, 'klarna_checkout_id');
            $quote = $helper->findQuote($checkoutId);
            if (!$quote || !$quote->getId()) {
                $message = $this->__('Cart not available. Please try again') . ': ' . $checkoutId . ' revisitedf = ' . $revisitedf;
                if (!$semaphoreSkipped) {
                    $this->_getSemaphore()->failedSemaphore(array('message' => 'success failed ' . $message));
                }
                Mage::throwException($message);
            }
            if (!$semaphoreSkipped) {
                $this->_getSemaphore()->updateSemaphore(array('quote_id' => $quote->getId()));
            }
            $klarna = Mage::getModel('klarna/klarnacheckout');
            $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);

        } catch (Exception $e) {
            // Will show empty success page... however unlikely it is to get here, it's not very good
            $helper->logKlarnaException($e);
            $helper->logKlarnaActionEnd();
            if (!$semaphoreSkipped) {
                $this->_getSemaphore()->failedSemaphore(array('message' => 'success failed ' . $e->getMessage()));
            }
            return $this;
        }

        $canDisplaySuccess = null;
        // Sometimes there is a timeout or incorrect status is given by the call to Klarna,
        // especially when running against test server
        // Now we try 5 times at least, before showing blank page...
        $useCurrentOrderSession = true;
        for ($cnt = 0; $cnt < 5; $cnt++) {
            try {
                $status = $klarna->getCheckoutStatus($checkoutId, $useCurrentOrderSession);
                $canDisplaySuccess =
                    $status == 'checkout_complete' ||
                    $status == 'created' ||
                    $status == 'AUTHORIZED';
                if (!$canDisplaySuccess) {
                    $helper->logDebugInfo(
                        'incorrect status: ' . $status . ' ' .
                        'Retrying (' . ($cnt + 1) . ' / 5)',
                        $checkoutId);
                    $useCurrentOrderSession = false; // Reinitiate communication
                } else {
                    break;
                }
            } catch (Exception $e) {
                $helper->logKlarnaException($e);
                $helper->logDebugInfo(
                    'exception: ' . $e->getMessage() .
                    'Retrying (' . ($cnt + 1) . ' / 5)',
                    $checkoutId);
                $useCurrentOrderSession = false; // Reinitiate communication
            }
        }

        try {
            if (!$canDisplaySuccess) {
                $helper->logKlarnaApi('ERROR: order not created: ' . $status);
                $error = $this->__('Checkout incomplete, please try again.');
                $this->_getSession()->addError($error);
                if (!$semaphoreSkipped) {
                    $this->_getSemaphore()->failedSemaphore(array('message' => 'success failed ' . $error));
                }
                $this->_redirectToCart($quote->getStoreId());
                $helper->logKlarnaActionEnd();
                return $this;
            } else {
                $helper->logKlarnaApi('Displaying success');
            }

            $createOrderOnSuccess = $klarna->getConfigData('create_order_on_success');
            if ($semaphoreSkipped) {
                $createOrderOnSuccess = false;
            }

            if (!$revisitedf) {

                if ($quote->getId() && $quote->getIsActive()) {

                    // successActionForQuote returns true if successful, a string if failed
                    $createdKlarnaOrder = new Varien_Object($klarna->getActualKlarnaOrderArray());
                    $helper->updateKlarnacheckoutHistory($checkoutId, null, $quote->getId(), null, $createdKlarnaOrder->getReservation());
                    $result = $klarna->successActionForQuote($checkoutId, $createOrderOnSuccess, $createdKlarnaOrder);
                    $helper->logDebugInfo('successActionForQuote result = ' . $result, null, $checkoutId);

                    $order = Mage::getModel('sales/order')->load($quote->getId(), 'quote_id');

                    if ($order && $order->getId()) {
                        $helper->logKlarnaApi('successActionForQuote successfully created order with no: ' . $order->getIncrementId());
                    }

                }

                $this->_getCart()->unsetData('quote');
                $this->_getSession()->clearHelperData();
                $this->_getSession()->clear();
                $this->_getSession()->setLastQuoteId($quote->getId());
                $this->_getSession()->setLastSuccessQuoteId($quote->getId());
                $order = Mage::getModel('sales/order')->load($quote->getId(), 'quote_id');
                if ($order && $order->getId()) {
                    $this->_getSession()->setLastOrderId($order->getId());
                    $this->_getSession()->setLastRealOrderId($order->getIncrementId());
                    $helper->updateKlarnacheckoutHistory($checkoutId, null, $quote->getId(), $order->getId());
                }
                $this->_getSession()->setKlarnaCheckoutPrevId($checkoutId);
                $this->_getSession()->setKlarnaCheckoutId(''); // This needs to be cleared, to be able to create new orders
                $this->_getSession()->setKlarnaUseOtherMethods(false);
            }

            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->getLayout()->getBlock('head')->setTitle($this->__('Klarna Checkout'));

            if ($this->_getSession()->getLastOrderId()) {
                Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($this->_getSession()->getLastOrderId())));
            }

// This is KCO specific for the current API... This must find another solution
            if ($block = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('google_analytics')) {
                $block->setKlarnaCheckoutOrder($klarna->getActualKlarnaOrder());
            }

            if (!$semaphoreSkipped) {
                $this->_getSemaphore()->deleteSemaphore();
            }

            $this->renderLayout();

            // This needs to be cleared, to be able to create new orders
            // Also, it needs to be done AFTER render layout has been run...
            $this->_getSession()->setKlarnaCheckoutId('');

            $helper->logKlarnaApi('Displayed success');
            $helper->logKlarnaActionEnd();
        } catch (Exception $e) {
            // Will show empty success page... however unlikely it is to get here, it's not very good
            $helper->logKlarnaException($e);
            $helper->logKlarnaActionEnd();
            if (!$semaphoreSkipped) {
                $this->_getSemaphore()->failedSemaphore(array('message' => 'success failed ' . $e->getMessage()));
            }
            return $this;
        }
    }

    public function saveShippingMethodAction()
    {
        $resultMessage = array();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $resultMessage['shipping_method'] = $data;

            try {
                $result = $this->_getOnepage()->saveShippingMethod($data);
                if (!$result) {
                    Mage::dispatchEvent(
                       'klarnacheckout_controller_klarna_save_shipping_method',
                        array(
                             'request' => $this->getRequest(),
                             'quote'   => $this->_getOnepage()->getQuote()));
                    $this->_checkShippingMethod();
                    $this->_getOnepage()->getQuote()->collectTotals()->save();
                }
            }
            catch (Exception $e) {
                $resultMessage['error'] = $e->getMessage();
            }

            $resultMessage['success'] = 'Shipping method successfully saved';
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getResponse()->setBody(Zend_Json::encode($resultMessage));
        } else {
            $this->_redirect('checkout/klarna');
        }
    }

    public function addGiftCardAction()
    {
    	$resultMessage = array();
        $data = $this->getRequest()->getPost();
        if (isset($data['giftcard_code'])) {
            $code = $data['giftcard_code'];
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->addToCart(false);

                $this->_checkShippingMethod();
                $quote = $this->_getQuote();
                $quote->collectTotals();
                $quote->save();

                $this->_getSession()->addSuccess(
                    $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($code))
                );
                $resultMessage['success'] = $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($code));
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));
                $this->_getSession()->addError(
                    $e->getMessage()
                );
                $resultMessage['error'] = $e->getMessage();
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot apply gift card.'));
                $resultMessage['error'] = $this->__('Cannot apply gift card.');
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
        	$this->getResponse()->setBody(Zend_Json::encode($resultMessage));
        } else {
        	$this->_redirect('checkout/klarna');
        }
    }

    public function removeGiftCardAction()
    {
        $resultMessage = array();
        if ($code = $this->getRequest()->getParam('code')) {
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->removeFromCart(false);

                $this->_checkShippingMethod();
                $quote = $this->_getQuote();
                $quote->collectTotals();
                $quote->save();

                $this->_getSession()->addSuccess(
                    $this->__('Gift Card "%s" was removed.', Mage::helper('core')->htmlEscape($code))
                );
                $resultMessage['success'] = $this->__('Gift Card "%s" was removed.', Mage::helper('core')->htmlEscape($code));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(
                    $e->getMessage()
                );
                $resultMessage['error'] = $e->getMessage();
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot remove gift card.'));
                $resultMessage['error'] = $this->__('Cannot remove gift card.');
            }
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
        	$this->getResponse()->setBody(Zend_Json::encode($resultMessage));
        } else {
        	$this->_redirect('checkout/klarna');
        }
    }

    public function getKlarnaWrapperHtmlAction()
    {
        $layout = (int) $this->getRequest()->getParam('klarna_layout');

        if ($layout == 1 && !empty($layout)) {
            $blockName = 'klarna_sidebar';
        }
        else {
            $blockName = 'klarna_default';
        }

        $this->loadLayout('checkout_klarna_index');

        $block = $this->getLayout()->getBlock($blockName);
        $cartHtml = $block->toHtml();

        $result['update_sections'] = array(
            'name' => 'klarna_sidebar',
            'html' => $cartHtml
        );

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function getKlarnaCheckoutAction()
    {
        Mage::helper('klarna')->setFunctionNameForLog('klarnacheckout');
        $this->loadLayout('checkout_klarna_index');

        $block = $this->getLayout()->getBlock('checkout');
        $klarnaCheckoutHtml = $block->toHtml();

        $result['update_sections'] = array(
            'name' => 'klarna_checkout',
            'html' => $klarnaCheckoutHtml
        );

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Copy of _updateShoppingCart in Mage_Checkout_CartController but with ajax response and
     * functionality to work on checkout page. (Tried to keep as standard as possible)
     */
    public function cartUpdatePostAction()
    {
        $result = array();

        try {
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                $cart = $this->_getCart();
                $quote = $cart->getQuote();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $quote->getCustomerId()) {
                    $quote->setCustomerId(null);
                }
                $cartData = $cart->suggestItemsQty($cartData);
                $cart->updateItems($cartData);

                // Addon to check qty vs stock to support ajax response
                $items = $quote->getItemsCollection();

                foreach ($items as $item) {
                    $item->checkData();
                }
                $errors = $quote->getErrors();
                $messages = array();

                foreach ($errors as $error) {
                    $messages[] = $error->getCode();
                }

                if (count($messages) > 0) {
                    Mage::throwException(implode(', ', $messages));
                }

                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals();
                $this->_checkShippingMethod();
                $cart->save();

                // Addon for ajax to redirect to cart
                if ($this->_getCart()->getSummaryQty() <= 0) {
                    $result['redirect_url'] = Mage::getBaseUrl() . Mage::helper('klarna')->getKCORedirectToCartUrl($quote->getStoreId());
                }
            }
            $this->_getSession()->setCartWasUpdated(true);
            $result['success'] = $this->__('Shopping cart updated successfully.');

        } catch (Mage_Core_Exception $e) {
            $result['error'] = Mage::helper('core')->escapeHtml($e->getMessage());
        } catch (Exception $e) {
            $result['error'] = Mage::helper('core')->escapeHtml($e->getMessage());
            Mage::logException($e);
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function couponPostAction()
    {
        $result = array();

        try {
            $couponCode = (string)trim($this->getRequest()->getParam('coupon_code'));
            $gc = (string)trim($this->getRequest()->getParam('gc'));

            // Remove GC
            if (isset($gc) && $gc != '') {
                $gcs = $this->_getQuote()->getGiftcertCode();

                if (!$gc || !$gcs || strpos($gcs, $gc) === false) {
                    Mage::throwException('Invalid request.');
                }

                $gcsArr = array();

                foreach (explode(',', $gcs) as $gc1) {
                    if (trim($gc1) !== $gc) {
                        $gcsArr[] = $gc1;
                    }
                }

                $this->_getQuote()->setGiftcertCode(join(',', $gcsArr))->save();
                $result['success'] = $this->__("Gift certificate was removed from your order.");
            } else {
                $isGiftcertActive = Mage::helper('core')->isModuleEnabled('Unirgy_Giftcert') || Mage::helper('core')->isModuleEnabled('Icommerce_Giftcert');

                if ($isGiftcertActive) {
                    $cert = Mage::getModel('ugiftcert/cert')->load($couponCode, 'cert_number');
                } else {
                    $cert = new Varien_Object();
                }

                // If giftcert, add giftcert
                if ($isGiftcertActive && $cert->getId() && $cert->getStatus() == 'A' && $cert->getBalance() > 0) {
                    $helper = Mage::helper('ugiftcert');
                    try {
                        $quote = $this->_getQuote();
                        if (Mage::getStoreConfig('ugiftcert/default/use_conditions')) {
                            $valid = $this->_validateConditions($cert, $quote);
                            if (!$valid) {
                                $result['error'] = $helper->__("Gift certificate '%s' cannot be used with your cart items", $cert->getCertNumber());
                            }
                        }
                        $cert->addToQuote($quote);
                        $quote->collectTotals()->save();
                        $result['success'] = $helper->__("Gift certificate '%s' was applied to your order.", $cert->getCertNumber());
                    } catch (Exception $e) {
                        $result['error'] = $helper->__("Gift certificate '%s' could not be applied to your order.", $cert->getCertNumber());
                    }
                } else {
                    // Just plain coupon code
                    if ($this->getRequest()->getParam('remove') == 1) {
                        $couponCode = '';
                    }
                    $oldCouponCode = $this->_getQuote()->getCouponCode();

                    if (!strlen($couponCode) && !strlen($oldCouponCode)) {
                        throw new Exception($this->__('No coupon code was submitted.'));
                    }

                    $this->_checkShippingMethod();
                    $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                        ->collectTotals()
                        ->save();

                    if ($couponCode) {
                        if ($couponCode == $this->_getQuote()->getCouponCode()) {
                            $result['success'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                        } else {
                            $result['error'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                        }
                    } else {
                        $result['success'] = $this->__('Coupon code was canceled successfully.');
                    }
                }
            }
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function rewardPostAction()
    {
        $useRewardPoints = $this->getRequest()->getParam('use_reward_points');
        $result = array();

        $quote = $this->_getQuote();
        $quote->setUseRewardPoints((bool)$useRewardPoints);

        if ($quote->getUseRewardPoints()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($quote->getCustomer())
                ->setWebsiteId($quote->getStore()->getWebsiteId())
                ->loadByCustomer();

            $minPointsBalance = (int)Mage::getStoreConfig(
                Enterprise_Reward_Model_Reward::XML_PATH_MIN_POINTS_BALANCE,
                $quote->getStoreId()
            );

            if ($reward->getId() && $reward->getPointsBalance() >= $minPointsBalance) {
                $this->_checkShippingMethod();
                $quote->setRewardInstance($reward);
                $quote->collectTotals();
                $quote->save();
                $result['success'] = $this->__('Reward points used');
            } else {
                $quote->setUseRewardPoints(false)->collectTotals()->save();
                $result['success'] = $this->__('Reward points unused');
            }
        } else {
            $quote->setUseRewardPoints(false)->collectTotals()->save();
            $result['success'] = $this->__('Reward points unused');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function customerBalancePostAction()
    {
        $shouldUseBalance = $this->getRequest()->getParam('use_customer_balance', false);
        $result = array();

        $quote = $this->_getQuote();
        $quote->setUseCustomerBalance($shouldUseBalance);

        if ($shouldUseBalance) {
            $store = Mage::app()->getStore($quote->getStoreId());
            $balance = Mage::getModel('enterprise_customerbalance/balance')
                ->setCustomerId($quote->getCustomerId())
                ->setWebsiteId($store->getWebsiteId())
                ->loadByCustomer();
            if ($balance) {
                $quote->setCustomerBalanceInstance($balance);
                $result['success'] = $this->__('Store credit used');
            } else {
                $quote->setUseCustomerBalance(false);
                $result['success'] = $this->__('Store credit unused');
            }
        } else {
            $result['success'] = $this->__('Store credit unused');
        }

        $quote->collectTotals()->save();
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
}
