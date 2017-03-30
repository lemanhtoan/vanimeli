<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_IndexController extends Mage_Core_Controller_Front_Action
{

    protected $_formFields = array();

    public function comingsoonAction()
    {
        $helper = Mage::helper('comingsoon');
        $preview = Mage::getSingleton('core/session')->getData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME);
        if(!$helper->moduleEnabled() && empty($preview['comingsoon'])) {
            $helper->redirect(Mage::getBaseUrl());
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function maintenanceAction()
    {
        $helper = Mage::helper('comingsoon');
        $config = Mage::helper('comingsoon/config');
        $preview = Mage::getSingleton('core/session')->getData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME);
        if(!$helper->moduleEnabled() && empty($preview['maintenance'])) {
            $helper->redirect(Mage::getBaseUrl());
        }

        if($config->getMaintenanceResponseHeader() == '503') {
            $this->getResponse()->setHeader('HTTP/1.1', 'HTTP/1.1 503 Service Temporarily Unavailable');
            $this->getResponse()->setHeader('Status', '503 Service Temporarily Unavailable');
            if($minutes = $config->getMaintenanceRefresh()) {
                $this->getResponse()->setHeader('Retry-After', $minutes * 60);           
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function previewAction()
    {
        $helper = Mage::helper('comingsoon');
        $session = Mage::getSingleton('core/session');
        $request = $this->getRequest();
        $mode = $request->getParam('mode');
        $action = $request->getParam('action');
        switch ($mode) {
            case 'comingsoon':
            case 'maintenance':
                $url = Mage::getUrl("comingsoon/index/{$mode}");
                break;
            
            case 'live':
                $url = Mage::getBaseUrl();
                break;

            default:
                return;
        }

        $preview = $session->getData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME);
        if(!is_array($preview)) {
            $preview = array();
        }
        
        if($action == 'stop') {
            unset($preview[$mode]);
            if($url = $this->_getRefererUrl()) {
                $url = Mage::getBaseUrl();
            }
        }elseif($action == md5($helper->getCustomerDate())) {
            $preview[$mode] = time();
        }
        $session->setData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME, $preview);

        $helper->redirect($url);
    }

    public function registerAction()
    {
        $helper = Mage::helper('comingsoon');
        $config = Mage::helper('comingsoon/config');
        $session = Mage::getSingleton('customer/session');
        $preview = $session->getData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME);
        if(!$helper->moduleEnabled() && empty($preview['comingsoon'])) {
            return;
        }
        
        $this->_formFields = $config->getComingsoonSignupFields(true);
        
        try {
            if($config->getComingsoonSignupMethod() == 'register_signup') {
                // Customer registration.
                $inputData = $this->getRequest()->getParams();
                $customer = $this->_initCustomer($inputData);
                $address = $this->_initAddress($inputData);

                if (!$this->_validateCustomer($customer) || !$this->_validateAddress($address)) {
                    return false;
                }

                $customer->save();

                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                // save address
                $address->setCustomerId($customer->getId())
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);

                $address->save();
            }
            
            // Signup for email.
            if($this->_subscribe()) {   
                // Send mail.
                $email = $this->getRequest()->getParam('email');
                if(isset($customer)) {
	                if(!$customerName = $customer->getName()) {
	                	$customerName = implode(' ', array($customer->getFirstname(), $customer->getLastname()));
	                }
	                $customerName = trim($customerName);
	            }
                $this->_sendEmail($email, array(
                    'customer'		=> isset($customer)? $customer : null,
                    'customerName'	=> !empty($customerName)? $customerName : 'friend',
                ));
            }

        }
        catch (Mage_Core_Exception $e) {
            $session->addError(Mage::helper('comingsoon')->__($e->getMessage()));
        } catch (Exception $e) {
            $session->addError(Mage::helper('comingsoon')->__($e->getMessage()));
        }        
    }

    protected function _initCustomer($data)
    {
        $customer = Mage::getModel('customer/customer')->setId(null);
        $customer->getGroupId();
        $customer->setData($data);

        if (!$this->_show('password')) {
            $customer->setPassword( $customer->generatePassword() );
        }
        return $customer;
    }

    protected function _initAddress($data)
    {
        $address  = Mage::getModel('customer/address')->setId(null);
        $address->setData($data);
        return $address;
    }

    protected function _show($name)
    {
        return (array_key_exists($name, $this->_formFields) && !empty($this->_formFields[$name]['enable']));
    }

    protected function _validateCustomer($customer)
    {
        $session = Mage::getSingleton('customer/session');
        $success = true;

        if ($this->_show('firstname')) {
            if (!Zend_Validate::is( trim($customer->getFirstname()) , 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('The first name cannot be empty.'));
                $success = false;
            }
        }

        if ($this->_show('lastname')) {
            if (!Zend_Validate::is( trim($customer->getLastname()) , 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('The last name cannot be empty.'));
                $success = false;
            }
        }

        if (!Zend_Validate::is($customer->getEmail(), 'EmailAddress')) {
            $session->addError(Mage::helper('customer')->__('Invalid email address "%s".', $customer->getEmail()));
            $success = false;
        }

        if ($this->_show('confirm_email')) {
            if ($customer->getEmail() != $customer->getConfirmEmail()) {
                $session->addError(Mage::helper('customer')->__('Please make sure your emails match.'));
                $success = false;
            }
        }

        $password = $customer->getPassword();
        if (!$customer->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $session->addError(Mage::helper('customer')->__('The password cannot be empty.'));
            $success = false;
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $session->addError(Mage::helper('customer')->__('The minimum password length is %s', 6));
            $success = false;
        }
        if ($this->_show('confirm_password')) {
            $confirmation = $customer->getConfirmation();
            if ($password != $confirmation) {
                $session->addError(Mage::helper('customer')->__('Please make sure your passwords match.'));
                $success = false;
            }
        }

        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');

        if ($this->_show('dob')) {
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
            if ($attribute->getIsRequired() && '' == trim($customer->getDob())) {
                $session->addError(Mage::helper('customer')->__('The Date of Birth is required.'));
                $success = false;
            }
        }

        if ($this->_show('taxvat')) {
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
            if ($attribute->getIsRequired() && '' == trim($customer->getTaxvat())) {
                $session->addError(Mage::helper('customer')->__('The TAX/VAT number is required.'));
                $success = false;
            }
        }

        if ($this->_show('gender')) {
            $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
            if ($attribute->getIsRequired() && '' == trim($customer->getGender())) {
                $session->addError(Mage::helper('customer')->__('Gender is required.'));
                $success = false;
            }
        }
        return $success;
    }

    protected function _validateAddress($address)
    {
        $session = Mage::getSingleton('customer/session');
        $success = true;

        $address->implodeStreetAddress();

        /* Checked with Customer
        if ($this->_show('firstname')) {
            if (!Zend_Validate::is($address->getFirstname(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the first name.'));
                $success = false;
            }
        }

        if ($this->_show('lastname')) {
            if (!Zend_Validate::is($address->getLastname(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the last name.'));
                $success = false;
            }
        }*/

        if ($this->_show('street')) {
            if (!Zend_Validate::is($address->getStreet(1), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the street.'));
                $success = false;
            }
        }

        if ($this->_show('city')) {
            if (!Zend_Validate::is($address->getCity(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the city.'));
                $success = false;
            }
        }

        if ($this->_show('telephone')) {
            if (!Zend_Validate::is($address->getTelephone(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the telephone number.'));
                $success = false;
            }
        }

        if ($this->_show('postcode')) {
            $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
            if (!in_array($address->getCountryId(), $_havingOptionalZip) && !Zend_Validate::is($address->getPostcode(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the zip/postal code.'));
                $success = false;
            }
        }

        if ($this->_show('country_id')) {
            if (!Zend_Validate::is($address->getCountryId(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the country.'));
                $success = false;
            }
        }

        if ($this->_show('region')) {
            if ($address->getCountryModel()->getRegionCollection()->getSize()
                   && !Zend_Validate::is($address->getRegionId(), 'NotEmpty')) {
                $session->addError(Mage::helper('customer')->__('Please enter the state/province.'));
                $success = false;
            }
        }

        return $success;
    }

    public function _subscribe()
    {
        $session = Mage::getSingleton('customer/session');
        try {
            $email = $this->getRequest()->getParam('email');
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                Mage::throwException($this->__('Please enter a valid email address.'));
            }

            $subscriber = Mage::getModel('newsletter/subscriber')->load($email, 'subscriber_email');
            if ((int)$subscriber->getId() !== 0) {
                Mage::throwException($this->__('This email address is already assigned to another user.'));
            }

            $config = Mage::helper('comingsoon/config');


            // Subscribe in Magento.
            $importMode = $subscriber->getImportMode();
            $subscriber
                ->setImportMode(true)
                ->subscribe($email);
            $subscriber->setImportMode($importMode);

            // Subscribe in MailChimp.
            if($config->enabledMailchimp()) {
                $model = Mage::helper('comingsoon')->getMcapi();
                if ($model) {
                    $list = $config->getMailchimpList();
                    foreach ($list as $id) {
                        $model->listSubscribe($id, $email, NULL, 'html', $config->getMailchimpSendEmail());
                    }
                }
            }

            return true;

        }
        catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        }
        catch (Exception $e) {
            $session->addError($this->__('Unknown Error'));
        }

    }

    protected function _sendEmail($email, $vars = array())
    {
        // Send email
        Mage::getModel('core/email_template')
            ->sendTransactional(
                Mage::helper('comingsoon/config')->getEmailWelcome(),
                'support',
                $email,
                Mage::app()->getStore()->getName(),
                $vars
            );
    }

}