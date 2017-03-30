<?php
class NextBits_HidePrice_Helper_Core extends Mage_Core_Helper_Abstract
{
    public function getStoreFlag($sStoreFlagPath, $sStoreFlagAttribute)
    {
        return (bool)$this->getStoreConfig($sStoreFlagPath, $sStoreFlagAttribute);
    }
    public function getStoreConfig($sStoreConfigPath, $sStoreConfigAttribute)
    {
        if ($this->$sStoreConfigAttribute === null) {
            $this->$sStoreConfigAttribute = Mage::getStoreConfig($sStoreConfigPath);
        }
        return $this->$sStoreConfigAttribute;
    }
}