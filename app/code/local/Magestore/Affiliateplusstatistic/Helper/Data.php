<?php

class Magestore_Affiliateplusstatistic_Helper_Data extends Mage_Core_Helper_Abstract {
    /* hainh edit 25-04-2014 */

    public function disableMenu() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return false;
        
        if (!Mage::getStoreConfig('affiliateplus/statistic/enable')) {
            return true;
        }
        return Mage::helper('affiliateplus/account')->accountNotLogin();
    }
}
