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
class Ves_ProductList_Block_Widget_Categorytab extends Ves_ProductList_Block_List implements Mage_Widget_Block_Interface
{
    /**
     * Initialize Tab template
     */
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
            $tab_layout_type = $this->getConfig("tab_layout_type", "tabs");
            $tab_layout_type = empty($tab_layout_type)?"tabs":$tab_layout_type;

            $my_template = "ves/productlist/widget/category_tabs/{$tab_layout_type}.phtml";
        }
        $this->_config = $this->getData();
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
          Ves_ProductList_Model_Rule::CACHE_WIDGET_CATEGORYTAB
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
           'VES_PRODUCTLIST_CATEGORYTAB',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           $ruleId,
           'template' => $this->getTemplate(),
        );
    }

    public function _toHtml() {
        if(!$this->getConfig('show')) {
            return;
        }

        $pretext = $this->getConfig('productlist_pretext');

        $cms_block_id = $this->getConfig('cmsblock');
        $cms = "";
        if($cms_block_id){
            $cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
        }
        if($cms) {
            $this->assign( 'productlist_pretext', $cms );
        }elseif($pretext){
            $pretext = html_entity_decode(base64_decode($pretext));
            $this->setData('productlist_pretext',$pretext);
        }
        return parent::_toHtml();
    }

    public function getTabs(){
        if(stristr($this->_config['catsid'], ',') === FALSE) {
            $arr_catsid =  array(0 => $this->_config['catsid']);
        }else{
            $arr_catsid = explode(",", $this->_config['catsid']);
        }
        $config = $this->getData();
        $config['limit_item'] = isset($config['limit_item'])?$config['limit_item']:$this->getConfig('product_number',12);

        if( !empty($arr_catsid)){
            $data = array();

            foreach ($arr_catsid as $k => $v) {
                $cat = Mage::getModel('catalog/category')->load($v);
                $data[ $k ]["mainImage"] = "";
                $data[ $k ]["link"] = $cat->getUrl();
                $data[ $k ]["title"] = $cat->getName();
                $data[ $k ]["product_count"] = $cat->getProductCount();
                $data[ $k ]["products"] = array();
                $data[ $k ]["category"] = $cat;
                $data[ $k ]['blockProducts'] = '';
                $config['catsid'] = $v;

                if( ( $this->getConfig('tab_layout_type') == 'ajax-carousel' || $this->getConfig('tab_layout_type') == 'ajaxtab-sub-carousel' || $this->getConfig('tab_layout_type') == 'ajax-append') && $this->getConfig('is_ajax', 0)){
                    $config['itemspage'] = $this->getConfig('product_number',12);
                    $list = Mage::getModel('productlist/product')->getListProducts($config);
                } 

                if( $this->getConfig('tab_layout_type') == 'ajax-loadmore'  && $this->getConfig('is_ajax', 0)){

                    $list = Mage::getModel('productlist/product')->getListProducts($config);

                }
                
                if( $this->getConfig('tab_layout_type') == 'default' || $this->getConfig('tab_layout_type') == 'bootstrap_tabs' || $this->getConfig('tab_layout_type') == 'bootstrap_tabs_carousel' || "" == $this->getConfig('tab_layout_type') || false == $this->getConfig('tab_layout_type')){
                    $config['itemspage'] = $this->getConfig('product_number',12);
                    $list = Mage::getModel('productlist/product')->getListProducts($config);

                } 

                if(isset($list['products'])){
                    $data[ $k ]["products"] = $list['products'];
                }

                $data[ $k ]["source"] = $this->getConfig('source_type');
                $data[ $k ]["category_id"] = $v;
                $k++;
            }
        }

       
        return $data;
    }
}