<?php
class NextBits_CustomerActivation_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_EMAIL_ADMIN_NOTIFICATION = 'customeractive/customeractivation_group/admin_email';
    const XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE = 'customeractive/customeractivation_group/registration_admin_template';
    const XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE = 'customeractive/customeractivation_group/activation_template';
    const XML_PATH_ALERT_CUSTOMER = 'customeractive/customeractivation_group/alert_customer';
    const XML_PATH_ALERT_ADMIN = 'customeractive/customeractivation_group/alert_admin';
    const XML_PATH_DEFAULT_STATUS = 'customeractive/customeractivation_group/activation_status_default';
    const XML_PATH_DEFAULT_STATUS_BY_GROUP = 'customeractive/customeractivation_group/require_activation_for_specific_groups';
    const XML_PATH_DEFAULT_STATUS_GROUPS = 'customeractive/customeractivation_group/require_activation_groups';

    const XML_PATH_MODULE_DISABLED = 'customeractive/customeractivation_group/disable_ext';
    const XML_PATH_ALWAYS_ACTIVE_ADMIN = 'customeractive/customeractivation_group/always_active_in_admin';

    const STATUS_ACTIVATE_WITHOUT_EMAIL = 1;
    const STATUS_ACTIVATE_WITH_EMAIL = 2;
    const STATUS_DEACTIVATE = 0;

    protected $_origEmailDesignConfig;

    public function isModuleActive($store = null)
    {
        $value = Mage::getStoreConfig(self::XML_PATH_MODULE_DISABLED, $store);
        return ! $value;
    }
    
    public function isModuleActiveInAdmin()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ALWAYS_ACTIVE_ADMIN)) {
            return true;
        }
        return $this->isModuleActive(0);
    }
    public function sendAdminNotificationEmail(Mage_Customer_Model_Customer $customer)
    {
        $storeId = $this->getCustomerStoreId($customer);
        if (Mage::getStoreConfig(self::XML_PATH_ALERT_ADMIN, $storeId)) {
            $to = $this->_getEmails(self::XML_PATH_EMAIL_ADMIN_NOTIFICATION, $storeId);
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
            $this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_ADMIN_NOTIFICATION_TEMPLATE, $storeId);
        }
        return $this;
    }
    public function sendCustomerNotificationEmail(Mage_Customer_Model_Customer $customer)
    {
        if (Mage::getStoreConfig(self::XML_PATH_ALERT_CUSTOMER, $this->getCustomerStoreId($customer))) {
            $to = array(array(
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ));
            $this->_sendNotificationEmail($to, $customer, self::XML_PATH_EMAIL_CUSTOMER_NOTIFICATION_TEMPLATE);
        }
        return $this;
    }
    protected function _sendNotificationEmail($to, $customer, $templateConfigPath, $storeId = null)
    {
        if (!$to) return;

        if (is_null($storeId)) {
            $storeId = $this->getCustomerStoreId($customer);
        }

        $translate = Mage::getSingleton('core/translate')
            ->setTranslateInline(false);

        $mailTemplate = Mage::getModel('core/email_template');

        $template = Mage::getStoreConfig($templateConfigPath, $storeId);

        $sendTo = array();
        foreach ($to as $recipient) {
            if (is_array($recipient)) {
                $sendTo[] = $recipient;
            } else {
                $sendTo[] = array('email' => $recipient, 'name' => null);
            }
        }

        $this->_setEmailDesignConfig($mailTemplate, $storeId);

        foreach ($sendTo as $recipient) {
            $mailTemplate->sendTransactional(
                    $template,
                    Mage::getStoreConfig(Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $storeId),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'customer' => $customer,
                        'shipping' => $customer->getPrimaryShippingAddress(),
                        'billing' => $customer->getPrimaryBillingAddress(),
                        'store' => Mage::app()->getStore(
                            $this->getCustomerStoreId($customer)
                        ),
                    ),
                    $storeId
                );
        }
        $this->_revertEmailDesignConfig($mailTemplate);

        $translate->setTranslateInline(true);

        return $this;
    }
    protected function _setEmailDesignConfig(Mage_Core_Model_Email_Template $mailTemplate, $storeId)
    {
        $this->_origEmailDesignConfig = null;

        if (is_callable(array($mailTemplate, 'getDesignConfig'))) {
            $this->_origEmailDesignConfig = $mailTemplate->getDesignConfig();
            
        } elseif (version_compare(phpversion(), '5.3.2', '>=')) {
            $method = new ReflectionMethod($mailTemplate, 'getDesignConfig');
            if ($method->isProtected()) {
                $method->setAccessible(true);
            }
            if ($this->_origEmailDesignConfig = $method->invoke($mailTemplate)) {
                $this->_origEmailDesignConfig = $this->_origEmailDesignConfig->getData();
            }
        }
        if (! $this->_origEmailDesignConfig) {
            $this->_origEmailDesignConfig = array(
                'area' => Mage::app()->getStore()->isAdmin() ? 'adminhtml' : 'frontend',
                'store' => Mage::app()->getStore()->getId()
            );
        }

        $mailTemplate->setDesignConfig(array(
                'area' => Mage::app()->getStore($storeId)->isAdmin() ? 'adminhtml' : 'frontend',
                'store' => $storeId)
        );

        return $this;
    }
    protected function _revertEmailDesignConfig(Mage_Core_Model_Email_Template $mailTemplate)
    {
        $mailTemplate->setDesignConfig($this->_origEmailDesignConfig);
        return $this;
    }

    protected function _getEmails($configPath, $storeId = null)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
    public function getCustomerStoreId(Mage_Customer_Model_Customer $customer)
    {
        if (!($storeId = $customer->getSendemailStoreId())) {
            $storeId = $customer->getStoreId();
            if (!$storeId && $customer->getWebsiteId()) {
                if ($store = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()) {
                    $storeId = $store->getId();
                }
            }
            if (!$storeId && !Mage::app()->getStore()->isAdmin()) {
                $storeId = Mage::app()->getStore()->getId();
            }
        }
        return $storeId;
    }

    public function getDefaultActivationStatus($groupId, $storeId)
    {
        $defaultIsActive = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS, $storeId);
        $activateByGroup = Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS_BY_GROUP, $storeId);
        
        if (! $defaultIsActive && $activateByGroup) {
            $notActiveGroups = explode(',', Mage::getStoreConfig(self::XML_PATH_DEFAULT_STATUS_GROUPS, $storeId));
            $isActive = in_array($groupId, $notActiveGroups) ? false : true;
        } else {
            $isActive = $defaultIsActive;
        }

        return $isActive;
    }
}
