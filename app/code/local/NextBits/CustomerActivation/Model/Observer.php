<?php
class NextBits_CustomerActivation_Model_Observer
{
    const XML_PATH_ALWAYS_NOTIFY_ADMIN = 'customeractive/customeractivation_group/always_send_admin_email';
    public function customerLogin($observer)
    {
        $helper = Mage::helper('customeractivation');
        if (! $helper->isModuleActive()) {
            return;
        }

        if ($this->_isApiRequest()) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        $session = Mage::getSingleton('customer/session');

        if (!$customer->getCustomerActivated()) {
           
            $session->setCustomer(Mage::getModel('customer/customer'))
                ->setId(null)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

            if ($this->_checkRequestRoute('customer', 'account', 'createpost')) {
                
                $message = $helper->__('Please wait for your account to be activated');

                $session->addSuccess($message);
            } else {
                
                Mage::throwException($helper->__('This account is not activated.'));
            }
        }
    }
    public function customerSaveBefore($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        $helper = Mage::helper('customeractivation');
        $storeId = $helper->getCustomerStoreId($customer);

        if (! $helper->isModuleActive($storeId)) {
            return;
        }

        if (!$customer->getId()) {
            $customer->setCustomerActivationNewAccount(true);
            if (! (Mage::app()->getStore()->isAdmin() && $this->_checkControllerAction('customer', 'save'))) {
               
                $groupId = $customer->getGroupId();
                $defaultStatus = $helper->getDefaultActivationStatus($groupId, $storeId);
                $customer->setCustomerActivated($defaultStatus);
                
                if (! $defaultStatus) {
                    
                    $helper = Mage::helper('customer/address');
                    if (method_exists($helper, 'isVatValidationEnabled')) {
                        if (is_callable(array($helper, 'isVatValidationEnabled'))) {
                            if (Mage::helper('customer/address')->isVatValidationEnabled($storeId)) {
                                Mage::app()->getStore($storeId)->setConfig(
                                    Mage_Customer_Helper_Address::XML_PATH_VAT_VALIDATION_ENABLED, false
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    public function customerSaveAfter($observer)
    {
      
        $customer = $observer->getEvent()->getCustomer();

        $helper = Mage::helper('customeractivation');
        $storeId = $helper->getCustomerStoreId($customer);

        if (! $helper->isModuleActive($storeId)) {
            return;
        }

        $groupId = $customer->getGroupId();
        $defaultStatus = $helper->getDefaultActivationStatus($groupId, $storeId);

        try {
            if (Mage::app()->getStore()->isAdmin()) {
                if (!$customer->getOrigData('customer_activated') && $customer->getCustomerActivated()) {
                 
                    if (!($customer->getCustomerActivationNewAccount() && $defaultStatus)) {
                        $helper->sendCustomerNotificationEmail($customer);
                    }
                }
            } else {
                if ($customer->getCustomerActivationNewAccount()) {
                 
                    $alwaysNotify = Mage::getStoreConfig(self::XML_PATH_ALWAYS_NOTIFY_ADMIN, $storeId);
                    if (!$defaultStatus || $alwaysNotify) {
                        $helper->sendAdminNotificationEmail($customer);
                    }
                }
                $customer->setCustomerActivationNewAccount(false);
            }
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    public function salesConvertQuoteAddressToOrder(Varien_Event_Observer $observer)
    {
       
        $address = $observer->getEvent()->getAddress();
        $this->_abortCheckoutRegistration($address->getQuote());
    }

    protected function _abortCheckoutRegistration(Mage_Sales_Model_Quote $quote)
    {
        $helper = Mage::helper('customeractivation');
        if (! $helper->isModuleActive($quote->getStoreId())) {
            return;
        }

        if ($this->_isApiRequest()) {
            return;
        }

        if (!Mage::getSingleton('customer/session')->isLoggedIn() && !$quote->getCustomerIsGuest()) {
            $customer = $quote->getCustomer()->save();
            if (!$customer->getCustomerActivated()) {
                $message = $helper->__(
                    'Please wait for your account to be activated, then log in and continue with the checkout'
                );
                Mage::getSingleton('core/session')->addSuccess($message);

                $targetUrl = Mage::getUrl('customer/account/login');
                $response = Mage::app()->getResponse();

                if (Mage::app()->getRequest()->isAjax()) {
                    $result = array('redirect' => $targetUrl);
                    $response->setBody(Mage::helper('core')->jsonEncode($result));
                } else if ($response->canSendHeaders(true)) {
                    $response->clearHeader('location')
                            ->setRedirect($targetUrl);
                }
                $response->sendResponse();
                exit();
            }
        }
    }
    protected function _isApiRequest()
    {
        return Mage::app()->getRequest()->getModuleName() === 'api';
    }
    protected function _checkRequestRoute($module, $controller, $action)
    {
        $req = Mage::app()->getRequest();
        if (strtolower($req->getModuleName()) == $module
                && strtolower($req->getControllerName()) == $controller
                && strtolower($req->getActionName()) == $action
        ) {
            return true;
        }
        return false;
    }
    protected function _checkControllerAction($controller, $action)
    {
        $req = Mage::app()->getRequest();
        return $this->_checkRequestRoute($req->getModuleName(), $controller, $action);
    }
    public function adminhtmlBlockHtmlBefore(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock()->getId() != 'customerGrid') {
            return;
        }
        $massBlock = $observer->getBlock()->getMassactionBlock();
        if ($massBlock) {
            $helper = Mage::helper('customeractivation');
            
            if (! $helper->isModuleActiveInAdmin()) {
                return;
            }

            $noEmail = NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITHOUT_EMAIL;
            $withEmail = NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITH_EMAIL;
            $deactivate = NextBits_CustomerActivation_Helper_Data::STATUS_DEACTIVATE;

            $massBlock->addItem(
                'customer_activated',
                array(
                    'label' => $helper->__('Customer Activated'),
                    'url' => Mage::getUrl('customeractivation/admin/massActivation'),
                    'additional' => array(
                        'status' => array(
                            'name' => 'customer_activated',
                            'type' => 'select',
                            'class' => 'required-entry',
                            'label' => $helper->__('Customer Activated'),
                            'values' => array(
                                $noEmail => $helper->__('Yes (No Notification)'),
                                $withEmail => $helper->__('Yes (With Notification)'),
                                $deactivate => $helper->__('No')
                            )
                        )
                    )
                )
            );
        }
    }
    public function eavCollectionAbstractLoadBefore(Varien_Event_Observer $observer)
    {
        if (! Mage::helper('customeractivation')->isModuleActiveInAdmin()) {
            return;
        }

        if (Mage::app()->getRequest()->getControllerName() !== 'customer') {
            return;
        }

        $collection = $observer->getEvent()->getCollection();
        $customerTypeId = Mage::getSingleton('eav/config')->getEntityType('customer')->getId();
        $collectionTypeId = $collection->getEntity()->getTypeId();
        if ($customerTypeId == $collectionTypeId) {
            $collection->addAttributeToSelect('customer_activated');

        }
    }
    public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $observer)
    {
        if (! Mage::helper('customeractivation')->isModuleActiveInAdmin()) {
            return;
        }

        if (Mage::app()->getRequest()->getControllerName() !== 'customer') {
            return;
        }

        $block = $observer->getBlock();
        if ($block->getType() === 'adminhtml/customer_grid') {

                $this->_addActivationStatusColumn($block);
            
        }
    }
    protected function _addActivationStatusColumn(Mage_Adminhtml_Block_Widget_Grid $block)
    {
        $helper = Mage::helper('customeractivation');
        $block->addColumnAfter(
            'customer_activated',
            array(
                'header' => $helper->__('Customer Activated'),
                'align' => 'center',
                'width' => '80px',
                'type' => 'options',
                'options' => array(
                    '0' => $helper->__('No'),
                    '1' => $helper->__('Yes')
                ),
                'default' => '0',
                'index' => 'customer_activated',
                'renderer' => 'customeractivation/adminhtml_widget_grid_column_renderer_boolean'
            ),
            'customer_since'
        );

        $block->sortColumnsByOrder();
    }

    public function controllerActionPostdispatchCustomerAccountResetPasswordPost(Varien_Event_Observer $observer)
    {
        if (! Mage::helper('customeractivation')->isModuleActive()) {
            return;
        }
        if (version_compare(Mage::getVersion(), '1.7', '<')) {
            $session = Mage::getSingleton('customer/session');
            $customer = $session->getCustomer();
            if (!$customer->getCustomerActivated() && $session->isLoggedIn()) {
                $session->setCustomerId(null)->setId(null);
            }
        }
            
    }
}
