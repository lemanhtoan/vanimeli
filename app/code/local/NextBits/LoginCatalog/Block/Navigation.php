<?php
class NextBits_LoginCatalog_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    protected function _construct()
    {
        $this->setData('module_name', 'Mage_Catalog');
        parent::_construct();
    }
    public function getCacheKey()
    {
        $session = Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            $customerGroupId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
        } else {
            $customerGroupId = $session->getCustomerGroupId();
        }
        return parent::getCacheKey() . $customerGroupId;
    }
    protected function _checkHideNavigation()
    {
        return Mage::helper('logincatalog')->shouldHideCategoryNavigation();
    }
    public function drawItem($category, $level = 0, $last = false)
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::drawItem($category, $level, $last);
    }
    public function drawOpenCategoryItem($category)
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::drawOpenCategoryItem($category);
    }
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        if ($this->_checkHideNavigation()) {
            return '';
        }
        return parent::renderCategoriesMenuHtml($level, $outermostItemClass, $childrenWrapClass);
    }
}