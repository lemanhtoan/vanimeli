<?php

/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Productlist extends Mage_Catalog_Block_Product_List
{
    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;

    /**
     * List of available order fields
     *
     * @var array
     */
    protected $_availableOrder = array();

    protected $_current_rule = null;

    /**
     * Retrieve Layer object
     *
     * @return Ves_ProductList_Model_Layer
     */
    public function getLayer() {
        return Mage::getSingleton('productlist/layer');
    }

    public function convertSortField( $sort_by = "") {
        $target_fields = array("best" => "t2.position", "position" => "t2.position");
        return isset($target_fields[$sort_by])?$target_fields[$sort_by]: $sort_by;
    }
    /**
     * Retrive loaded rule collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection() {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();

            $collection = $layer->getProductCollection();
            $this->setCollection($collection);
            $this->_productCollection = $collection;

            $this->prepareSortableFieldsByRule( $this->getRule() );
        }

        return $this->_productCollection;
    }

    /**
     * Get default sort direction
     *
     * @return string
     */
    public function getDefaultDirection() {
        $default = 'desc';
        $rule = $this->getRule();
        if ($rule->getData('product_direction') != '') {
            $default = $rule->getData('product_direction');
        }

        return $default;
    }

    /**
     * Get default order
     *
     * @return string
     */
    public function getDefaultOrder() {
        $default = 'rule_product_position';
        $rule = $this->getRule();
        if ($rule->getData('product_order') != '') {
            $default = $rule->getData('product_order');
        }

        return $default;
    }

    protected function _beforeToHtml() {
        $toolbar = $this->getToolbarBlock();
        $collection = $this->_getProductCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }

        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);

        Mage::dispatchEvent('catalog_block_product_list_collection', array('collection' => $this->_getProductCollection()));

        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Get current rule filter
     *
     * @return Ves_ProductList_Model_Rule
     */
    protected function getRule() {
        return Mage::registry('current_rule');
    }

    protected function _prepareLayout() {
        $rule = $this->getRule();
        $title = '';
        if ($rule->getPageTitle() != '') {
            $title = $rule->getPageTitle();
        }
        elseif ($rule->getTitle()) {
            $title = $rule->getTitle();
        }
        $this->getLayout()->getBlock('head')->setTitle($title);

        if ($rule->getMetaKeywords()) {
            $keywords = $rule->getMetaKeywords();
            $this->getLayout()->getBlock('head')->setKeywords($keywords);
        }

        if ($rule->getMetaDescription()) {
            $description = $rule->getMetaDescription();
            $this->getLayout()->getBlock('head')->setDescription($description);
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare Sort By fields from Category Data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFieldsByRule($rule) {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($rule->getAvailableSortByOptions());
        }

        $availableOrders = $this->getAvailableOrders();

        if (!$this->getSortBy()) {
            if ($ruleSortBy = $rule->getProductOrder()) {
                if (!$availableOrders) {
                    $availableOrders = $rule->getDefaultSortBy();
                }

                if (isset($availableOrders[$ruleSortBy])) {
                    $this->setSortBy($ruleSortBy);
                }
            }
        }

        return $this;
    }
}