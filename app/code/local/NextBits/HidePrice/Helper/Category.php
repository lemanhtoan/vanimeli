<?php
class NextBits_HidePrice_Helper_Category extends NextBits_HidePrice_Helper_Core
{
    const CONFIG_EXTENSION_ACTIVE_BY_CATEGORY = 'hideprice/requirelogin/activebycategory';
    const CONFIG_EXTENSION_ACTIVATED_CATEGORIES = 'hideprice/requirelogin/activecategories';
    protected $bExtensionActiveByCategory;
    public function isExtensionActivatedByCategory()
    {
        return $this->getStoreFlag(self::CONFIG_EXTENSION_ACTIVE_BY_CATEGORY, 'bExtensionActiveByCategory');
    }
    protected function getChildrenAndParentIds(Mage_Catalog_Model_Product $oProduct)
    {
        $oProductType             = $oProduct->getTypeInstance();
        $aLinkedProductIds        = $oProductType->getParentIdsByChild($oProduct->getId());
        $aChildProductIdsByGroups = $oProductType->getChildrenIds($oProduct->getId());
        foreach ($aChildProductIdsByGroups as $aChildProductIds) {
            $aLinkedProductIds = array_unique(array_merge($aLinkedProductIds, $aChildProductIds));
        }
        return $aLinkedProductIds;
    }
    public function isCategoryActiveByProduct(Mage_Catalog_Model_Product $oProduct)
    {
        $aCurrentCategories = $oProduct->getCategoryIds();
        $aLinkedProductIds = array();
        if ($oProduct->isSuper()) {
            $aLinkedProductIds = $this->getChildrenAndParentIds($oProduct);
        }
        if (!empty($aLinkedProductIds)) {
            $aCurrentCategories = $this->getAllCategoryIds($aLinkedProductIds, $aCurrentCategories);
        }
        if (!is_array($aCurrentCategories)) {
            $aCurrentCategories = array(
                $aCurrentCategories
            );
        }
        return $this->hasActiveCategory($aCurrentCategories);
    }
    public function hasActiveCategory($aCategoryIds)
    {
        $aActiveCategoryIds = $this->getActiveCategories();
        foreach ($aCategoryIds as $iCategoryId) {
            if (in_array($iCategoryId, $aActiveCategoryIds)) {
                return true;
            }
        }
        return false;
    }
    protected function getActiveCategories()
    {
        $aCurrentActiveCategories = $this->getExtensionActivatedCategoryIds();
        $aSubActiveCategories = $this->addCategoryChildren($aCurrentActiveCategories);
        return array_unique(array_merge($aCurrentActiveCategories, $aSubActiveCategories));
    }
    protected function getExtensionActivatedCategoryIds()
    {
       $sActivatedCategoryIds = Mage::getStoreConfig(self::CONFIG_EXTENSION_ACTIVATED_CATEGORIES);
        return explode(',', $sActivatedCategoryIds);
    }
    protected function addCategoryChildren($aCategoryIds)
    {
        $oCategoryResource = Mage::getResourceModel('catalog/category');
        $oAdapter          = $oCategoryResource->getReadConnection();
        $oSelect = $oAdapter->select();
        $oSelect->from(array('m' => $oCategoryResource->getEntityTable()), 'entity_id');
        foreach ($aCategoryIds as $iCategoryId) {
            $oSelect->orWhere($oAdapter->quoteIdentifier('path') . ' LIKE ?', '%/' . $iCategoryId . '/%');
        }
        return $oAdapter->fetchCol($oSelect);
    }
    protected function getAllCategoryIds($aProductIds, $aCurrentCategories)
    {
        $oProductResource = Mage::getResourceModel('catalog/product');
        $oAdapter         = $oProductResource->getReadConnection();
        $oSelect = $oAdapter->select();
        $oSelect->from($oProductResource->getTable('catalog/category_product'), 'category_id');
        $oSelect->where('product_id IN (?)', $aProductIds);
        return array_unique(array_merge($aCurrentCategories, $oAdapter->fetchCol($oSelect)));
    }
}