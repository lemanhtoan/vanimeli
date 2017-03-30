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
class Ves_ProductList_Model_Rule extends Mage_Rule_Model_Abstract
{
    const CACHE_WIDGET_NEW_TAG = "productlist_new_widget";
    const CACHE_WIDGET_CAROUSEL = "productlist_carousel_widget";
    const CACHE_WIDGET_CATEGORYTAB = "productlist_categorytab_widget";
    const CACHE_WIDGET_TAB = "productlist_tab_widget";
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Init resource model and id field
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('productlist/rule');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return Mage_ProductList_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('productlist/rule_condition_combine');
    }

    /**
     * Getter for rule actions collection
     *
     * @return Mage_ProductList_Model_Rule_Action_Collection
     */
    public function getActionsInstance()
    {
        return Mage::getModel('productlist/rule_action_collection');
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if (is_null($this->_productIds)) {
            $this->_productIds = array();
            $this->setCollectedAttributes(array());
            $productCollection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }
            $this->getConditions()->collectValidatedAttributes($productCollection);

            Mage::getSingleton('core/resource_iterator')->walk(
                $productCollection->getSelect(),
                array(array($this, 'callbackValidateProduct')),
                array(
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => Mage::getModel('catalog/product'),
                    )
                );
        }

        $productIds = $this->getSourceProducts($this->_productIds);

        $ids = array();
        foreach ($productIds as $k => $v) {
            $ids[] = $v;
            if( $k == ( $this->getProductNumber()) ) break;
        }

        return $ids;
    }

    /**
     * Callback function for product matching
     *
     * @param $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if($this->getConditions()->validate($product)){
            $this->_productIds[] = $product->getId();
        }
    }

    public function getCollectionPro($model_type = 'catalog/product_collection')
    {
      $storeId = Mage::app()->getStore()->getId();        
      $productFlatTable = Mage::getResourceSingleton('catalog/product_flat')->getFlatTableName($storeId);
      $attributesToSelect = "*";//array('name','entity_id','price', 'small_image','short_description');
      try{
            /**
            * init resource singleton collection
            */
            $products = Mage::getResourceModel($model_type);
            if(Mage::helper('catalog/product_flat')->isEnabled()){
              $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
            }else{
              $products->addAttributeToSelect($attributesToSelect);
            }
            $products->addStoreFilter($storeId);
            return $products;
      }catch (Exception $e){
            Mage::logException($e->getMessage());
      }
    }

    /**
     * Filter Collection by rule
     * @param Ids array
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function getSourceProducts($ids){
        $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect('*')
                        ->addAttributeToSort('e.created_at','DESC');

        $newIds = array();
        if($collection && $collection->getSize()>0){
            $data = array();
            foreach ($collection as $_product) {
                $data[] = $_product->getId();
            }

            foreach ($ids as $k => $v) {
                if($i = array_search($v,$data)){
                    $newIds[$i] = $v;
                }
            }

            ksort($newIds);
        }
        return $newIds;
    }


    protected function getTopRateProducts(){
        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
        $summaryTable = Mage::getSingleton('core/resource')->getTableName('review_entity_summary');
        $collection->getSelect()->join(array('t2'=>$summaryTable),'e.entity_id = t2.entity_pk_value');
        $collection->getSelect()->where('t2.rating_summary > 0')
        ->order(array('t2.rating_summary DESC'))
        ->group('e.entity_id');
        return $collection;
    }

    protected function getMostViewedProducts(){
        $storeId    = Mage::app()->getStore()->getId();
        $collection = Mage::getResourceModel('reports/product_collection')
        ->addAttributeToSelect('*')
        ->setStoreId($storeId)
        ->addViewsCount()
        ->addAttributeToSort('views','DESC');
        return $collection;
    }

    protected function getBestSellerProducts(){
        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
        $storeId    = Mage::app()->getStore()->getId();
        $date = new Zend_Date();
        $collection->addPriceData()
        ->addTaxPercents()
        ->addUrlRewrite();
        $collection->getSelect()
        ->joinLeft(
            array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
            "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId}",
            array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
        ->order(array('aggregation.qty_ordered DESC', 'e.created_at'))
        ->group('e.entity_id');
        return $collection;
    }

    protected function getSpecialProducts(){
        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
        $storeId = Mage::app()->getStore()->getId();
        $collection->addMinimalPrice()
        ->addUrlRewrite()
        ->addTaxPercents()
        ->addFinalPrice()
        ->addStoreFilter($storeId)
        ->getSelect()
        ->where('price_index.final_price < price_index.price')
        ->order(array('e.created_at DESC'))
        ->group('e.entity_id');
        return $collection;
    }

    protected function getFeaturedProducts(){
        $collection = Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('*');
        if(Mage::helper('catalog/product_flat')->isEnabled()){
            $collection->addAttributeToFilter(array(array(
                'attribute' => 'featured',
                'eq' => 1,
                )), null, 'left');
        }else{
            $collection->addAttributeToFilter("featured", 1);
        }
        $collection->getSelect()
        ->order(array('e.created_at DESC'))
        ->group('e.entity_id');
        return $collection;
    }

    public function getUrl(){
        return Mage::getUrl().$this->getIdentifier().'.html';
    }

    /**
     * Retrieve array of product id's for category
     *
     * array($productId => $position)
     *
     * @return array
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return array();
        }

        $array = $this->getData('products_position');
        if (is_null($array)) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    public function getDefaultSortBy() {
        return array(
            'price' => Mage::helper("productlist")->__('Price'),
            'name' => Mage::helper("productlist")->__('Name')
            );
    }
    /**
     * Retrieve Available Product Listing  Sort By
     * code as key, value - name
     *
     * @return array
     */
    public function getAvailableSortByOptions() {
        $availableSortBy = array();
        $defaultSortBy   = $this->getDefaultSortBy();

        $tmp_available_sort_by = $this->getAvailableSortBy();
        $tmp_available_sort_by   = is_array($tmp_available_sort_by)?$tmp_available_sort_by:explode(",", $tmp_available_sort_by);

        if ($tmp_available_sort_by) {
            foreach ($tmp_available_sort_by as $sortBy) {
                $sortBy = trim($sortBy);
                $label = $sortBy;
                if($sortBy == "position") {
                    $sortBy = "best";
                    $label = Mage::helper("productlist")->__("Position");
                }
                if (isset($defaultSortBy[$sortBy])) {
                    $availableSortBy[$sortBy] = $defaultSortBy[$sortBy];
                } elseif($sortBy) {
                    $availableSortBy[$sortBy] = $label;
                }
            }
        }
        if (!$availableSortBy) {
            $availableSortBy = $defaultSortBy;
        }

        return $availableSortBy;
    }


    public function updateProductPosition($product_id , $position = 0) {

        if(($rule_id = $this->getId()) && $product_id) {
            $resource = Mage::getSingleton('core/resource');
            /**
            * Retrieve the write connection
            */
            $writeConnection = $resource->getConnection('core_write');
            $rule_product = $resource->getTableName("productlist/rule_product");
            $writeConnection->query("UPDATE `{$rule_product}` SET `position` = {$position} WHERE `rule_id` = {$rule_id} AND `rule_product_id` = {$product_id}");

        }
        
    }
    /*Apply data source type for product collection*/
    public function applySourceType($collection = null, $is_block_mode = true) {
        if($collection) {
            /*Apply source type*/
            $source_type = $this->getSourceType();
            switch($source_type) {
                case "best_value":
                    if($is_block_mode) {
                        $collection->getSelect()->order('t2.position ASC');
                    }
                break;
                case "new_arrival":
                    $fieldorder = 'created_at';
                    $order = 'desc';    
                    $todayStartOfDayDate  = Mage::app()->getLocale()->date()
                        ->setTime('00:00:00')
                        ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

                    $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                        ->setTime('23:59:59')
                        ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

                    $collection->addAttributeToFilter(array( array('attribute' => 'news_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                              ), 'left')))
                              ->addAttributeToFilter(array( array('attribute' => 'news_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')))
                              ->addAttributeToSort('news_from_date', 'desc')
                              ->addAttributeToSort($fieldorder, $order);

                break;
                case "special":
                    $collection->getSelect()
                        ->where('price_index.final_price < price_index.price');
                break;
                case "most_viewed":
                    if($is_block_mode) {
                        $collection->addViewsCount();
                    }
                   
                break;
                case "best_seller":
                    if($is_block_mode) {
                        // Date
                        $date = new Zend_Date();
                        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
                        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
                        $storeId    = Mage::app()->getStore()->getId();

                        $collection->getSelect()
                            ->joinLeft(
                                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                                array('SUM(aggregation.qty_ordered) AS sold_quantity')
                                )
                            ->group('e.entity_id')
                            ->order(array('sold_quantity DESC', 'e.created_at'));
                    }
                break;
                case "top_rate":
                    if($is_block_mode) {
                        $collection->joinField('rating_summary_field', 'review/review_aggregate', 'rating_summary', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => Mage::app()->getStore()->getId()), 'left');                
                        $collection->addAttributeToSort('rating_summary_field', 'desc');
                    }
                break;
                case "latest":
                    $fieldorder = 'created_at';
                    $order = 'desc';

                    $collection->addAttributeToSort($fieldorder, $order);
                break;
                case "random":
                    $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                break;
            }
        }

        return $collection;
    }

}