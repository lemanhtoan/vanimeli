<?php

class NextBits_HidePrice_Model_Observer
{
    protected static $iLastProductId = 0;
    protected $oHidePriceHelper;
    public function __construct()
    {
        $this->oHidePriceHelper = Mage::helper('hideprice');
    }
     protected function isExtensionActive()
    {
        return $this->oHidePriceHelper->isExtensionActive();
    } 
    public function catalogProductTypePrepareFullOptions(Varien_Event_Observer $oObserver)
    {
        if ($this->isExtensionActive()) {
            $oProduct = $this->getEventsProduct($oObserver);

            if ($this->oHidePriceHelper->isProductActive($oProduct) === false) {
                throw new Mage_Catalog_Exception($this->oHidePriceHelper->__('Your account is not allowed to access this store.'));
            }
        }
    }
    public function coreBlockAbstractToHtmlAfter(Varien_Event_Observer $oObserver)
    {
        if ($this->isExtensionActive()) {
            $oBlock     = $oObserver->getData('block');
            $oTransport = $oObserver->getData('transport');
            if ($this->isExactlyPriceBlock($oBlock)) {
                $this->transformPriceBlock($oBlock, $oTransport);
            }
            if ($this->isExactlyAddToCartBlock($oBlock)) {
                $oTransport->setHtml('');
            }
        }
    }
    public function controllerActionPredispatch(Varien_Event_Observer $oObserver)
    {
        if ($this->isExtensionActive()) {
            $oControllerAction = $oObserver->getData('controller_action');
            $oHidePriceCustomerHelper = Mage::helper('hideprice/customer');
            if ($oHidePriceCustomerHelper->isLoginRequired() == true && !$oHidePriceCustomerHelper->isCustomerLoggedIn()) {
                Mage::helper('hideprice/redirects')->performRedirect($oControllerAction);
            }
        }
    }
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $oObserver)
    {
        if ($this->isExtensionActive()) {
            $oBlock = $oObserver->getData('block');

            if ($oBlock instanceof Mage_Catalog_Block_Product_List_Toolbar) {
                $oBlock->removeOrderFromAvailableOrders('price');
            }
        }
    }
    public function coreLayoutBlockCreateAfter(Varien_Event_Observer $oObserver)
    {
        if ($this->isExtensionActive()) {
            $oBlock = $oObserver->getData('block');
            if ($oBlock instanceof Mage_Catalog_Block_Layer_View) {
                $aCategoryOptions = $this->getCategoryFilters($oBlock);

                if ($this->oHidePriceHelper->hasEnabledCategories($aCategoryOptions)) {
                    $this->removePriceFilter($oBlock);
                }
            }
        }
    }
    public function nextbitsHidePriceProductListCollectionLoadAfter(Varien_Event_Observer $oObserver)
    {
        $oProductCollection = $oObserver->getProductCollection();
        $oDummyOption       = Mage::getModel('catalog/product_option');
        foreach ($oProductCollection as $oProduct) {
            if ($this->oHidePriceHelper->isProductActive($oProduct) === false) {
                $oProduct->setRequiredOptions(array($oDummyOption));
            }
        }
        return $oProductCollection;
    }
    protected function isExactlyPriceBlock($oBlock)
    {
        return $oBlock && $this->oHidePriceHelper->isBlockPriceBlock($oBlock);
    }
    protected function isExactlyAddToCartBlock($oBlock)
    {
        return $this->oHidePriceHelper->isAddToCartBlockAndHidden($oBlock);
    }
    protected function getCategoryFilters($oBlock)
    {
        $oCategoryFilter  = $oBlock->getChild('category_filter');
        $aCategoryOptions = array();
        if ($oCategoryFilter instanceof Mage_Catalog_Block_Layer_Filter_Category) {
            $oCategories = $oCategoryFilter->getItems();
            foreach ($oCategories as $oCategory) {
                $iCategoryId        = $oCategory->getValue();
                $aCategoryOptions[] = $iCategoryId;
            }
            if (empty($aCategoryOptions)) {
                return $this->getDefaultCategoryOptions();
            }
        }
        return $aCategoryOptions;
    }
    protected function getDefaultCategoryOptions()
    {
        $aCategoryOptions = array();
        $oCategory = Mage::registry('current_category_filter');
        if ($oCategory === null) {
            $oCategory = Mage::registry('current_category');
            if ($oCategory === null) {
                $oCategory = Mage::getModel('catalog/category')->load(
                    Mage::app()->getStore()->getRootCategoryId()
                );
            }
        }
        $aCategoryOptions[] = $oCategory->getId();
        return $aCategoryOptions;
    }
    protected function removePriceFilter($oBlock)
    {
        $aFilterableAttributes    = $oBlock->getData('_filterable_attributes');
        $aNewFilterableAttributes = array();
        foreach ($aFilterableAttributes as $oFilterableAttribute) {
            if ($oFilterableAttribute->getAttributeCode() != 'price') {
                $aNewFilterableAttributes[] = $oFilterableAttribute;
            }
        }
        $oBlock->setData('_filterable_attributes', $aNewFilterableAttributes);
    }
    protected function setSymmetricsProductType(Mage_Catalog_Model_Product $oProduct)
    {
        if (
            Mage::helper('core')->isModuleEnabled('Symmetrics_TweaksGerman')
            && $oProduct->getTypeId() == 'bundle'
        ) {
            $oProduct->setTypeId('combined');
        }
    }
    protected function transformPriceBlock($oBlock, $oTransport)
    {
        $oProduct          = $oBlock->getProduct();
        $iCurrentProductId = $oProduct->getId();

        if ($this->oHidePriceHelper->isProductActive($oProduct) === false) {
            if ($iCurrentProductId !== self::$iLastProductId) {
                self::$iLastProductId = $iCurrentProductId;
                $oTransport->setHtml($this->oHidePriceHelper->getLoginMessage());
            } else {
                $oTransport->setHtml('');
            }
            $this->setSymmetricsProductType($oProduct);
        }
    }
    protected function getEventsProduct(Varien_Event_Observer $oObserver)
    {
        return $oObserver->getProduct();
    }
}