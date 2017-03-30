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
class Ves_ProductList_Block_Widget_Carousel extends Ves_ProductList_Block_List implements Mage_Widget_Block_Interface
{

    public function __construct($attributes = array()) {
        $this->convertAttributesToConfig($attributes);

        if(!$this->getConfig('show')) {
            return;
        }

        if ($this->hasData("template")) {
            $my_template = $this->getData("template");
        }
        elseif (isset($attributes['template']) && $attributes['template']) {
            $my_template = $attributes['template'];
        }
        elseif (isset($attributes['block_template']) && $attributes['block_template']) {
            $my_template = $attributes['block_template'];
        }
        elseif( $this->getConfig('block_template')) {
            $my_template = $this->getConfig('block_template');
        }
        else {
            $carousel_mode = $this->getConfig("carousel_mode", "owl");

            if($carousel_mode == "bootstrap") {
                $my_template = "ves/productlist/widget/carousel_bootstrap.phtml";
            } else {
                $my_template = "ves/productlist/widget/carousel.phtml";
            }
            
        }

        $this->setTemplate($my_template);

        parent::__construct($attributes);

        /*Cache Block*/
        $enable_cache = $this->getConfig("enable_cache", 1 );
        if(!$enable_cache) {
          $cache_lifetime = null;
        } else {
          $cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
          $cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
        }

        $this->addData(array('cache_lifetime' => $cache_lifetime));

        $this->addCacheTag(array(
          Mage_Core_Model_Store::CACHE_TAG,
          Mage_Cms_Model_Block::CACHE_TAG,
          Ves_ProductList_Model_Rule::CACHE_WIDGET_CAROUSEL
        ));
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $ruleId = $this->getConfig("rule_id");
        return array(
           'VES_PRODUCTLIST_CAROUSEL',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           $ruleId,
           'template' => $this->getTemplate(),
        );
    }
    public function _initCarouselData() {
        $carousels = array();
        $ruleId = $this->getConfig("rule_id");
        $limit = $this->getConfig('limit',6);
        $rule = $collection = null;
        if ($ruleId) {
            $rule = Mage::getModel('productlist/rule')->load($ruleId);
            $ruleProductTable = Mage::getSingleton('core/resource')->getTableName('productlist/rule_product');

            $source_type = $rule->getSourceType();
            if(in_array($source_type, array("most_viewed", "best_seller", "top_rate")) ) {
                $collection = Mage::getResourceModel('reports/product_collection');
            } else {
                $collection = Mage::getResourceModel('catalog/product_collection');
            }

            $collection->addAttributeToSelect('*')
                        ->addMinimalPrice()
                        ->addFinalPrice()
                        ->addTaxPercents();

            /*Apply data source type*/
            $collection = $rule->applySourceType( $collection );

            $collection->getSelect()->where('t2.rule_id = (?)',$ruleId)->join(array('t2'=>$ruleProductTable),'e.entity_id = t2.rule_product_id');
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
            $collection->setPageSize($limit)->setCurPage(1);
        }

        $this->setRuleModel($rule);
        $this->setDataItems($collection);
    }
    public function _toHtml() {
        if(!$this->getConfig('show')) {
            return;
        }
        return parent::_toHtml();
    }
}