<?php
class NextBits_HidePrice_Helper_Customer extends NextBits_HidePrice_Helper_Core
{
   const CONFIG_EXTENSION_REQUIRES_LOGIN = 'hideprice/requirelogin/requirelogin';
   const CONFIG_EXTENSION_ACTIVE_BY_CUSTOMER_GROUP = 'hideprice/requirelogin/activebycustomer';    const CONFIG_EXTENSION_ACTIVE_CUSTOMER_GROUPS = 'hideprice/requirelogin/activecustomers';
   //const CONFIG_EXTENSION_ACTIVE_CUSTOMER_GLOBALLY = 'hideprice/generalsettings/activecustomers';

    protected $bLoginRequired;

    protected $bExtensionActiveByCustomerGroup;

    protected $aActivatedCustomerGroupIds = array();

     public function isCustomerLoggedIn()
    {
        if (Mage::helper('customer')->isLoggedIn() === true) {
           
            if ($this->isCustomerActivationGlobal()) {
                return true;
            }
            $oCustomerSession = Mage::getSingleton('customer/session');
            $oCustomer = $oCustomerSession->getCustomer();
            if($this->isCustomerActiveForStore($oCustomer)) {
                return true;
            }
        }
        return false;
    }
    private function isCustomerActivationGlobal()
    {
        return true;
    } 

     private function isCustomerActiveForStore(Mage_Customer_Model_Customer $oCustomer)
    {
        
        if ($this->isCustomerAdminCreation($oCustomer)) {
            return true;
        }

        $iUserStoreId    = $oCustomer->getStoreId();
        $iCurrentStoreId = Mage::app()->getStore()->getId();

        return $iUserStoreId === $iCurrentStoreId;
    }

    private function isCustomerAdminCreation(Mage_Customer_Model_Customer $oCustomer)
    {
        return $oCustomer->getStoreId() === Mage_Core_Model_App::ADMIN_STORE_ID;
    } 

    
    public function isCustomerGroupActive()
    {
        $oCustomerSession        = Mage::getModel('customer/session');
        $iCurrentCustomerGroupId = $oCustomerSession->getCustomerGroupId();
        $aActiveCustomerGroupIds = $this->getActivatedCustomerGroupIds();

        return in_array($iCurrentCustomerGroupId, $aActiveCustomerGroupIds);
    }

    public function isLoginRequired()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_REQUIRES_LOGIN, 'bLoginRequired');
    }

    public function isExtensionActivatedByCustomerGroup()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE_BY_CUSTOMER_GROUP, 'bExtensionActiveByCustomerGroup');
    }
    private function getActivatedCustomerGroupIds()
    {
        if (empty($this->aActivatedCustomerGroupIds)) {
            $sActivatedCustomerGroups         = Mage::getStoreConfig(self::CONFIG_EXTENSION_ACTIVE_CUSTOMER_GROUPS);
            $this->aActivatedCustomerGroupIds = explode(',', $sActivatedCustomerGroups);

            $this->aActivatedCustomerGroupIds[] = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        }
        return $this->aActivatedCustomerGroupIds;
    }
}