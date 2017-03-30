<?php
/**
 * Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
 */
class Magestore_Affiliateplus_Adminhtml_Affiliateplus_AccountController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('affiliateplus/account')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Accounts Manager'), Mage::helper('adminhtml')->__('Account Manager'));

        return $this;
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Accounts'));
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function customerAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.customer')
                ->setCustomers($this->getRequest()->getPost('rcustomers', null));
        $this->renderLayout();
    }

    public function customerGridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.customer')
                ->setCustomers($this->getRequest()->getPost('rcustomers', null));
        $this->renderLayout();
    }

    public function changeCustomerAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $customer_id = $this->getRequest()->getParam('customer_id');
        $customer = Mage::getModel('customer/customer')
                ->load($customer_id);
        $html = '';
        $html .= '<input type="hidden" id="map_customer_name" value="' . $customer->getName() . '" />';
        $html .= '<input type="hidden" id="map_customer_email" value="' . $customer->getEmail() . '" />';
        $html .= '<input type="hidden" id="map_customer_id" value="' . $customer->getId() . '" />';
        $this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody($html);
    }

    public function transactionAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        //$this->getLayout()->getBlock('account.edit.tab.transaction');
        $this->renderLayout();
    }

    public function transactionGridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function paymentAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function paymentGridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function processpaymentAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $accountId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        $paymentRelease = Mage::getStoreConfig('affiliateplus/payment/payment_release', $storeId);
        $whoPayFees = Mage::getStoreConfig('affiliateplus/payment/who_pay_fees');
        $isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();

        $account = Mage::getModel('affiliateplus/account')->load($accountId)
                //->setBalanceIsGlobal($isBalanceIsGlobal)
                ->setStoreId($storeId);

        if ($whoPayFees == 'payer') {
            $amount = round($account->getBalance(), 2);
            $isPayerFees = 1;
        } else {
            $isPayerFees = 0;

            if ($account->getBalance() >= 50)
                $amount = round($account->getBalance() - 1, 2); // max fee is 1$ by api
            else
                $amount = round($account->getBalance() / 1.02, 2); // fees 2% when payment by api
        }

        $paid = $account->getBalance();

        if ($account->getBalance() >= $paymentRelease) {
            $data = array(array('amount' => $amount, 'email' => $account->getPaypalEmail()));
            $url = Mage::helper('affiliateplus/payment_paypal')->getPaymanetUrl($data);

            $http = new Varien_Http_Adapter_Curl();
            $http->write(Zend_Http_Client::GET, $url);
            $response = $http->read();
            $pos = strpos($response, 'ACK=Success');

            if ($pos) { //create payment
                $storeIds = array();
                if (!$storeId) {
                    $stores = Mage::app()->getStores();
                    foreach ($stores as $store) {
                        $storeIds[] = $store->getId();
                    }
                } else
                    $storeIds = array($storeId);

                try {
                    $payment->setData('affiliateplus_account', $account);
                    $payment = Mage::getModel('affiliateplus/payment')
                            ->setAccountId($accountId)
                            ->setAccountName($account->getName())
                            ->setPaymentMethod('paypal')
                            ->setAmount($account->getBalance())
                            ->setFee(round($amount * 0.02, 2))
                            ->setRequestTime(now())
                            ->setStatus(3) //complete
                            ->setDescription(Mage::helper('affiliateplus')->__('Payment by PayPal API'))
                            ->setStoreIds(implode(',', $storeIds))
                            ->setIsRequest(0)
                            ->setIsPayerFee($isPayerFees)
                            ->save();

                    $paypalPayment = $payment->getPayment()
                            ->setEmail($account->getPaypalEmail())
                            //->setTransactionId($data['transaction_id'])
                            ->savePaymentMethodInfo();

//					$account->setBalance(0)
//							->setTotalCommissionReceived($account->getTotalCommissionReceived() + $amount)
//							->setTotalPaid($account->getTotalPaid() + $paid)
//							->save();
                    //send mail process payment to account
//					$payment->sendMailProcessPaymentToAccount();

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('The payment has been made successfully.'));
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('There was an error, please try again.'));
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('There was an error when paying by PayPal. Please try again or contact us for help.'));
            }
            $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
        }
    }

    public function editAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        $isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();
        $account = Mage::getModel('affiliateplus/account')
                //->setBalanceIsGlobal($isBalanceIsGlobal)
                ->setStoreId($storeId)
                ->load($id);

        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Accounts'));
        if ($account && $account->getId())
            $this->_title($this->__($account->getName()));
        else
            $this->_title($this->__('New Account'));

        if ($account->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $account->setData($data);
            }
            $customer = Mage::getModel('customer/customer')->load($account->getData('customer_id'));
            $account->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname());

            Mage::register('account_data', $account);

            $this->loadLayout();
            $this->_setActiveMenu('affiliateplus/account');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Account Manager'), Mage::helper('adminhtml')->__('Account Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('New Account'), Mage::helper('adminhtml')->__('New Account'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit'))
                    ->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_account_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('This account does not exist.'));
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    public function newAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->editAction();
        // $this->_forward('edit');
    }

    public function saveAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        // Changed By Adam 23/04/2015
        $storeId = $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            $accountId = $this->getRequest()->getParam('id');
            // Added By Adam (27/08/2016)
            if (isset($data['key_shop'])) {
                $data['key_shop'] = Mage::helper('affiliateplus')->refineUrlKey($data['key_shop']);
                $urlRewrite = Mage::getModel('affiliateplus/account')->loadByRequestPath($data['key_shop'], $storeId);
                if ($urlRewrite->getId()) {
                    $urlRewriteIdPath = (version_compare(Mage::getVersion(), '1.13', '>='))?$urlRewrite->getIdentifier():$urlRewrite->getIdPath();
                    if (!$this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addError('Key shop has existed. Please fill out a valid one.');
                        $this->_redirect('*/*/new', array('store' => $storeId));
                        return;
                    } elseif ($this->getRequest()->getParam('id') && $urlRewriteIdPath != 'affiliates/' . $this->getRequest()->getParam('id')) {
                        Mage::getSingleton('adminhtml/session')->addError('URL key has already existed. Please choose a different one.');
                        $this->_redirect('*/*/edit', array('store' => $storeId, 'id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
            }
            $customer = Mage::getModel('customer/customer')->load($data['customer_id']);

            $email = isset($data['email']) ? $data['email'] : '';
            if (!$accountId && !$customer->getId()) {
                if (!$email || !strpos($email, '@')) {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Invalid email address'));
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
                    return;
                }
                /* edit by blanka */
//                $customer = Mage::getResourceModel('customer/customer_collection')
//                        ->addFieldToFilter('email', $email)
//                        ->getFirstItem();
                $websiteId = null;
                if (isset($data['associate_website_id']) && $data['associate_website_id'])
                    $websiteId = $data['associate_website_id'];
                $customer = Mage::getModel('customer/customer')
                        ->setWebsiteId($websiteId)
                        ->loadByEmail($email);
                /* end edit */
                if (!$customer || !$customer->getId()) {
                    try {
                        $websiteId = isset($data['associate_website_id']) ? $data['associate_website_id'] : null;
                        $customer->setEmail($email)
                                ->setWebsiteId(Mage::app()->getWebsite($websiteId)->getId())
                                ->setGroupId($customer->getGroupId())
                                ->setFirstname($data['firstname'])
                                ->setLastname($data['lastname'])
                                ->setForceConfirmed(true);
                        $password = $data['password'];
                        if (!$password)
                            $password = $customer->generatePassword();
                        $customer->setPassword($password);
                        $customer->save();
                        //$customer->sendPasswordReminderEmail();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        Mage::getSingleton('adminhtml/session')->setFormData($data);
                        $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
                        return;
                    }
                } else {
                    $existedAccount = Mage::getModel('affiliateplus/account')->loadByCustomerId($customer->getId());
                    if ($existedAccount->getId())
                        $accountId = $existedAccount->getId();
                    if ($data['password']) {
                        try {
                            $customer->setFirstname($data['firstname'])
                                    ->setLastname($data['lastname']);
                            $customer->changePassword($data['password']);
                            $customer->sendPasswordReminderEmail();
                        } catch (Exception $e) {
                            
                        }
                    }
                }
            }

            $address = $customer->getDefaultShippingAddress();

            if ($address && $address->getId())
                $data['address_id'] = $address->getId();

            $beforeAccount = Mage::getModel('affiliateplus/account')->load($accountId);
            $beforeStatusIsDisabled = ($beforeAccount->getStatus() == 2) ? true : false;
            $unapproved = ($beforeAccount->getApproved() == 2) ? true : false;

            $account = Mage::getModel('affiliateplus/account');
            $account->setStoreId($storeId);
            $account->setData($data)->setId($accountId);


            try {
                //add event to before save 
                Mage::dispatchEvent('affiliateplus_adminhtml_before_save_account', array('post_data' => $data, 'account' => $account));
                //save customer info
                $customer->setFirstname($data['firstname'])
                        ->setLastname($data['lastname']);
                if ($email && strpos($email, '@'))
                    $customer->setEmail($email);
                $customer->save();

                $account->setName($customer->getName())
                        ->setCustomerId($customer->getId());

                if (!$accountId) {
                    $account->setIdentifyCode($account->generateIdentifyCode())
                            ->setCreatedTime(now())
                            ->setApproved(1)//approved
                    ;
                }

                $account->save();

                /* Added by Adam (27/08/2016): add customer to affiliate lifetime manually from back-end */
                $addLifetimeCustomers = isset($data['add_lifetime_customer']) ? $data['add_lifetime_customer'] : '';
                $removeLifetimeCustomers = isset($data['remove_lifetime_customer']) ? $data['remove_lifetime_customer'] : '';
                if($addLifetimeCustomers)
                    Mage::helper('affiliateplus')->addLifetimeCustomers($addLifetimeCustomers, $accountId);
                if($removeLifetimeCustomers)
                    Mage::helper('affiliateplus')->removeLifetimeCustomers($removeLifetimeCustomers, $accountId);
                /* End Code */

                // Added By Adam (27/08/2016): save key store into url rewrite
                $account->updateUrlKey();

                if ($accountId) {
                    if ($account->isEnabled() && $beforeStatusIsDisabled && $unapproved) {
                        //send mail to approved account
                        $account->sendMailToApprovedAccount();
                    }
                } else {
                    //send mail to new account
                    // Adam 01/07/2015: Fix issue of the affiliate's link in email is wrong when create account from back-end
                    $account->sendMailToNewAccount($account->getIdentifyCode());
                }

                /*Added By Adam (27/08/2016) to change the balance manually*/
                $affAccount = Mage::getModel('affiliateplus/account')->setStoreId($storeId)->load($account->getId());
                if($data['update_balance'] < 0 && abs($data['update_balance']) > $affAccount->getBalance()) {
                    $error = "The balance is not enough to subtract. Please check it again!"; //Mage::getSingleton('adminhtml/session')->addError("The balance is not enough to subtract. Please check it again!");
                    throw new Exception($error);
                } else {
                    if($data['update_balance'] != 0 && $data['update_balance'] != null){
                        $balance = $affAccount->getBalance() + $data['update_balance'];
                        $affAccount->setBalance($balance)->save();
                        Mage::helper('affiliateplus')->addTransaction($account->getId(), $account->getName(), $account->getEmail(), $data['update_balance'], $storeId);
                    }
                }
                /*End code*/

                //add event after save
                Mage::dispatchEvent('affiliateplus_adminhtml_after_save_account', array('post_data' => $data, 'account' => $account));
                //ssss

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('The account has been updated successfully.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $account->getId(), 'store' => $storeId));
                    return;
                }
                $this->_redirect('*/*/', array('store' => $storeId));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find an account to update'));
        $this->_redirect('*/*/', array('store' => $storeId));
    }

    public function deleteAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $accountId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store');
        if ($accountId > 0) {
            try {
                $model = Mage::getModel('affiliateplus/account');
                $model->setId($accountId)
                        ->delete();
                // Added By Adam (27/08/2016): remove url rewrite by key store
                $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                foreach ($stores as $store) {
                    $urlRewrite = Mage::getModel('affiliateplus/account')->loadByIdPath('affiliates/' . $accountId, $store->getId());
                    if ($urlRewrite->getId())
                        $urlRewrite->delete();
                }
                // end code;

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The account has been deleted successfully.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $accountId, 'store' => $storeId));
            }
        }
        $this->_redirect('*/*/', array('store' => $storeId));
    }

    public function massDeleteAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $accountIds = $this->getRequest()->getParam('account');
        $storeId = $this->getRequest()->getParam('store');
        if (!is_array($accountIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select account(s)'));
        } else {
            try {
                // Added By Adam (27/08/2016): remove url rewrite by key store
                $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                // end code;

                foreach ($accountIds as $accountId) {
                    $account = Mage::getModel('affiliateplus/account')->load($accountId);
                    $account->delete();

                    // Added By Adam (27/08/2016): remove url rewrite by key store
                    foreach ($stores as $store) {
                        $urlRewrite = Mage::getModel('affiliateplus/account')->loadByIdPath('affiliates/' . $accountId, $store->getId());
                        if ($urlRewrite->getId())
                            $urlRewrite->delete();
                    }
                    // end code;
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($accountIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store' => $storeId));
    }

    public function massStatusAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $accountIds = $this->getRequest()->getParam('account');
        $storeId = $this->getRequest()->getParam('store');

        if (!is_array($accountIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select account(s)'));
        } else {
            try {
                foreach ($accountIds as $accountId) {
                    $account = Mage::getSingleton('affiliateplus/account')
                            ->setStoreId($storeId)
                            ->load($accountId);
                    $beforeStatusIsDisabled = ($account->getStatus() == 2) ? true : false;
                    $unapproved = ($account->getApproved() == 2) ? true : false;
                    $account->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                    if ($account->isEnabled() && $beforeStatusIsDisabled && $unapproved) {
                        //send mail to approved account
                        $account->sendMailToApprovedAccount();
                    }
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($accountIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store' => $storeId));
    }

    public function exportCsvAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'account.csv';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_account_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'account.xml';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_account_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('affiliateplus/account');
    }

    /**
     * Added By Adam (27/08/2016)
     */
    public function productAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('affiliateplus.block.adminhtml.account.edit.tab.products')
            ->setAccountProducts($this->getRequest()->getPost('account_products', null));
        $this->renderLayout();
    }

    /**
     * Added By Adam (27/08/2016)
     */
    public function productGridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('affiliateplus.block.adminhtml.account.edit.tab.products')
            ->setAccountProducts($this->getRequest()->getPost('account_products', null));
        $this->renderLayout();
    }

    /* Customize By Adam 21/12/2015 */
    public function lifetimecustomerAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.lifetimecustomer')
            ->setLifetimecustomers($this->getRequest()->getPost('rlifetimecustomers', null));
        $this->renderLayout();
    }

    public function lifetimecustomerGridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->getLayout()->getBlock('account.edit.tab.lifetimecustomer')
            ->setLifetimecustomers($this->getRequest()->getPost('rlifetimecustomers', null));
        $this->renderLayout();
    }

    public function removeAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $tracking_id = $this->getRequest()->getParam('tracking_id');
        if ($tracking_id > 0) {
            try {
                $model = Mage::getModel('affiliateplus/tracking');

                $model->setId($tracking_id)
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The customer has been deleted from affiliate successfully.'));
                $this->_redirect('adminhtml/affiliateplus_account/edit', array('id'=> $id));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('adminhtml/affiliateplus_account/edit', array('id'=> $id));
            }
        }
        $this->_redirect('adminhtml/affiliateplus_account/edit', array('id'=> $id));
    }
    /* End Code */

}
