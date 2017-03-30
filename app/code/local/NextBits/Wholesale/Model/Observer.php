<?php

class NextBits_Wholesale_Model_Observer extends Mage_Core_Model_Session_Abstract
{
  
	
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $oObserver)
    {
		$currentStore = Mage::app()->getStore()->getStoreId();
		$configStore = Mage::getStoreConfig('wholesale/general/wholesale_type_store');
		$currentWebsite = Mage::app()->getWebsite()->getId();
		$configWebsite = Mage::getStoreConfig('wholesale/general/wholesale_type_webs');
		$wholesaleType = Mage::getStoreConfig('wholesale/general/wholesale_type');
		$layout = $oObserver->getEvent()->getLayout();
		$configStoreArray = explode(",",$configStore);
		$configWebsiteArray = explode(",",$configWebsite);
		
		if(!Mage::getSingleton('customer/session')->isLoggedIn() && $wholesaleType != 'none'){
			if($wholesaleType == 'w-store'){
				if(in_array($currentStore,$configStoreArray)){
					$layout->getUpdate()->addHandle('wholesale_link');
				}
			}
		}
    }
	
	public function systemConfigSaveAfterObserver(Varien_Event_Observer $oObserver)
    {
		$configStore = Mage::getStoreConfig('wholesale/general/wholesale_type_store');
		$configWebsite = Mage::getStoreConfig('wholesale/general/wholesale_type_webs');
		$hidePricecatalog = Mage::getStoreConfig('wholesale/general/wholesale_hide_pricecatalog');
		$confCustomerActivation = Mage::getStoreConfig('wholesale/general/wholesale_customer_activation');
		$customerGroup = Mage::getStoreConfig('wholesale/general/wholesale_customer_group');
		$storewebcheck = Mage::getStoreConfig('wholesale/general/wholesale_type');
		$configStoreArray = explode(",",$configStore);
		$configWebsiteArray = explode(",",$configWebsite);
		$config = new Mage_Core_Model_Config();
		if($storewebcheck == 'w-store' && $configStore){
			if(count($configStoreArray)){
				for($i=0;$i<count($configStoreArray);$i++){
					if($hidePricecatalog == 1){
						$config->saveConfig('hideprice/requirelogin/active', 1, 'stores', $configStoreArray[$i]);
					}
					else if($hidePricecatalog == 2){
						$config->saveConfig('catalog_login/logincatalog/disable_ext', 0, 'stores', $configStoreArray[$i]);
					}
					
					if($confCustomerActivation == 1){
						$config->saveConfig('customeractive/customeractivation_group/disable_ext', 0, 'stores', $configStoreArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/require_activation_groups', $customerGroup, 'stores', $configStoreArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/require_activation_for_specific_groups', 1, 'stores', $configStoreArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/always_send_admin_email', 1, 'stores', $configStoreArray[$i]);
					}
				}
			}
		}
		elseif($storewebcheck == 'w-webs' && $configWebsite){
			if(count($configWebsiteArray)){
				for($i=0;$i<count($configWebsiteArray);$i++){
					if($hidePricecatalog == 1){
						$config->saveConfig('hideprice/requirelogin/active', 1, 'websites', $configWebsiteArray[$i]);
					}
					else if($hidePricecatalog == 2){
						$config->saveConfig('catalog_login/logincatalog/disable_ext', 0, 'websites', $configWebsiteArray[$i]);
					}
					
					if($confCustomerActivation == 1){
						$config->saveConfig('customeractive/customeractivation_group/disable_ext', 0, 'websites', $configWebsiteArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/require_activation_groups', $customerGroup, 'websites', $configWebsiteArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/require_activation_for_specific_groups', 1, 'websites', $configWebsiteArray[$i]);
						$config->saveConfig('customeractive/customeractivation_group/always_send_admin_email', 1, 'websites', $configWebsiteArray[$i]);
					}
				}
			}
		}
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
		Mage::getConfig()->cleanCache();
    }
	
	public function customerSaveBefore( $observer )
	{
		$customerGroupId = Mage::getStoreConfig('wholesale/general/wholesale_customer_group');
		$currentStore = Mage::app()->getStore()->getStoreId();
		$configStore = Mage::getStoreConfig('wholesale/general/wholesale_type_store');
		$currentWebsite = Mage::app()->getWebsite()->getId();
		$configWebsite = Mage::getStoreConfig('wholesale/general/wholesale_type_webs');
		$wholesaleType = Mage::getStoreConfig('wholesale/general/wholesale_type');
		$configStoreArray = explode(",",$configStore);
		$configWebsiteArray = explode(",",$configWebsite);
		$CustomerActivated = Mage::getStoreConfig('customeractive/customeractivation_group/require_activation_for_specific_groups');
		if(Mage::app()->getRequest()->getPost('storeweb')){
			if($wholesaleType == 'w-store'){
				if(in_array($currentStore,$configStoreArray)){
					try {
						$customer = $observer->getCustomer();
						if($customerGroupId){
							$customer->setData( 'group_id',$customerGroupId );
							if($CustomerActivated)
							{
								$customer->setCustomerActivated(0);
							}
							
						}
							 
					} catch ( Exception $e ) {
						Mage::log( "customer_save_before observer failed: " . $e->getMessage() );
					}
				}
			}
			elseif($wholesaleType == 'w-webs'){
				if(in_array($currentWebsite,$configWebsiteArray)){
					try {
						$customer = $observer->getCustomer();
						if($customerGroupId){
							$customer->setData( 'group_id',$customerGroupId );
							if($CustomerActivated)
							{
								$customer->setCustomerActivated(0);
							}
						}
							 
					} catch ( Exception $e ) {
						Mage::log( "customer_save_before observer failed: " . $e->getMessage() );
					}
				}
			}
		}
	}
	
	public function hookToControllerActionPreDispatch($observer)
    {
        $currentStore = Mage::app()->getStore()->getStoreId();
		$configStore = Mage::getStoreConfig('wholesale/general/wholesale_type_store');
		$currentWebsite = Mage::app()->getWebsite()->getId();
		$configWebsite = Mage::getStoreConfig('wholesale/general/wholesale_type_webs');
		$wholesaleType = Mage::getStoreConfig('wholesale/general/wholesale_type');
		$configStoreArray = explode(",",$configStore);
		$configWebsiteArray = explode(",",$configWebsite);
		
        if($observer->getEvent()->getControllerAction()->getFullActionName() == 'customer_account_create'){
			if(!Mage::getSingleton('customer/session')->isLoggedIn() && $wholesaleType != 'none'){
				if($wholesaleType == 'w-webs'){
					if(in_array($currentWebsite,$configWebsiteArray)){
						Mage::app()->getResponse()->setRedirect(Mage::getUrl("wholesale/account/create"));
					}
				}
			}
		}
	}
}