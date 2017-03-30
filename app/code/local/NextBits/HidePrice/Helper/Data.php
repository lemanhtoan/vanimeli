<?php
class NextBits_HidePrice_Helper_Data extends NextBits_HidePrice_Helper_Core
{
    const CONFIG_EXTENSION_ACTIVE = 'hideprice/requirelogin/active';
    const CONFIG_EXTENSION_PRICE_BLOCKS = 'hideprice/requirelogin/priceblocks';
    const CONFIG_EXTENSION_LOGIN_MESSAGE = 'hideprice/requirelogin/login_message';

    protected $bExtensionActive;
    protected $sLoginMessage;
    protected $bExtensionActiveByCategory;
    protected $bExtensionActiveByCustomerGroup;
    protected $aPriceBlockClassNames;
    protected $aAddToCartBlockLayoutNames = array(
        'product.info.addtocart'
    );
     public function isExtensionActive()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE, 'bExtensionActive');
    } 
    public function getLoginMessage()
    {
        return $this->getStoreConfig(self::CONFIG_EXTENSION_LOGIN_MESSAGE, 'sLoginMessage');
    }
    protected function isExtensionActiveByCategory()
    {
        if ($this->bExtensionActiveByCategory === null) {
            $this->bExtensionActiveByCategory = Mage::helper(
                'hideprice/category'
            )->isExtensionActivatedByCategory();
        }
        return $this->bExtensionActiveByCategory;
    }
    protected function isExtensionActivatedByCustomerGroup()
    {
        if ($this->bExtensionActiveByCustomerGroup === null) {
            $this->bExtensionActiveByCustomerGroup = Mage::helper(
                'hideprice/customer'
            )->isExtensionActivatedByCustomerGroup();
        }
        return $this->bExtensionActiveByCustomerGroup;
    }
    public function isBlockPriceBlock($oBlock)
    {
        $aPriceBlockClassNames = $this->getPriceBlocks();
        return in_array(get_class($oBlock), $aPriceBlockClassNames);
    }
    public function isBlockAddToCartBlock($oBlock)
    {
        $aAddToCartBlockClassNames = $this->getAddToCartBlockLayoutNames();
        return in_array($oBlock->getNameInLayout(), $aAddToCartBlockClassNames);
    }
    public function isAddToCartBlockAndHidden($oBlock)
    {
        if ($this->isBlockAddToCartBlock($oBlock)) {
            $oProduct = $oBlock->getProduct();
            if ($this->isProductActive($oProduct) === false) {
                return true;
            }
        }
        return false;
    }
    public function isProductActive(Mage_Catalog_Model_Product $oProduct)
    {
        $bIsProductActive = true;
        if ($this->isExtensionActive() === true) {
            $bCheckCategory      = $this->isExtensionActiveByCategory();
            $bCheckUser          = $this->isExtensionActivatedByCustomerGroup();
            $bIsCustomerLoggedIn = $this->isCustomerLoggedIn();

            $oCategoryHelper = Mage::helper('hideprice/category');
            $oCustomerHelper = Mage::helper('hideprice/customer');

            $bIsCategoryEnabled      = $oCategoryHelper->isCategoryActiveByProduct($oProduct);
            $bIsCustomerGroupEnabled = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bIsProductActive = !($bIsCategoryEnabled && $bIsCustomerGroupEnabled);
            } elseif ($bCheckUser) {
                $bIsProductActive = !$bIsCustomerGroupEnabled;
            } elseif ($bCheckCategory) {
                if ($bIsCustomerLoggedIn) {
                    $bIsProductActive = true;
                } else {
                    $bIsProductActive = !$bIsCategoryEnabled;
                }
            } else {
                $bIsProductActive = $bIsCustomerLoggedIn;
            }
        }
        return $bIsProductActive;
    }
    public function hasEnabledCategories($aCategoryIds)
    {
        $bHasCategories = false;
        if ($this->isExtensionActive() === true) {
            $bCheckCategory      = $this->isExtensionActiveByCategory();
            $bCheckUser          = $this->isExtensionActivatedByCustomerGroup();
            $bIsCustomerLoggedIn = $this->isCustomerLoggedIn();
            $oCategoryHelper = Mage::helper('hideprice/category');
            $oCustomerHelper = Mage::helper('hideprice/customer');
            $bHasActiveCategories = $oCategoryHelper->hasActiveCategory($aCategoryIds);
            $bIsUserGroupActive   = $oCustomerHelper->isCustomerGroupActive();

            if ($bCheckCategory && $bCheckUser) {
                $bHasCategories = $bHasActiveCategories && $bIsUserGroupActive;
            } elseif ($bCheckUser) {
                $bHasCategories = $bIsUserGroupActive;
            } elseif ($bCheckCategory) {
                if ($bIsCustomerLoggedIn) {
                    $bHasCategories = false;
                } else {
                    $bHasCategories = $bHasActiveCategories;
                }
            } else {
                $bHasCategories = !$bIsCustomerLoggedIn;
            }
        }
        return $bHasCategories;
    }
    protected function isCustomerLoggedIn()
    {
        return Mage::helper('hideprice/customer')->isCustomerLoggedIn();
    }
    protected function getPriceBlocks()
    {
        if ($this->aPriceBlockClassNames === null) {
            $this->aPriceBlockClassNames = Mage::getStoreConfig(self::CONFIG_EXTENSION_PRICE_BLOCKS);
        }
        return $this->aPriceBlockClassNames;
    }
    protected function getAddToCartBlockLayoutNames()
    {
        return $this->aAddToCartBlockLayoutNames;
    }
}