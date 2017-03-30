<?php
/******************************************************
 * @package Venustheme Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://venustheme.com
 * @copyright	Copyright (C) December 2010 venustheme.com <@emai:venustheme@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class Ves_ProductList_Model_Product extends Mage_Catalog_Block_Product_Abstract{

	protected $_config = '';
	const DEFAULT_STORE_ID = 0;
	public $_current_page = 1;
	public function getConfig( $key, $val=0) 
	{
		return (isset($this->_config[$key])?$this->_config[$key]:$val);
	}
	public function setCurPage($page = 1) {
		$this->_current_page = (int)$page;
	}

	function getListProducts($config = array()) {
		$list = array();

		switch ( $config['source_type'] ) {
			case 'new_arrival' :
			$list = $this->getListNewarrivalProducts($config);
			break;
			case 'latest' :
			$list = $this->getListLatestProducts($config);
			break;
			case 'best_seller' :
			$list = $this->getListBestSellerProducts($config);
			break;
			case 'attribute' :
			$list = $this->getListAttributeProducts($config);
			break;
			case 'featured':
			$list = $this->getListFeaturedProducts($config);
			break;
			case 'most_viewed' :
			$list = $this->getListMostViewedProducts($config);
			break;
			case 'special' :
			$list = $this->getListSpecialProducts($config);
			break;
			case 'top_rate':
			$list = $this->getListTopRatedProducts($config);
			break;
			case 'random':
			$list = $this->getListRandomProducts($config);
			break;
		}

		return $list;
	}

	public function getCollectionPro($model_type = 'catalog/product_collection')
    {
	    $storeId = Mage::app()->getStore()->getId();        
	    $productFlatTable = Mage::getResourceSingleton('catalog/product_flat')->getFlatTableName($storeId);
	    $attributesToSelect = array('name','entity_id','price', 'small_image','short_description');

	    try{

	        /**
	        * init resource singleton collection
	        */
	        $products = Mage::getResourceModel($model_type);//Mage::getResourceSingleton('reports/product_collection');
	        if(Mage::helper('catalog/product_flat')->isEnabled()){
	          $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	        }else{
	          $products->addAttributeToSelect($attributesToSelect);
	        }
	        $products->addStoreFilter($storeId);
	        return $products;

	    } catch (Exception $e){
	        Mage::logException($e->getMessage());
	    }
    }

	public function getListSpecialProducts( $config = array() ){
		$this->_config = $config;
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$list = array();
		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

		if($cateids && $cateids != "1") {

			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $resource = Mage::getSingleton('core/resource');
		    
		    $products = $this->getCollectionPro()
			         	->addFieldToFilter('visibility', array(
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			                   )) //showing just products visible in catalog or both search and catalog
			         	->addMinimalPrice()
						->addUrlRewrite()
						->addTaxPercents()
						->addStoreFilter($storeId)
			            ->addFinalPrice()
			            ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
			         	->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
			         	->groupByAttribute('entity_id');
		         	 
        	$products ->getSelect()->where('price_index.final_price < price_index.price');

		} else {
			$products = $this->getCollectionPro()
			         	->addFieldToFilter('visibility', array(
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			                               Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			                   )) //showing just products visible in catalog or both search and catalog
			         	->addMinimalPrice()
						->addUrlRewrite()
						->addTaxPercents()
						->addStoreFilter($storeId)
			            ->addFinalPrice()
			         	->groupByAttribute('entity_id');
		         	 
        	$products ->getSelect()->where('price_index.final_price < price_index.price');

		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$size = $products->getSize();
		$list['hasNextData'] = true;
		if($limit_item<$size){
			$size = $limit_item;
		}
		if($size<=$curPage*$itemspage){
			$list['hasNextData'] = false;
		}
		$products->setPageSize($itemspage)->setCurPage($curPage);
		$this->setProductCollection($products);

		$this->_addProductAttributesAndPrices($products);               
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list['products'] = $products;
		}
		return $list;
	}

	public function getListRandomProducts(  $config = array())
    {
		$list = array();
		$fieldorder = 'created_at';
		$order = 'desc';
		$this->_config = $config;
		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);
		if( $curPage * $itemspage > $limit_item + $itemspage ){ 
			return '';
		}
		if($this->getConfig('page')){

		}

		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$resource = Mage::getSingleton('core/resource');

		if($cateids && $cateids != "1") {
			$arr_catsid = array();
			if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $products   = $this->getCollectionPro()
						        ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
				         		->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');


		} else {
			$products   = $this->getCollectionPro()
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');
		}

		$products->getSelect()->order(new Zend_Db_Expr('RAND()'));

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$products->setPageSize($itemspage)->setCurPage($curPage);		

		$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);
		
		if (($products = $this->getProductCollection ()) && $products->getSize ()) {
			$list['products'] = $products;
			$size = $products->getSize();
			$list['hasNextData'] = true;
			if($limit_item<$size){
				$size = $limit_item;
			}

			if($size<=$curPage*$itemspage){
				$list['hasNextData'] = false;
			}
		}
		return $list;
    }

	public function getListLatestProducts( $config = array() )
	{	
		$list = array();
		$fieldorder = 'created_at';
		$order = 'desc';
		$this->_config = $config;
		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);
		if( $curPage * $itemspage > $limit_item + $itemspage ){ 
			return '';
		}
		if($this->getConfig('page')){

		}

		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$resource = Mage::getSingleton('core/resource');

		if($cateids && $cateids != "1") {
			$arr_catsid = array();
			if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $products   = $this->getCollectionPro()
						        ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
				         		->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
						        ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');


		} else {
			$products   = $this->getCollectionPro()
						        ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');
		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$products->setPageSize($itemspage)->setCurPage($curPage);		

		$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);
		
		if (($products = $this->getProductCollection ()) && $products->getSize ()) {
			$list['products'] = $products;
			$size = $products->getSize();
			$list['hasNextData'] = true;
			if($limit_item<$size){
				$size = $limit_item;
			}

			if($size<=$curPage*$itemspage){
				$list['hasNextData'] = false;
			}
		}
		return $list;
	}

	public function getListNewarrivalProducts( $config = array() )
	{	$list = array();
		$fieldorder = 'created_at';
		$order = 'desc';
		$this->_config = $config;
		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		if( $curPage * $itemspage > $limit_item + $itemspage ){ 
			return '';
		}
		

		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

    	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $resource = Mage::getSingleton('core/resource');

		if($cateids && $cateids != "1") {
			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $products   = $this->getCollectionPro()
							    ->addAttributeToFilter(array( array('attribute' => 'news_from_date', array('or'=> array(
					                0 => array('date' => true, 'to' => $todayEndOfDayDate),
					                1 => array('is' => new Zend_Db_Expr('null')))
					          ), 'left')))
					          ->addAttributeToFilter(array( array('attribute' => 'news_to_date', array('or'=> array(
					                0 => array('date' => true, 'from' => $todayStartOfDayDate),
					                1 => array('is' => new Zend_Db_Expr('null')))
					            ), 'left')))
						        ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
				         		->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
				         		->addAttributeToFilter(
					                array(
					                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
					                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
					                    )
					              )
						        ->addAttributeToSort('news_from_date', 'desc')
						        ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');
		} else {
			 $products   = $this->getCollectionPro()
							    ->addAttributeToFilter(array( array('attribute' => 'news_from_date', array('or'=> array(
					                0 => array('date' => true, 'to' => $todayEndOfDayDate),
					                1 => array('is' => new Zend_Db_Expr('null')))
					          ), 'left')))
					          	->addAttributeToFilter(array( array('attribute' => 'news_to_date', array('or'=> array(
					                0 => array('date' => true, 'from' => $todayStartOfDayDate),
					                1 => array('is' => new Zend_Db_Expr('null')))
					            ), 'left')))
					          	->addAttributeToFilter(
					                array(
					                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
					                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
					                    )
					              )
						        ->addAttributeToSort('news_from_date', 'desc')
						        ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');
		}		
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$products->setPageSize($itemspage)->setCurPage($curPage);		

		$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);

		if (($products = $this->getProductCollection ()) && $products->getSize ()) {
			$list['products'] = $products;
			$size = $products->getSize();
			$list['hasNextData'] = true;
			if($limit_item<$size){
				$size = $limit_item;
			}

			if($size<=$curPage*$itemspage){
				$list['hasNextData'] = false;
			}
		}
		return $list;
	}

	public function getListBestSellerProducts( $config = array() )
	{
		$this->_config = $config;
		$fieldorder = 'ordered_qty';
		$order = 'desc';
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$list = array();
		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		$date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
        $resource = Mage::getSingleton('core/resource');

		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

		if($cateids && $cateids != "1") {

			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $products   = $this->getCollectionPro()
										//->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
										->addStoreFilter()
										->addPriceData()
										->addTaxPercents()
										->addUrlRewrite()
										->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
									    ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))));

			$products->getSelect()
						->joinLeft(
							array('aggregation' => $products->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
							"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
							array('SUM(aggregation.qty_ordered) AS sold_quantity')
							)
						->group('e.entity_id')
						->order(array('sold_quantity DESC', 'e.created_at'));

		} else {

			$products   = $this->getCollectionPro()
									//->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
									->addStoreFilter()
									->addPriceData()
									->addTaxPercents()
									->addUrlRewrite();

		  	$products->getSelect()
					->joinLeft(
						array('aggregation' => $products->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
						"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
						array('SUM(aggregation.qty_ordered) AS sold_quantity')
						)
					->group('e.entity_id')
					->order(array('sold_quantity DESC', 'e.created_at'));
	    }
	    $list = array();

	    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
    	Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
    	$this->_addProductAttributesAndPrices($products);
    	$products->setPageSize($itemspage)->setCurPage($curPage);
    	$this->setProductCollection($products);

    	if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
    		$list['products'] = $products;
    	}

	    $size = $products->getSize();
	    $list['hasNextData'] = true;
	    if($limit_item<$size){
	    	$size = $limit_item;
	    }
	    if($size<=$curPage*$itemspage){
	    	$list['hasNextData'] = false;
	    }
	    return $list;
	}

	public function getListTopRatedProducts($config = array()) {
		$this->_config = $config;
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');
		$resource = Mage::getSingleton('core/resource');

		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

    	if($cateids && $cateids != "1") {
    		$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

    		$products   = $this->getCollectionPro('reports/product_collection')
			                   	->addAttributeToFilter(array( array('attribute' =>'visibility', array('neq'=>1))))
			                   	->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
					         	->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
					         	->groupByAttribute('entity_id');

			$products->joinField('rating_summary_field', 'review/review_aggregate', 'rating_summary', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => Mage::app()->getStore()->getId()), 'left');                
			$products->addAttributeToSort('rating_summary_field', 'desc');
		} else {
			$products   = $this->getCollectionPro('reports/product_collection')
                   				->addAttributeToFilter(array( array('attribute' =>'visibility', array('neq'=>1))))
		         				->groupByAttribute('entity_id');

			$products->joinField('rating_summary_field', 'review/review_aggregate', 'rating_summary', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => Mage::app()->getStore()->getId()), 'left');                
			$products->addAttributeToSort('rating_summary_field', 'desc');
		}

	    $list = array();


		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$products->setPageSize($itemspage)->setCurPage($curPage);

		$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);

		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list['products'] = $products;
		}

		$size = $products->getSize();
		$list['hasNextData'] = true;
		if($limit_item<$size){
			$size = $limit_item;
		}
		if($size<=$curPage*$itemspage){
			$list['hasNextData'] = false;
		}
		
		return $list;
	}

	public function getListMostViewedProducts(  $config = array())
	{
		$this->_config = $config;
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');
		$resource = Mage::getSingleton('core/resource');

		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

		if($cateids && $cateids != "1") {

			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

		    $products   = $this->getCollectionPro('reports/product_collection')
		    						->addAttributeToFilter(array( array('attribute' =>'visibility', array('neq'=>1))))
		    						->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
		         					->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
									->groupByAttribute('entity_id');


		} else {
			$products   = $this->getCollectionPro('reports/product_collection')
									->addAttributeToFilter(array( array('attribute' =>'visibility', array('neq'=>1))))
									->groupByAttribute('entity_id');
		}

		$list = array();


		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$products->setPageSize($itemspage)->setCurPage($curPage);
		$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);

		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {            
			$list['products'] = $products;
		}

		$size = $products->getSize();
		$list['hasNextData'] = true;
		if($limit_item<$size){
			$size = $limit_item;
		}
		if($size<=$curPage*$itemspage){
			$list['hasNextData'] = false;
		}
		
		return $list;
	}

	public function getListAttributeProducts(  $config = array())
	{ 
		$this->_config = $config;
		$attribute_key = $this->getConfig('attr_key', 'featured');
		$attribute_value = $this->getConfig('attr_val', '1');

		$list = array();
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$resource = Mage::getSingleton('core/resource');

		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);
		
		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

		if($cateids && $cateids != "1") {
			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}

	    	$products = $this->getCollectionPro()
										    ->addMinimalPrice()
										    ->addUrlRewrite()
										    ->addTaxPercents()
									  		->addAttributeToFilter( array( 
														    array( 'attribute'=> $attribute_key, 'eq' => $attribute_value )
														))
										    ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
										    ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
								    		->addAttributeToSort('news_from_date','desc')
										    ->addAttributeToSort('created_at', 'desc')
										    ->addAttributeToSort('updated_at', 'desc')
										    ->groupByAttribute('entity_id');	
		} else {
			$products = $this->getCollectionPro()
										    ->addMinimalPrice()
										    ->addUrlRewrite()
										    ->addTaxPercents()
									  		->addAttributeToFilter( array( 
														    array( 'attribute'=>'featured', 'eq' => '1' )
														))
								    		->addAttributeToSort('news_from_date','desc')
										    ->addAttributeToSort('created_at', 'desc')
										    ->addAttributeToSort('updated_at', 'desc')
										    ->groupByAttribute('entity_id');
		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$size = $products->getSize();
		$list['hasNextData'] = true;
		if( $limit_item < $size ){
			$size = $limit_item;
		}
		if( $size <= $curPage * $itemspage ){
			$list['hasNextData'] = false;
		}

		$products->setPageSize($itemspage)->setCurPage($curPage);
		$this->setProductCollection($products);

		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			$list['products'] = $_products;
		}

		return $list;
	}
	public function getListFeaturedProducts(  $config = array())
	{ 
		$this->_config = $config;
		$list = array();
		$storeId    = Mage::app()->getStore()->getId();
		$cateids = $this->getConfig('catsid');

		$resource = Mage::getSingleton('core/resource');

		$limit_item = $this->getConfig('limit_item',12);
		$itemspage = $this->getConfig('itemspage',6);
		$curPage = $this->getConfig('page',1);

		if($curPage*$itemspage>$limit_item+$itemspage){ 
			return '';
		}

		if($cateids && $cateids != "1") {
			$arr_catsid = array();
	    	if(is_array($cateids)) {
				$arr_catsid = $cateids;
			} else {
				if(stristr($cateids, ',') === FALSE) {
	            	$arr_catsid =  array($cateids);
			    }else{
			        $arr_catsid = explode(",", $cateids);
			    }
			}
			
	    	$products = $this->getCollectionPro()
										    ->addMinimalPrice()
										    ->addUrlRewrite()
										    ->addTaxPercents()
									  		->addAttributeToFilter( array( 
														    array( 'attribute'=>'featured', 'eq' => '1' )
														))
										    ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
										    ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
								    		->addAttributeToSort('news_from_date','desc')
										    ->addAttributeToSort('created_at', 'desc')
										    ->addAttributeToSort('updated_at', 'desc')
										    ->groupByAttribute('entity_id');	
		} else {
			$products = $this->getCollectionPro()
										    ->addMinimalPrice()
										    ->addUrlRewrite()
										    ->addTaxPercents()
									  		->addAttributeToFilter( array( 
														    array( 'attribute'=>'featured', 'eq' => '1' )
														))
								    		->addAttributeToSort('news_from_date','desc')
										    ->addAttributeToSort('created_at', 'desc')
										    ->addAttributeToSort('updated_at', 'desc')
										    ->groupByAttribute('entity_id');
		}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

		$size = $products->getSize();
		$list['hasNextData'] = true;
		if( $limit_item < $size ){
			$size = $limit_item;
		}
		if( $size <= $curPage * $itemspage ){
			$list['hasNextData'] = false;
		}

		$products->setPageSize($itemspage)->setCurPage($curPage);
		$this->setProductCollection($products);

		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			$list['products'] = $_products;
		}

		return $list;
	}

	function inArray($source, $target) {
		for($i = 0; $i < sizeof ( $source ); $i ++) {
			if (in_array ( $source [$i], $target )) {
				return true;
			}
		}
	}

	function getProductByCategories($config){
		$products = '';
		if( $config['catsid']!='' ){

			$storeId = Mage::app()->getStore()->getId();
			$products = Mage::getResourceModel('reports/product_collection')
			->addAttributeToSelect('*')
			->addAttributeToFilter('category_ids',array('finset'=>$config['catsid']));
			$products->setPageSize(3)->setCurPage(1);

			$products = Mage::getResourceModel('reports/product_collection')
			->addAttributeToSelect('*')
			->addAttributeToFilter('category_ids',array('finset'=>'23,24'));
		}

		return $products;
	}
	
	function getProductByCategory(){
		$return = array(); 
		$pids = array();
		$catsid=$this->getConfig('catsid');
		$products = Mage::getResourceModel ( 'catalog/product_collection' );

		foreach ($products->getItems() as $key => $_product){
			$arr_categoryids[$key] = $_product->getCategoryIds();

			if($catsid && $catsid !="1"){    
				if(stristr($catsid, ',') === FALSE) {
					$arr_catsid[$key] =  array(0 => $catsid);
				}else{
					$arr_catsid[$key] = explode(",", $catsid);
				}
				$return[$key] = $this->inArray($arr_catsid[$key], $arr_categoryids[$key]);
			}
		}

		foreach ($return as $k => $v){ 
			if($v==1) $pids[] = $k;
		}    

		return $pids;   
	}
}