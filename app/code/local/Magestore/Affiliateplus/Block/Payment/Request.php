<?php
class Magestore_Affiliateplus_Block_Payment_Request extends Mage_Core_Block_Template
{
	/**
	 * Get Affiliate Payment Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Payment
	 */
	protected function _getPaymentHelper(){
		return Mage::helper('affiliateplus/payment');
	}
	
	public function _prepareLayout(){
		parent::_prepareLayout();
		
		$layout = $this->getLayout();
		$paymentMethods = $this->getAllPaymentMethod();
		foreach ($paymentMethods as $code => $method){
			$paymentMethodFormBlock = $layout->createBlock($method->getFormBlockType(),"payment_method_form_$code")->setPaymentMethod($method);
			$this->setChild("payment_method_form_$code",$paymentMethodFormBlock);
		}
		
		return $this;
    }
    
    public function getAllPaymentMethod(){
    	if (!$this->hasData('all_payment_method')){
    		$this->setData('all_payment_method',$this->_getPaymentHelper()->getAvailablePayment());
    	}
    	return $this->getData('all_payment_method');
    }
    
    public function getAmount(){
        if($this->getRequest()->getParam('amount'))
            return $this->getRequest()->getParam('amount');
        $paymentSession = Mage::getSingleton('affiliateplus/session')->getPayment();
        if($paymentSession)
            if($paymentSession->getAmount())
                return $paymentSession->getAmount();
    }
    
    /**
     * get Current Affiliate Account
     *
     * @return Magestore_Affiliateplus_Model_Account
     */
    public function getAccount(){
    	return Mage::getSingleton('affiliateplus/session')->getAccount();
    }
    
    public function getBalance(){
        /*Changed By Adam 15/09/2014: to fix the issue of request withdrawal when scope is website*/
        $balance = 0;
        if(Mage::getStoreConfig('affiliateplus/account/balance') == 'website') {
            $website = Mage::app()->getStore()->getWebsite();
            
            $stores = $website->getStores();
            
            foreach($stores as $store) {
                $account = Mage::getModel('affiliateplus/account')->setStoreId($store->getId())->load($this->getAccount()->getId());
                $balance += $account->getBalance();
            }
        } else {
            $balance = $this->getAccount()->getBalance();
        }
        $balance = Mage::app()->getStore()->convertPrice($balance);
        return floor($balance * 100) / 100;
    	return round(Mage::app()->getStore()->convertPrice($this->getAccount()->getBalance()),2);
    }
    
    public function getFormatedBalance(){
        /*Changed By Adam 15/09/2014: to fix the issue of request withdrawal when scope is website*/
        $balance = 0;
        if(Mage::getStoreConfig('affiliateplus/account/balance') == 'website') {
            $website = Mage::app()->getStore()->getWebsite();
            
            $stores = $website->getStores();
            
            foreach($stores as $store) {
                $account = Mage::getModel('affiliateplus/account')->setStoreId($store->getId())->load($this->getAccount()->getId());
                $balance += $account->getBalance();
            }
            return Mage::helper('core')->currency($balance);
        } else {
            return Mage::helper('core')->currency($this->getAccount()->getBalance());
        }
    }
    
    public function getFormActionUrl(){
        $url = $this->getUrl('affiliateplus/index/confirmRequest');
        return $url;
    }
    
    /**
     * get Tax rate when withdrawal
     * 
     * @return float
     */
    public function getTaxRate() {
        if (!$this->hasData('tax_rate')) {
            $this->setData('tax_rate', Mage::helper('affiliateplus/payment_tax')->getTaxRate());
        }
        return $this->getData('tax_rate');
    }
    
    public function includingFee() {
        return (Mage::getStoreConfig('affiliateplus/payment/who_pay_fees') != 'payer');
    }
    
    public function getPriceFormatJs() {
        $priceFormat = Mage::app()->getLocale()->getJsPriceFormat();
        return Mage::helper('core')->jsonEncode($priceFormat);
    }
    
    /*add by blanka*/
    /**
     * get default payment method
     * @return type
     */
    protected function _getDefaultPaymentMethod(){
        return Mage::getStoreConfig('affiliateplus/payment/default_method');
    }
    
    /**
     * check a method is default or not
     * @param type $code
     * @return boolean
     */
    public function methodSelected($code){
        if($code == $this->_getDefaultPaymentMethod()){
            return true;
        }
        return false;
    }
    /*end add by blanka*/
}
