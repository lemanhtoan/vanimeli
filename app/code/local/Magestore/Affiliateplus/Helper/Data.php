<?php

class Magestore_Affiliateplus_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';

    public function getBackendProductHtmls($productIds) {
        $productHtmls = array();
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $productName = Mage::getModel('catalog/product')->load($productId)->getName();
            $productUrl = $this->_getUrl('adminhtml/catalog_product/edit/', array('_current' => true, 'id' => $productId));
            $productHtmls[] = '<a href="' . $productUrl . '" title="' . Mage::helper('affiliateplus')->__('View Product Details') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    //get sender information from Admin Panel Page
    public function getStoreId() {
        return Mage::getModel('affiliateplus/account')->getStoreId();
    }

    public function getSenderContact() {
        $storeId = $this->getStoreId();
        $senderEmailConfiguration = array();
        $emailConfiguration = Mage::getStoreConfig('affiliateplus/email/email_sender', $storeId);
        $nameConfiguration = Mage::getStoreConfig('affiliateplus/email/name_sender', $storeId);
        $senderDefault = Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_IDENTITY, $storeId);
        if (!$emailConfiguration) {
            if (!$nameConfiguration) {
                $senderEmailConfiguration = $senderDefault;
            } else {
                $senderEmailConfiguration = array(
                    'email' => $senderDefault['email'],
                    'name' => $nameConfiguration,
                );
            }
        } else {
            if (!$nameConfiguration) {
                $senderEmailConfiguration = array(
                    'email' => $emailConfiguration,
                    'name' => $senderDefault['name'],
                );
            } else {
                $senderEmailConfiguration = array(
                    'email' => $emailConfiguration,
                    'name' => $nameConfiguration,
                );
            }
        }
        return $senderEmailConfiguration;
    }

    public function getFrontendProductHtmls($productIds) {
        $productHtmls = array();
        $productIds = explode(',', $productIds);
        foreach ($productIds as $productId) {
            $product = Mage::getModel('catalog/product')->load($productId);
            $productName = $product->getName();
            $productUrl = $product->getProductUrl();
            $productHtmls[] = '<a href="' . $productUrl . '" title="' . Mage::helper('affiliateplus')->__('View Product Details') . '">' . $productName . '</a>';
        }
        return implode('<br />', $productHtmls);
    }

    public function getStore($storeId) {
        return Mage::getModel('core/store')->load($storeId);
    }

    public function getAffiliateCustomerIds() {
        $customerIds = array();
        $collection = Mage::getModel('affiliateplus/account')->getCollection();

        foreach ($collection as $account) {
            $customerIds[] = $account->getCustomerId();
        }

        return $customerIds;
    }

    public function isBalanceIsGlobal() {
        $scope = Mage::getStoreConfig('affiliateplus/account/balance');
        if ($scope == 'store')
            return false;
        else
            return true;
    }

    public function multilevelIsActive() {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (isset($modulesArray['Magestore_Affiliatepluslevel']) && is_object($modulesArray['Magestore_Affiliatepluslevel']))
            return $modulesArray['Magestore_Affiliatepluslevel']->is('active');
        return false;
    }

    public function isRobots() {
        $storeId = Mage::app()->getStore()->getId();
        if (!Mage::getStoreConfig('affiliateplus/action/detect_software'))
            return false;
        if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == '')
            return true;
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'])
            return true;
        define("UNKNOWN", 0);
        define("TRIDENT", 1);
        define("GECKO", 2);
        define("PRESTO", 3);
        define("WEBKIT", 4);
        define("VALIDATOR", 5);
        define("ROBOTS", 6);

        if (!isset($_SESSION["info"]['browser'])) {

            $_SESSION["info"]['browser']['engine'] = UNKNOWN;
            $_SESSION["info"]['browser']['version'] = UNKNOWN;
            $_SESSION["info"]['browser']['platform'] = 'Unknown';

            $navigator_user_agent = ' ' . strtolower($_SERVER['HTTP_USER_AGENT']);

            if (strpos($navigator_user_agent, 'linux')) :
                $_SESSION["info"]['browser']['platform'] = 'Linux';
            elseif (strpos($navigator_user_agent, 'mac')) :
                $_SESSION["info"]['browser']['platform'] = 'Mac';
            elseif (strpos($navigator_user_agent, 'win')) :
                $_SESSION["info"]['browser']['platform'] = 'Windows';
            endif;

            if (strpos($navigator_user_agent, "trident")) {
                $_SESSION["info"]['browser']['engine'] = TRIDENT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "trident/") + 8, 3));
            } elseif (strpos($navigator_user_agent, "webkit")) {
                $_SESSION["info"]['browser']['engine'] = WEBKIT;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "webkit/") + 7, 8));
            } elseif (strpos($navigator_user_agent, "presto")) {
                $_SESSION["info"]['browser']['engine'] = PRESTO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "presto/") + 6, 7));
            } elseif (strpos($navigator_user_agent, "gecko")) {
                $_SESSION["info"]['browser']['engine'] = GECKO;
                $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "gecko/") + 6, 9));
            } elseif (strpos($navigator_user_agent, "robot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "spider"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "bot"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "crawl"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "search"))
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            elseif (strpos($navigator_user_agent, "w3c_validator"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            elseif (strpos($navigator_user_agent, "jigsaw"))
                $_SESSION["info"]['browser']['engine'] = VALIDATOR;
            else {
                $_SESSION["info"]['browser']['engine'] = ROBOTS;
            }
            if ($_SESSION["info"]['browser']['engine'] == ROBOTS)
                return true;
        }
        return false;
    }

    public function isProxys() {
        $useheader = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy');
        $usehostbyaddr = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_hostbyaddr');
        $usebankip = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_bankip');
        if ($useheader) {
            $header = Mage::helper('affiliateplus/config')->getActionConfig('detect_proxy_header');
            $arrindex = explode(',', $header);
            $headerarr = Mage::getModel('affiliateplus/system_config_source_headerdetectproxy')->getOptionList();
            foreach ($arrindex as $index) {
                if (isset($_SERVER[$headerarr[$index]])) {
                    return TRUE;
                }
            }
        }
        if ($usebankip) {
            $arrbankip = explode(';', $usebankip);
            $ip = $_SERVER['REMOTE_ADDR'];
            foreach ($arrbankip as $bankip) {
                if (preg_match('/' . $bankip . '/', $ip, $match))
                    return TRUE;
            }
        }
        if ($usehostbyaddr) {
            $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($host != $_SERVER['REMOTE_ADDR'])
                return TRUE;
        }
        return FALSE;
    }

    public function exitedCookie($parameter=null) {
        $usecookie = Mage::helper('affiliateplus/config')->getActionConfig('detect_cookie');
        if (!$usecookie)
            return FALSE;
        $check = FALSE;
        $days = Mage::helper('affiliateplus/config')->getActionConfig('resetclickby');
        $cookie = Mage::app()->getCookie();
        $expiredTime = Mage::helper('affiliateplus/config')->getGeneralConfig('expired_time');
        $params = Mage::app()->getRequest()->getParams();
        $link = '';
        $account = '';
        //hainh 29-07-2014 
        if($parameter && isset($params[$parameter]) && $params[$parameter])       // Added by Adam to fix error
            $account = Mage::getModel('affiliateplus/account')->getCollection()->addFieldToFilter('account_id', $params[$parameter])->getFirstItem();
        if ($account && $account->getId())
            $params[$parameter] = $account->getIdentifyCode();
        //end editing
        foreach ($params as $param) {
            $link .=$param;
        }
        if ($expiredTime)
            $cookie->setLifeTime(intval($expiredTime) * 86400);
        $date = New DateTime(now());
        $date->modify(-$days . 'days');
        $datemodifyreset = $date->format('Y-m-d');
        $datenow = date('Y-m-d');
        $dateset = $cookie->get($link);
        if ($datemodifyreset <= $dateset && $dateset) {
            $check = TRUE;
        } else {
            $cookie->set($link, $datenow);
        }
        return $check;
    }

    /*
     * * affiliate type is profit
     * */

    public function affiliateTypeIsProfit() {
        if (Mage::helper('affiliateplus/config')->getCommissionConfig('affiliate_type') == 'profit')
            return true;
        return false;
    }

    /**
     * render price to order currency
     * @param type $value
     * @param type $store
     * @return string
     */
    public function renderCurrency($value, $store) {
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrency()->getCode();
        $storeCurrencyCode = $store->getCurrentCurrencyCode();
        if ($baseCurrencyCode == $storeCurrencyCode)
            return '';
        else
            return '<br/>[' . $store->convertPrice($value, true, true) . ']';
    }

    /**
     * @author Adam
     * @date    28/07/2014
     * @return boolean
     */
    public function isAffiliateModuleEnabled() {
        $storeId = Mage::app()->getStore()->getId();
        if (Mage::helper('affiliateplus/config')->getGeneralConfig('enable', $storeId)) {
            return true;
        }
        return false;
    }
    /* Edit By Jack */
    public function isAllowUseCoupon($couponBySession){
        $isEnableCouponPlugin = Mage::getStoreConfig('affiliateplus/coupon/enable');
        if($isEnableCouponPlugin == 0 || !Mage::helper('core')->isModuleEnabled('Magestore_Affiliatepluscoupon')){
            return false;
        }
        else if($isEnableCouponPlugin == 1 && Mage::helper('core')->isModuleEnabled('Magestore_Affiliatepluscoupon')){
             $accountInfo = Mage::getModel('affiliatepluscoupon/coupon')->getAccountByCoupon($couponBySession);
             if($accountInfo->getStatus() && $accountInfo->getStatus() == 2)
                 return false;
        }
        return true;
    }
    public function isAdmin() {
        // Changed By Adam 28/07/2014
        if (!Mage::helper('affiliateplus')->isAffiliateModuleEnabled())
            return;
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if (Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        }

        return false;
    }
    public function isEditOrder($orderId, $couponCodeBySession,$dataProcessing){
        if(isset($dataProcessing['customer_id']))
            $customerId = $dataProcessing['customer_id'];
        else
            $customerId = '';
        if(isset($dataProcessing['program_id']))
            $programId = $dataProcessing['program_id'];
        else
            $programId = '';
        if(isset($dataProcessing['program_name']))
            $programName = $dataProcessing['program_name'];
        else
            $programName = '';
        if($orderId && !$couponCodeBySession && ($programId != '' || $programName != ''))
            return true;
        if((!$orderId && $customerId && !$couponCodeBySession))
            return true;
        return false;      
    }
    public function checkLifeTimeForOrderBackend($customerId){
        if(!$customerId)
            return;
        $account = '';
        if (Mage::getStoreConfig('affiliateplus/commission/life_time_sales')) {
            $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
            $tracksCollection->addFieldToFilter('customer_id', $customerId);
            $track = $tracksCollection->getFirstItem();
            if ($track && $track->getId()) {
                $account = Mage::getModel('affiliateplus/account')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($track->getAccountId());
                if($account && $account->getStatus() == 1){
                    return $account;
                }
            }
        }
        return $account;
    }
    public function checkLifeTime($customerId){
        if(!$customerId)
            return false;
        $isLifeTime = $this->checkLifeTimeForOrderBackend($customerId);
        if($isLifeTime)
            return true;
        return false;
    }
    public function getAccountAndProgramData($customerOrderId){
        if($this->checkLifeTimeForOrderBackend($customerOrderId)){ // check life time
            $account =  $this->checkLifeTimeForOrderBackend($customerOrderId);   
            $lifeTimeAff = true;
        }
        else{
            $account = '';
            $lifeTimeAff = false;
        }
        $programData = Mage::getSingleton('checkout/session')->getProgramData();
        if($programData && $programData != 'Affiliate Program'){  // if program isn't default program
            $programId = $programData->getProgramId();
            $programName = $programData->getName();
        }
        else if($programData == 'Affiliate Program'){   // set program_id = 0 for default program
            $programId = 0;
            $programName = 'Affiliate Program';
        }
        else{
            $programId = '';
            $programName = '';
        }
         $accountAndProgramData = new Varien_Object(array(
                'program_id' => '',
                'program_name' => '',
                'account' => $account,
                'lifetime_aff' => $lifeTimeAff,
            ));
        $accountAndProgramData->setAccount($account);
        $accountAndProgramData->setProgramId($programId);
        $accountAndProgramData->setProgramName($programName);
        $accountAndProgramData->setLifetimeAff($lifeTimeAff);
        return $accountAndProgramData;
    }
    public function processDataWhenEditOrder(){
                $result = array();
                $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();  
                $currentCouponCode = '';
                $session = Mage::getSingleton('checkout/session');
                $couponCodeBySession = $session->getAffiliateCouponCode();
                $baseAffiliateDiscount = 0;
                if($orderId){   // edit order
                    $currentOrderEdit = Mage::getModel('sales/order')->load($orderId); 
                    $orderIncrementId = $currentOrderEdit->getIncrementId();
                    /* get transaction was created by order editing */
                    $transactionAffiliate = Mage::getModel('affiliateplus/transaction')
                                            ->getCollection()
                                            ->addFieldToFilter('order_number',$orderIncrementId)
                                            ->getFirstItem();  
                    $currentCouponCode = $transactionAffiliate->getCouponCode();
                    $convertLifetime = false;
                    if($currentCouponCode && !$couponCodeBySession)
                        $convertLifetime = true;
                    /* end get transaction  */
                    $affId = $transactionAffiliate->getAccountId();
                    // check lifetime
                    $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId();
                     $accountInfo = $this->checkLifeTimeForOrderBackend($customerId);
                    if($accountInfo)
                        $defaultDiscount = true;
                    else{
                         if($currentCouponCode && !$couponCodeBySession){
                             return $result;
                         }
                            $defaultDiscount = false;
                            $programId = '';
                            $programInfo = new Varien_Object(array(
                                'program_id'	=> $programId,
                            ));
                            Mage::dispatchEvent('affiliateplus_program_transaction_data',array(
                                'order_number' => $orderIncrementId,
                                'program_info'	=> $programInfo,
                            ));
                            $programId = $programInfo->getProgramId();
                            if(!$programId){
                                $programId = $transactionAffiliate->getProgramId();
                                $programName = $transactionAffiliate->getProgramName();
                            }
                            //
                            $baseAffiliateDiscount = $currentOrderEdit->getAffiliateplusDiscount();  
                            $accountInfo = Mage::getModel('affiliateplus/account')->load($affId);
                            if($accountInfo->getStatus() == 2 && $couponCodeBySession == '')
                                return $result;
                    }
                    $currentAff = Mage::getModel('affiliateplus/account')->load($affId);
                    if(!$convertLifetime){
                        if($currentAff->getStatus() == 1) 
                            $accountInfo = $currentAff;
                        else
                            return $result;
                    }
                }
                else{
                    /* Edit By Jack - create order for lower affiliate */
                    $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId();
                    $accountInfo = $this->checkLifeTimeForOrderBackend($customerId);
                    if($accountInfo)
                        $defaultDiscount = true;
                    else
                        $defaultDiscount = false;
                    /* End Edit By Jack  */
                }
                if($couponCodeBySession && $this->isAdmin()){
                    $programAndAccountData = new Varien_Object(array(
			'program_id'	=> '',
                        'account_info' =>  '',
                        'program_name' => ''
                    ));
                    Mage::dispatchEvent('affiliateplus_coupon_get_program_data_and_account',array(
			'coupon_code_by_session' => $couponCodeBySession,
                        'program_and_account_data' => $programAndAccountData
                    )); 
                    $programId = $programAndAccountData->getProgramId();
                    $programName = $programAndAccountData->getProgramName();
                    $accountInfo = $programAndAccountData->getAccountInfo();
                    if($accountInfo && $accountInfo->getStatus() != 1)
                        return $result;
                }
                if(isset($currentCouponCode))
                    $result['current_couponcode'] = $currentCouponCode;
                if(isset($programId))
                     $result['program_id'] = $programId;
                if(isset($programName))
                     $result['program_name'] = $programName;
                if(isset($accountInfo))
                     $result['account_info'] = $accountInfo;
                if(isset($baseAffiliateDiscount))
                     $result['base_affiliate_discount'] = $baseAffiliateDiscount;
                if(isset($customerId))
                     $result['customer_id'] = $customerId;
                if(isset($defaultDiscount))
                     $result['default_discount'] = $defaultDiscount;
                return $result;
        }
        public function showAffiliateDiscount($orderId){
            if($orderId){
                $currentOrderEdit = Mage::getModel('sales/order')->load($orderId);
                $baseAffiliateDiscount = $currentOrderEdit->getAffiliateplusDiscount(); 
                $currentShippingMethod = $currentOrderEdit->getData('shipping_method');
            }
            else{
                $currentShippingMethod = Mage::getSingleton('adminhtml/session_quote')
                                        ->getQuote()
                                        ->getShippingAddress()->getShippingMethod();
            }
            if(!$currentShippingMethod) {
                $currentShippingMethod = '';
                return false;// Changed By Adam (13/08/2015): can't create order because can't load the shipping method.
            }
            
            /* reload session to show Affiliate Discount - Edit By Jack */
            echo Mage::app()->getLayout()
                            ->createBlock('core/template')
                            ->setId($currentShippingMethod)
                            ->setTemplate('affiliateplus/js/updatediscount.phtml')
                            ->toHtml();
            /* end reload session */
        }
    /* */

    public function refineUrlKey($keyshop) {
        for($i=0;$i<5;$i++)
        {
            $urlKey = str_replace("  "," ",$keyshop);
        }
        $chars = array(
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        );
        $newUrlKey = strtr($keyshop, $chars);
        $newUrlKey = str_replace(" ","-",$newUrlKey);
        $newUrlKey = htmlspecialchars(strtolower($newUrlKey));
        $newUrlKey = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $newUrlKey);
        return $newUrlKey;
    }

    /**
     * Added By Adam (27/08/2016): create transaction from the existed order.
     * @return array
     */
    public function getAccountList()
    {
        $list = array();
        $accounts = Mage::getModel('affiliateplus/account')->getCollection()
            ->addFieldToFilter('status', 1);
        foreach($accounts as $account){
            $list[$account->getEmail()] = $account->getName().' - '.$account->getEmail();
        }
        return $list;
    }

    /* Customize by Adam (27/08/2016): add customer to affiliate lifetime manually */
    protected function checkLifetimeCustomer($customer='', $affiliateId, $remove=false) {
        if($customer) {
            $tracksCollection = Mage::getResourceModel('affiliateplus/tracking_collection');
            $tracksCollection->addFieldToFilter('customer_email', $customer)
            ;
            if($remove) {
                $tracksCollection->addFieldToFilter('account_id', $affiliateId);
            }
            if($tracksCollection->getSize())
                return $tracksCollection;
            return false;
        }
        return false;
    }

    public function addLifetimeCustomers($addLifetimeCustomers, $affiliateId){
        if($addLifetimeCustomers) {
            $arrAddLifetimeCustomers = explode(',', $addLifetimeCustomers);
            foreach($arrAddLifetimeCustomers as $customer) {
                $customer = trim($customer);
                if (Zend_Validate::is($customer, 'EmailAddress')) {
                    if(!$this->checkLifetimeCustomer($customer, $affiliateId)) {
                        $model = Mage::getModel('affiliateplus/tracking');
                        try{
                            $model->setId(null)
                                ->setAccountId($affiliateId)
                                ->setCustomerEmail($customer)
                                ->setCreatedTime(now())
                                ->save();
                        } catch(Exception $e) {
                            print_r($e->getMessage());die('d');
                        }
                    }
                }
            }
        }
    }

    public function removeLifetimeCustomers($removeLifetimeCustomers, $affiliateId){
        if($removeLifetimeCustomers) {
            $arrRemoveLifetimeCustomers = explode(',', $removeLifetimeCustomers);
            foreach($arrRemoveLifetimeCustomers as $customer) {
                $customer = trim($customer);
                if (Zend_Validate::is($customer, 'EmailAddress')) {
                    if($tracikingCustomer = $this->checkLifetimeCustomer($customer, $affiliateId, true)) {
                        $tracikingCustomer->getFirstItem()
                            ->delete();
                    }
                }
            }
        }
    }
    /* Customize by Adam (27/08/2016): add customer to affiliate lifetime manually */

    /*Added By Adam (27/08/2016): change the balance manually, create transactioin to log*/
    public function addTransaction($account_id, $account_name, $account_email, $commission, $storeId) {
        $modelProgram = Mage::getModel('affiliateplus/transaction');
        $modelProgram->setAccountId($account_id)
            ->setAccountName($account_name)
            ->setAccountEmail($account_email)
            ->setCommission($commission)
            ->setStoreId($storeId)
            ->setCreatedTime(now())
            ->setStatus('1')
            ->setType(10)
            ->save();

    }

    /**
     * Added By Adam (31/08/2016): check if the product is already assign to an affiliate.
     * Check
     * @param $accountId
     * @param $productId
     * @return bool
     */
    public function checkExistAccountProduct($accountId = null, $productId = null){
        $model = Mage::getModel('affiliateplus/accountproduct')->getCollection();
        if($accountId)
            $model->addFieldToFilter('account_id', $accountId);
        if($productId)
            $model->addFieldToFilter('product_id', $productId);

        if($model->getSize()){
            return true;
        }
        return false;
    }
}
