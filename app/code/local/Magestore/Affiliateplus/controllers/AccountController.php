<?php

class Magestore_Affiliateplus_AccountController extends Mage_Core_Controller_Front_Action {

    /**
     * get Affiliateplus session
     *
     * @return Magestore_Affiliateplus_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('affiliateplus/session');
    }

    /**
     * get Core Session
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getCoreSession() {
        return Mage::getSingleton('core/session');
    }

    /**
     * get Customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession() {
        return Mage::getSingleton('customer/session');
    }

    public function editAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (Mage::helper('affiliateplus/account')->accountNotLogin())
            return $this->_redirect('affiliateplus/account/login');

        $session = $this->_getSession();
        $formData = $session->getCustomer()->getData();
        $formData['account'] = $session->getAccount()->getData();
        $formData['account_name'] = $session->getCustomer()->getName();
        $formData['paypal_email'] = $session->getAccount()->getPaypalEmail();
        $formData['notification'] = $session->getAccount()->getNotification();
        $formData['key_shop'] = $session->getAccount()->getKeyShop();


        /*
          hainh update for adding referring website to form data in order to use on edit form
          22-04-2014
         */
        $formData['referring_website'] = $session->getAccount()->getReferringWebsite();
        $session->setAffiliateFormData($formData);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Account Settings'));
        $this->renderLayout();
    }

    public function editPostAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (Mage::helper('affiliateplus/account')->accountNotLogin())
            return $this->_redirect('affiliateplus/account/login');
        if (!$this->getRequest()->isPost())
            return $this->_redirect('affiliateplus/account/edit');
        $session = $this->_getSession();
        $coreSession = $this->_getCoreSession();
        $customerSession = $this->_getCustomerSession();

        $data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));

        $customer = $customerSession->getCustomer();
        $customer->addData($data);
        $customer->setFirstname($data['firstname']);
        $customer->setLastname($data['lastname']);

        $errors = array();
        if (isset($data['account_address_id']) && $data['account_address_id']) {
            $address = Mage::getModel('customer/address')->load($data['account_address_id']);
        } else {
            $address_data = $this->getRequest()->getPost('account');
            $address = Mage::getModel('customer/address')
                    ->setData($address_data)
                    ->setParentId($customer->getId())
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setId(null);
            $customer->addAddress($address);
            $errors = $address->validate();
        }
        if (!is_array($errors))
            $errors = array();
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('confirmation');

            $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
            if (Mage::helper('core/string')->strpos($oldPass, ':'))
                list($_salt, $salt) = explode(':', $oldPass);
            else
                $salt = false;

            if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                if (strlen($newPass)) {
                    $customer->setPassword($newPass);
                    $customer->setPasswordConfirmation($confPass);
                } else {
                    $errors[] = $this->__('The New Password field is empty. Please enter a new password.');
                }
            } else {
                $errors[] = $this->__('Please re-enter your current password.');
            }
        }
        try {
            $validationCustomer = $customer->validate();
            if (is_array($validationCustomer))
                $errors = array_merge($validationCustomer, $errors);
            $validationResult = (count($errors) == 0);

            if (true === $validationResult) {
                $customer->save();
                if (!$address->getId())
                    $address->save();
            }else {
                foreach ($errors as $error)
                    $coreSession->addError($error);
                $formData = $this->getRequest()->getPost();
                $formData['account_name'] = $customer->getName();
                $formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
                $session->setAffiliateFormData($formData);
                return $this->_redirect('affiliateplus/account/edit');
            }
        } catch (Exception $e) {
            $coreSession->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/edit');
        }
        $account = Mage::getModel('affiliateplus/account')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($session->getAccount()->getId());
        try {
            /*
              hainh update for saving referring website
              22-04-2014
             */
            $account->setData('referring_website', $data['referring_website']);
            /* end updating */
            $data['paypal_email'] = isset($data['paypal_email']) ? $data['paypal_email'] : '';
            $account->setData('name', $customer->getName())
                    ->setData('paypal_email', $data['paypal_email'])
                    ->setData('notification', isset($data['notification']) ? 1 : 0);
            if ($address)
                $account->setData('address_id', $address->getId());
            $account->save();
            $successMessage = $this->__('Your account information has been saved.');
            $coreSession->addSuccess($successMessage);
            return $this->_redirect('affiliateplus/account/edit');
        } catch (Exception $e) {
            $coreSession->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/edit');
        }
    }

    public function loginAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (Mage::helper('affiliateplus/account')->isLoggedIn()) {
            $this->_getCoreSession()->addSuccess(Mage::helper('affiliateplus')->__('You have logged in successfully.'));
            return $this->_redirect('affiliateplus/index/index');
        } elseif (Mage::helper('affiliateplus/account')->isRegistered()) {
            $this->_getCoreSession()->addError(Mage::helper('affiliateplus')->__('Your affiliate account is currently disabled. Please contact us to resolve this issue.'));
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->getRequest()->getServer('HTTP_REFERER'))
            $this->_getSession()->setDirectUrl($this->getRequest()->getServer('HTTP_REFERER'));
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Affiliate login'));
        $this->renderLayout();
    }

    public function loginPostAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (!$this->getRequest()->isPost() || $this->_getCustomerSession()->isLoggedIn())
            return $this->_redirect('affiliateplus/account/login');
        //Login to affiliate system
        $login = $this->getRequest()->getPost('login');
        if (!empty($login['username']) && !empty($login['password'])) {
            try {
                $this->_getCustomerSession()->login($login['username'], $login['password']);
                if ($this->_getSession()->getDirectUrl()) {
                    $this->_redirectUrl($this->_getSession()->getDirectUrl());
                    $this->_getSession()->setDirectUrl(null);
                    return;
                }
                return $this->_redirect('affiliateplus/index/index');
            } catch (Mage_Core_Exception $e) {
                switch ($e->getCode()) {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $message = $e->getMessage();
                        break;
                    default:
                        $message = $e->getMessage();
                }
                $this->_getCoreSession()->addError($message);
                $this->_getCoreSession()->setLoginFormData(array('email' => $login['username']));
            }
        } else {
            $this->_getCoreSession()->addError($this->__('Please enter your username and password.'));
        }

        return $this->_redirect('affiliateplus/account/login');
    }

    public function logoutAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        $this->_getCustomerSession()->logout()
                ->setBeforeAuthUrl(Mage::getUrl());
        $this->_redirect('customer/account/logoutSuccess');
    }

    public function registerAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (Mage::helper('affiliateplus/account')->isRegistered()) {
            if (Mage::helper('affiliateplus/account')->isLoggedIn()) {
                $this->_getCoreSession()->addSuccess(Mage::helper('affiliateplus')->__('You have logged in successfully.'));
                return $this->_redirect('affiliateplus/index/index');
            } else {
                //$this->_getCoreSession()->addError(Mage::helper('affiliateplus')->__('You already had an affiliate account, please log in.'));
                return $this->_redirect('affiliateplus/account/login');
            }
        }
        if ($this->_getCustomerSession()->isLoggedIn()) {
            $formData = array('account_name' => $this->_getCustomerSession()->getCustomer()->getName());
            $this->_getSession()->setAffiliateFormData($formData);
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('affiliateplus')->__('Sign up for an Affiliate Account'));
        $this->renderLayout();
    }

    public function createPostAction() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return $this->_redirectUrl(Mage::getBaseUrl());
        
        if (!$this->getRequest()->isPost())
            return $this->_redirect('affiliateplus/account/register');

        $session = $this->_getSession();
        $coreSession = $this->_getCoreSession();
        $customerSession = $this->_getCustomerSession();

        $address = '';
        
        //hainh 28-07-2014 save on session
        $referredBy = $this->getRequest()->getPost('referred_by', '');
        if (($referredBy) && ($referredBy != '')) {
            $websiteId = Mage::app()->getWebsite()->getId();
            $customerId = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($referredBy)->getId();
            if ($customerId && ($customerId != '')) {
                $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('customer_id', $customerId)->getFirstItem();
                if ($account && ($account->getAccountId())) {
                    $accountCode = $account->getIdentifyCode();
                    $expiredTime = Mage::helper('affiliateplus/config')->getGeneralConfig('expired_time');
                    Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code', $accountCode);
                    Mage::helper('affiliateplus/cookie')->saveCookie($accountCode, $expiredTime, true);
                }
            }
        }
        //end editing

        if ($session->isRegistered()) {
            //Registered
            //$coreSession->addError(Mage::helper('affiliateplus')->__('You already had an affiliate account, please log in.'));
            return $this->_redirect('affiliateplus/account/login');
        } elseif ($customerSession->isLoggedIn()) {
            $data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
            //Check Captcha Code
            $captchaCode = $coreSession->getData('register_account_captcha_code');
            if ($captchaCode != $data['account_captcha']) {
                $session->setAffiliateFormData($this->getRequest()->getPost());
                $coreSession->addError(Mage::helper('affiliateplus')->__('The verification code entered is incorrect. Please try again.'));
                return $this->_redirect('affiliateplus/account/register');
            }
            //Customer not register affiliate account
            $customer = $customerSession->getCustomer();
            if (isset($data['account_address_id']) && $data['account_address_id']) {
                $address = Mage::getModel('customer/address')->load($data['account_address_id']);
            } elseif (Mage::helper('affiliateplus/config')->getSharingConfig('required_address')) {
                $address_data = $this->getRequest()->getPost('account');
                $address = Mage::getModel('customer/address')
                        ->setData($address_data)
                        ->setParentId($customer->getId())
                        ->setFirstname($customer->getFirstname())
                        ->setLastname($customer->getLastname())
                        ->setId(null);
                $customer->addAddress($address);
                $errors = $address->validate();
                if (!is_array($errors))
                    $errors = array();
                try {
                    $validationCustomer = $customer->validate();
                    if (is_array($validationCustomer))
                        $errors = array_merge($validationCustomer, $errors);
                    $validationResult = (count($errors) == 0);
                    if (true === $validationResult) {
                        $customer->save();
                        $address->save();
                    } else {
                        foreach ($errors as $error)
                            $coreSession->addError($error);
                        $formData = $this->getRequest()->getPost();
                        $formData['account_name'] = $customer->getName();
                        $session->setAffiliateFormData($formData);
                        return $this->_redirect('affiliateplus/account/register');
                    }
                } catch (Exception $e) {
                    $coreSession->addError($e->getMessage());
                    $formData = $this->getRequest()->getPost();
                    $formData['account_name'] = $customer->getName();
                    $session->setAffiliateFormData($formData);
                    return $this->_redirect('affiliateplus/account/register');
                }
            }
        } else {

            $data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
            //Check Captcha Code
            $captchaCode = $coreSession->getData('register_account_captcha_code');
            if ($captchaCode != $data['account_captcha']) {
                $session->setAffiliateFormData($this->getRequest()->getPost());
                $coreSession->addError(Mage::helper('affiliateplus')->__('The verification code entered is incorrect. Please try again.'));
                return $this->_redirect('affiliateplus/account/register');
            }

            //Create new customer and affiliate account
            $customerSession->setEscapeMessages(true);
            $errors = array();
            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            foreach (Mage::getConfig()->getFieldset('customer_account') as $code => $node)
                if ($node->is('create') && isset($data[$code])) {
                    if ($code == 'email')
                        $data[$code] = trim($data[$code]);
                    $customer->setData($code, $data[$code]);
                }

            $customer->getGroupId();

            if (Mage::helper('affiliateplus/config')->getSharingConfig('required_address')) {
                $address_data = $this->getRequest()->getPost('account');
                $address = Mage::getModel('customer/address')
                        ->setData($address_data)
                        ->setFirstname($customer->getFirstname())
                        ->setLastname($customer->getLastname())
                        ->setIsDefaultBilling(true)
                        ->setIsDefaultShipping(true)
                        ->setId(null);
                $customer->addAddress($address);

                $errors = $address->validate();
            }
            if (!is_array($errors))
                $errors = array();

            try {
                $customer->setPasswordConfirmation($data['confirmation']);
                $validationCustomer = $customer->validate();
                if (is_array($validationCustomer))
                    $errors = array_merge($validationCustomer, $errors);
                $validationResult = (count($errors) == 0);
                if (true === $validationResult) {
                    $customer->save();
                    if ($address)
                        $address->save();
                    $successMessage = '';
                    $successMessage = Mage::helper('affiliateplus/account')
                                        ->createAffiliateAccount($address, $data['paypal_email'],
                                            $customer, $this->getRequest()->getPost('notification'),
                                            $this->getRequest()->getPost('referring_website'),
                                            $successMessage, $referredBy, $coreSession);
                    if(Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code')) {
                        Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code', '');
                    }

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                                'confirmation', $customerSession->getBeforeAuthUrl(), Mage::app()->getStore()->getId()
                        );
                        $coreSession->addSuccess(
                            $this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                            Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                    } else {
                        $coreSession->addSuccess($successMessage);
                        $customerSession->setCustomerAsLoggedIn($customer);
                    }
                    return $this->_redirect('affiliateplus/index/index');
                } else {
                    foreach ($errors as $error)
                        $coreSession->addError($error);
                    $formData = $this->getRequest()->getPost();
                    $formData['account_name'] = $customer->getName();
                    $session->setAffiliateFormData($formData);
                    return $this->_redirect('affiliateplus/account/register');
                }
            } catch (Exception $e) {
                $coreSession->addError($e->getMessage());
                $formData = $this->getRequest()->getPost();
                $formData['account_name'] = $customer->getName();
                $session->setAffiliateFormData($formData);
                return $this->_redirect('affiliateplus/account/register');
            }
        }

        try {
            //hainh 22-07-2014
           /*
            $referredBy = $this->getRequest()->getPost('referred_by');
            if (($referredBy) && ($referredBy != '')) {
                $websiteId = Mage::app()->getWebsite()->getId();
                $customerId = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($referredBy)->getId();
                if ($customerId && ($customerId != '')) {
                    $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('customer_id', $customerId)->getFirstItem();
                    if ($account && ($account->getAccountId())) {
                        $accountCode = $account->getIdentifyCode();
                        $expiredTime = Mage::helper('affiliateplus/config')->getGeneralConfig('expired_time');
                        Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code', $accountCode);
                        Mage::helper('affiliateplus/cookie')->saveCookie($accountCode, $expiredTime, true);
                    }
                }
            }
            */
            $successMessage = '';
            $keyShop = Mage::helper('affiliateplus')->refineUrlKey($this->getRequest()->getPost('key_shop'));
            $successMessage = Mage::helper('affiliateplus/account')->createAffiliateAccount($address, $this->getRequest()->getPost('paypal_email'), $customer, $this->getRequest()->getPost('notification'), $this->getRequest()->getPost('referring_website'), $successMessage, $referredBy, $coreSession, $keyShop);

            //add success
            $coreSession->addSuccess($successMessage);
            
            // Changed By Adam 11/09/2014: fix issue of referred by: coi referred by nhu click vao link affiliate
        if(Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code')) {
            Mage::getSingleton('affiliateplus/session')->setData('top_affiliate_indentify_code', '');
        }
        
            return $this->_redirect('affiliateplus/index/index');
        } catch (Exception $e) {
            $coreSession->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/register');
        }
    }

    public function imagecaptchaAction() {
        require_once(Mage::getBaseDir('lib') . DS . 'captcha' . DS . 'class.simplecaptcha.php');
        $config['BackgroundImage'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "white.png";
        $config['BackgroundColor'] = "FF0000";
        $config['Height'] = 30;
        $config['Width'] = 100;
        $config['Font_Size'] = 23;
        $config['Font'] = Mage::getBaseDir('lib') . DS . 'captcha' . DS . "ARLRDBD.TTF";
        $config['TextMinimumAngle'] = 15;
        $config['TextMaximumAngle'] = 30;
        $config['TextColor'] = '2B519A';
        $config['TextLength'] = 4;
        $config['Transparency'] = 80;
        $captcha = new SimpleCaptcha($config);
        $this->_getCoreSession()->setData('register_account_captcha_code', $captcha->Code);
    }

    public function refreshcaptchaAction() {
        $result = Mage::getModel('core/url')->getUrl('*/*/imageCaptcha', array('tms' => time()));
        echo $result;
    }

    public function checkemailregisterAction() {
        $email_address = $this->getRequest()->getParam('email_address');
        $isvalid_email = true;
        if (!Zend_Validate::is(trim($email_address), 'EmailAddress')) {
            $isvalid_email = false;
        }
        if ($isvalid_email) {
            $error = false;
            $websiteId = Mage::app()->getWebsite()->getId();
//			$email = Mage::getModel('customer/customer')->getCollection()
//				->addAttributeToFilter('email',$email_address)
//				->getFirstItem();
            /* edit by blanka */
            $email = Mage::getModel('customer/customer')->setWebsiteId($websiteId)
                    ->loadByEmail($email_address);
            /* end edit */
            if ($email->getId()) {
                $error = true;
            }
            if ($error != '') {
                $html = "<div class='error-msg'>" . $this->__('The email %s belongs to a customer. If it is your email address, you can use it to <a href="%s">login</a> our system.', $email_address, Mage::getUrl('*/*/login')) . "</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="0"/>';
            } else {
                $html = "<div class='success-msg'>" . $this->__('You can use this email address.') . "</div>";
                $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
            }
        } else {
            $html = "<div class='error-msg'>" . $this->__('Invalid email address.') . "</div>";
            $html .= '<input type="hidden" id="is_valid_email" value="1"/>';
        }
        $this->getResponse()->setBody($html);
    }
    
    
    /*Changed By Adam 12/09/2014: check referred by email before register*/
    public function checkreferredemailAction(){
        $email_address = $this->getRequest()->getParam('email_address');
        $isvalid_email = true;
        if (!Zend_Validate::is(trim($email_address), 'EmailAddress')) {
            $isvalid_email = false;
        }
        if ($isvalid_email) {
            $error = true;
            
            $affiliate = Mage::getModel('affiliateplus/account')->load($email_address, 'email');
            /* end edit */
            if ($affiliate && $affiliate->getId()) {
                
                $error = false;
            }
            if (!$error) {
                $html = "<div class='success-msg'>".$this->__('You are referring by %s', $affiliate->getName())."</div>";
                $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
                
                
//                $html = "<div class='error-msg'>" . $this->__('The email %s belongs to a customer. If it is your email address, you can use it to <a href="%s">login</a> our system.', $email_address, Mage::getUrl('*/*/login')) . "</div>";
//                $html .= '<input type="hidden" id="is_valid_email" value="0"/>';
            } else {
                $html = "<div class='error-msg'>" . $this->__('There is no affiliate with email address %s. Please enter a different one.', $email_address) . "</div>";
                $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
            }
        } else {
            $html = "<div class='error-msg'>" . $this->__('Invalid email address.') . "</div>";
            $html .= '<input type="hidden" id="is_valid_referredemail" value="1"/>';
        }
        $this->getResponse()->setBody($html);
    }

    /**
     * Added By Adam (27/08/2016): Check key shop before registering
     */
    public function checkkeyshopAction() {
        $keyShop = $this->getRequest()->getParam('key_shop');
        $isValidKey = true;
        if ($isValidKey) {
            $error = false;
            $collection = Mage::getResourceModel('affiliateplus/account_collection')
                ->addFieldToFilter('key_shop', $keyShop)
                ->getFirstItem();

            if ($collection && $collection->getId()) {
                $error = true;
            }

            if ($error != '') {
                $html = "<div class='error-msg'>" . $this->__('The key shop %s belongs to a customer. Please try a different one', $keyShop)."</div>";
                $html .= '<input type="hidden" id="is_valid_key_shop" value="0"/>';
            } else {
                $html = "<div class='success-msg'>" . $this->__('You can use <b>%s</b> as key shop', $keyShop) . "</div>";
                $html .= '<input type="hidden" id="is_valid_key_shop" value="1"/>';
            }
        }
        $this->getResponse()->setBody($html);
    }
}