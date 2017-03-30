<?php
class NextBits_LoginCatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_moduleActive = null;

    public function getConfig($key, $store = null)
    {
        $path = 'catalog_login/logincatalog/' . $key;
        return Mage::getStoreConfig($path, $store);
    }

    public function moduleActive()
    {
        if ('' == Mage::app()->getRequest()->getRequestUri()) {
            return false;
        }

        if (null !== $this->getModuleActiveFlag()) {
            return $this->getModuleActiveFlag();
        }
        return !(bool)$this->getConfig('disable_ext');
    }

    public function setModuleActive($state = true)
    {
        $this->_moduleActive = $state;
        return $this;
    }

    public function resetActivationState()
    {
        $this->_moduleActive = null;
        return $this;
    }

    public function getModuleActiveFlag()
    {
        return $this->_moduleActive;
    }

    public function shouldHideCategoryNavigation()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()
                && $this->moduleActive()
                && $this->getConfig('hide_categories')
        ) {
            return true;
        } else {
            return false;
        }
    }
}