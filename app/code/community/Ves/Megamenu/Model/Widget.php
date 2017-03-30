<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php

class Ves_Megamenu_Model_Widget extends Mage_Core_Model_Abstract
{
	const DEFAULT_STORE_ID = 0;
	private $widgets = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('ves_megamenu/widget');
    }

	/**
	 *
	 */
	public function getWidgetContent( $type, $data, $widget_name = ""){
		$method = "renderWidget".ucfirst($type).'Content';

	 	$args = array();

		if( method_exists( $this, $method ) ){
			return $this->{$method}( $args, $data, $widget_name );
		}
		return ;
	}
	/**
	 *
	 */
	public function renderWidgetHtmlContent(  $args, $setting, $widget_name= "" ){
		
		$t  = array(
			'name'			=> '',
			'show_name'		=> 1,
			'html'   		=> ''
		);
		$setting = array_merge( $t, $setting );
		$html = '';

		if( isset($setting['html']) ){
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$html = $processor->filter($setting['html']);
		}
		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
						"html" 			=> $html);
  		$output = $this->renderLayoutHtml( "html", $data );

  		return $output;
	}
	/**
	 *
	 */
	public function renderWidgetVideo_codeContent(  $args, $setting, $widget_name= "" ){

		$t  = array(
			'name'			=> '',
			'show_name'		=> 1,
			'video_code'   		=> ''
		);
		$setting = array_merge( $t, $setting );
		$html =  $setting['video_code'];

		$html = str_replace(array("[", "]"), array("<", ">"), $html);

 		$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
						"html" 			=> $html);
  		$output = $this->renderLayoutHtml( "html", $data );


  		return $output;
	}
	/**
	 *
	 */
	public function renderWidgetFeedContent(  $args, $setting, $widget_name= "" ){

		$t = array(
			'limit' => 12,
			'show_name'		=> 1,
	 		'feed_url' => ''
		);
		$setting = array_merge( $t, $setting );

	 	$output = '';
	 	if( $setting['feed_url'] ) {
			$content = file_get_contents( $setting['feed_url']  );
			$x = new SimpleXmlElement($content);
			$items = $x->channel->item;

			$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "limit"		=> $setting['limit'],
						"items" 			=> $items);

			$output = $this->renderLayoutHtml( "feed", $data );
		}


		return $output;
	}

	public function checkModuleInstalled( $module_name = "") {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        if($modulesArray) {
            $tmp = array();
            foreach($modulesArray as $key=>$value) {
                $tmp[$key] = $value;
            }
            $modulesArray = $tmp;
        }

        if(isset($modulesArray[$module_name])) {

            if((string)$modulesArray[$module_name]->active == "true") {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }

	public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml') {
            return true;
        }
        return false;
    }

    public function inArray($source, $target) {
		for($i = 0; $i < sizeof ( $source ); $i ++) {
			if (in_array ( $source [$i], $target )) {
			return true;
			}
		}
    }

    public function getStoreCategories() {
    	$store_id =  Mage::app()->getStore()->getId();

		if(!$store_id) {
			$store_id = Mage::app()
						    ->getWebsite()
						    ->getDefaultGroup()
						    ->getDefaultStoreId();
		}

		$catsid = array();
		$category_model = Mage::getModel('catalog/category');
		$rootCategoryId = Mage::app()->getStore($store_id)->getRootCategoryId();
		$_category = $category_model->load($rootCategoryId);
		$all_child_categories = $category_model->getResource()->getAllChildren($_category);
		foreach($all_child_categories as $storecategories){
			$catsid[] = $storecategories;
		}

		return $catsid;
    }

    public function getProductByCategory($arr_catsid = array()){
        $return = array(); 
        $pids = array();
        $products = Mage::getResourceModel ( 'catalog/product_collection' );

        foreach ($products->getItems() as $key => $_product){
            $arr_categoryids[$key] = $_product->getCategoryIds();
            if($arr_catsid){
                $return[$key] = $this->inArray($arr_catsid, $arr_categoryids[$key]);

            }
        }

        foreach ($return as $k => $v){ 
            if($v==1) $pids[] = $k;
        }    

        return $pids;   
    }

	/**********/

	public function renderWidgetProductContent( $args, $setting, $widget_name = "" ) {

		$output = '';
		$t = array(
			'show_name'=> '1',
			'product_id' => 0,
			'image_height' => '320',
			'image_width'	 =>  300
		);

		$setting = array_merge( $t, $setting );

		$setting['product_id'] = isset($setting['product_id'])?$setting['product_id']:0;

		if($setting['product_id']) {
			$collection = Mage::getModel('catalog/product')->getCollection()
													  ->addAttributeToSelect('*')
													  ->addAttributeToFilter('entity_id', $setting['product_id']);

			if(!$this->isAdmin()){
				$collection = $this->_addProductAttributesAndPrices( $collection );
			}

	        $result = $collection->getFirstItem();

	  		$data = array( "widget_name" 	=> $widget_name,
						   "show_name" 		=> $setting['show_name'],
						   "image_height"	=> $setting['image_height'],
						   "image_width"	=> $setting['image_width'],
						 	"product" 		=> $result);

			$output = $this->renderLayoutHtml( "product", $data );
		}

		return $output;
	}
	/**
	 *
	 */
	public function renderWidgetImageContent(  $args, $setting, $widget_name= "" ){

		$t  = array(
			'show_name'=> '1',
			'group_id'=> '',
			'image_width'   => 80,
			'image_height'	=> 80
		);

		$setting = array_merge( $t, $setting );
		

		$image = "";
        if ($setting['image_path']) {
            $image = Mage::helper("ves_megamenu")->resizeImage($setting['image_path'], (int)$setting['image_width'], (int)$setting['image_height']);
        }

		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "image_height"	=> $setting['image_height'],
					   "image_width"	=> $setting['image_width'],
						"image" 		=> $image);

		$output = $this->renderLayoutHtml( "image", $data );

		return $output;

	}

	/**
	 *
	 */
	public function renderWidgetVes_brandContent(  $args, $setting, $widget_name= "" ){
		$t  = array(
			'show_name'=> '1',
			'limit'   => '5',
			'image_width'=>'200',
			'image_height' =>'200'
		);
		$setting = array_merge( $t, $setting );

		$output = "";
		
		if($this->checkModuleInstalled("Ves_Brand")) {
			$collection = Mage::getModel( 'ves_brand/brand' )
						->getCollection();
		
			$collection->setOrder( 'position', 'ASC' );
			
			$collection->setPageSize( (int)$setting['limit'] )->setCurPage( 1 );

			$data = array("widget_name" 	=> $widget_name,
						   "show_name" 		=> $setting['show_name'],
						   "image_width"	=> $setting['image_width'],
					   	   "image_height"	=> $setting['image_height'],
						   "brands" 		=> $collection);

			$output = $this->renderLayoutHtml( "brands", $data );
		}
		

  		return $output;
	}

	/**
	 *
	 */
	public function renderWidgetVes_blogContent(  $args, $setting, $widget_name= "" ){
		$t  = array(
			'show_name'=> '1',
			'limit'   => '5'
		);
		$setting = array_merge( $t, $setting );

		$output = "";
		
		if($this->checkModuleInstalled("Ves_Blog")) {
			$collection = Mage::getModel( 'ves_blog/post' )
						->getCollection()
						->addCategoriesFilter(0);
		
			$collection ->setOrder( 'created', 'DESC' );
			
			$collection->setPageSize( (int)$setting['limit'] )->setCurPage( 1 );

			$data = array("widget_name" 	=> $widget_name,
						   "show_name" 		=> $setting['show_name'],
						   "blogs" 			=> $collection);

			$output = $this->renderLayoutHtml( "blogs", $data );
		}
		

  		return $output;
	}

	/**
	 *
	 */
	public function renderWidgetProduct_categoryContent(  $args, $setting, $widget_name= "" ){
		$t  = array(
			'show_name'=> '1',
			'category_id'=> '',
			'limit'   => '5',
			'image_width'=>'200',
			'image_height' =>'200'
		);
		$setting = array_merge( $t, $setting );

		if((int)$setting['category_id'] == -1)
			return ;

		$storeId    = Mage::app()->getStore()->getId();
    	$arr_catsid = array((int)$setting['category_id']);

    	$resource = Mage::getSingleton('core/resource');
    	$collection   = $this->getCollectionPro()
						        ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
				         	    ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
						        ->addAttributeToSort('updated_at', 'DESC')
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');

    	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

		$collection->setPageSize( (int)$setting['limit'] )->setCurPage(1);

		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "image_width"	=> $setting['image_width'],
					   "image_height"	=> $setting['image_height'],
					   "products" 		=> $collection);

		$output = $this->renderLayoutHtml( "product_list", $data );

  		return $output;
	}

	/**
	 * category_list
	 */
	public function renderWidgetCategory_listContent(  $args, $setting, $widget_name= "" ){
		$t = array(
			'show_name'=> '1',
			'show_image'=> '1',
			'subcategory_level'=> '1',
			'image_width'=> '80',
			'image_height'=> '80',
			'category_id'=>'0'
		);
		
		$categories = array();
		$subcategories = array();

		$setting = array_merge( $t, $setting );
		$storeId    = Mage::app()->getStore()->getId();
		$setting['category_id'] = (int)$setting['category_id'];
		if($setting['category_id'] == -1)
			return;
		$category_model = null;
		$category_link = "";
		$recursionLevel = (int)$setting['subcategory_level'];
		if($setting['category_id']) {
			$category_model = Mage::getModel('catalog/category'); 
			$_category = $category_model->load($setting['category_id']);
			$category_link = $_category->getUrl();
			
			$categories = $_category->getChildren();
			
		}

  		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "show_image" 	=> $setting['show_image'],
					   "image_width" 	=> $setting['image_width'],
					   "image_height" 	=> $setting['image_height'],
					   "recursion_level" => $recursionLevel,
					   "category_model" => $category_model,
					   "category_link" 	=> $category_link,
					   "categories" 	=> $categories);

  		if($recursionLevel > 1) {
  			$output = $this->renderLayoutHtml( "category_list_levels", $data );
  		} else {
  			$output = $this->renderLayoutHtml( "category_list", $data );
  		}
		

  		return $output;
	}

	/**
	 *
	 */
	public function renderWidgetProduct_listContent(  $args, $setting, $widget_name= "" ){
		$t = array(
			'show_name'=> '1',
			'list_type'=> '',
			'limit' => 5,
			'image_width'=>'200',
			'image_height' =>'200'
		);
		
		$products = array();

		$setting = array_merge( $t, $setting );
		$storeId    = Mage::app()->getStore()->getId();
		$resource = Mage::getSingleton('core/resource');
		if( $setting['list_type'] == 'bestseller' ) {


			$catsid = $this->getStoreCategories();

			$date = new Zend_Date();
	      	$toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
	      	$fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
	      	
		    $products   = $this->getCollectionPro()
											->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
											->addStoreFilter()
											->addPriceData()
											->addTaxPercents()
											->addUrlRewrite()
											->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
										  ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))));

			$products->getSelect()
					->joinLeft(
						array('aggregation' => $products->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
						"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
						array('SUM(aggregation.qty_ordered) AS sold_quantity')
						)
					->group('e.entity_id')
					->order(array('sold_quantity DESC', 'e.created_at'));

		} else if( $setting['list_type'] == 'special' ) {
			$catsid = $this->getStoreCategories();
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
			         	 ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))))
			         	 ->groupByAttribute('entity_id');

	        $products ->getSelect()
	               		->where('price_index.final_price < price_index.price');

		} else {	
			$catsid = $this->getStoreCategories();
			$todayStartOfDayDate  = Mage::app()->getLocale()->date()
						            ->setTime('00:00:00')
						            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      		$todayEndOfDayDate  = Mage::app()->getLocale()->date()
						            ->setTime('23:59:59')
						            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

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
		         				->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))))
				          		->addAttributeToSort('news_from_date', 'desc')
				          		->addAttributeToSort("created_at", "DESC")
				          		->addAttributeToSort("updated_at", "DESC")
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');

    	}
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);


        $products->setPage(1, (int)$setting['limit']);

        $products = $this->_addProductAttributesAndPrices( $products );

  		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "image_width"	=> $setting['image_width'],
					   "image_height"	=> $setting['image_height'],
					   "products" 		=> $products);

		$output = $this->renderLayoutHtml( "product_list", $data );

  		return $output;
	}

	/**
	 *
	 */
	public function renderWidgetProduct_carouselContent(  $args, $setting, $widget_name= "" ){
		$t = array(
			'show_name'=> '1',
			'list_type'=> '',
			'limit' => 5,
			'category_id' => 0,
			'max_items' => 1,
			'limit_cols' => 1,
			'auto_play' => 0,
			'speed' => 6000,
			'image_width'=>'200',
			'image_height' =>'200'
		);
		
		$products = array();
		$resource = Mage::getSingleton('core/resource');
		$setting = array_merge( $t, $setting );
		$storeId    = Mage::app()->getStore()->getId();
		$category_id = $setting['category_id']?(int)$setting['category_id']:0;

		if( $setting['list_type'] == 'bestseller' ) {
			$category_id = $setting['category_id']?(int)$setting['category_id']:0;
			if(!$category_id || $category_id == -1)
				$catsid = $this->getStoreCategories();
			else 
				$catsid = array($category_id);

			$date = new Zend_Date();
	      	$toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
	      	$fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');

		    $products   = $this->getCollectionPro()
											->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
											->addAttributeToSelect(array('name', 'price', 'small_image')) //edit to suit tastes
											->addStoreFilter()
											->addPriceData()
											->addTaxPercents()
											->addUrlRewrite()
											->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
										  ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))));

			$products->getSelect()
					->joinLeft(
						array('aggregation' => $products->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
						"e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
						array('SUM(aggregation.qty_ordered) AS sold_quantity')
						)
					->group('e.entity_id')
					->order(array('sold_quantity DESC', 'e.created_at'));

		} else if( $setting['list_type'] == 'special' ) {
			if(!$category_id || $category_id == -1)
				$catsid = $this->getStoreCategories();
			else 
				$catsid = array($category_id);

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
			         	 ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))))
			         	 ->groupByAttribute('entity_id');

	        $products ->getSelect()
	               		->where('price_index.final_price < price_index.price');


		} else if( $setting['list_type'] == 'featured' ) {
			if(!$category_id || $category_id == -1)
				$catsid = $this->getStoreCategories();
			else 
				$catsid = array($category_id);
			if($catsid) {
				$productIds = $this->getProductByCategory($catsid);

				$products = Mage::getResourceModel('catalog/product_collection')
						    ->addAttributeToSelect('*')
						    ->addMinimalPrice()
						    ->addUrlRewrite()
						    ->addTaxPercents()
						    ->addStoreFilter($storeId)
						    ->addIdFilter($productIds)
						    ->addAttributeToFilter("featured", 1)
						    ->setOrder('created_at', Varien_Db_Select::SQL_DESC);
			} else {
				$products = Mage::getResourceModel('catalog/product_collection')
						    ->addAttributeToSelect('*')
						    ->addMinimalPrice()
						    ->addUrlRewrite()
						    ->addTaxPercents()
						    ->addStoreFilter($storeId)
						    ->addAttributeToFilter("featured", 1)
						    ->setOrder('created_at', Varien_Db_Select::SQL_DESC);
			}
			

		} else {	

			if(!$category_id || $category_id == -1)
				$catsid = $this->getStoreCategories();
			else 
				$catsid = array($category_id);

			$todayStartOfDayDate  = Mage::app()->getLocale()->date()
						            ->setTime('00:00:00')
						            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      		$todayEndOfDayDate  = Mage::app()->getLocale()->date()
						            ->setTime('23:59:59')
						            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

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
		         				->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $catsid))))
				          		->addAttributeToSort('news_from_date', 'desc')
				          		->addAttributeToSort("created_at", "DESC")
				          		->addAttributeToSort("updated_at", "DESC")
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');
    	}

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
       
        $products->setPage(1, (int)$setting['limit']);

        $products = $this->_addProductAttributesAndPrices( $products );

  		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
					   "max_items" 		=> $setting['max_items'],
					   "limit_cols" 	=> $setting['limit_cols'],
					   "auto_play" 		=> $setting['auto_play'],
					   "speed" 			=> $setting['speed'],
					   "image_width"	=> $setting['image_width'],
					   "image_height"	=> $setting['image_height'],
					   "products" 		=> $products);

		$output = $this->renderLayoutHtml( "product_carousel", $data );

  		return $output;
	}
	/**
	 *
	 */
	public function renderWidgetStatic_blockContent(  $args, $setting, $widget_name= "" ){

		$t  = array(
			'name'			=> '',
			'show_name'		=> 1,
			'static_id'   		=> ''
		);
		$setting = array_merge( $t, $setting );
		$html = '';

		if( isset($setting['static_id']) && $setting['static_id']){
			$html = Mage::getSingleton('core/layout')
						->createBlock('cms/block')
						->setData('area','frontend')
						->setBlockId($setting['static_id'])->toHtml();
		}

		$data = array("widget_name" 	=> $widget_name,
					   "show_name" 		=> $setting['show_name'],
						"html" 			=> $html);
  		$output = $this->renderLayoutHtml( "html", $data );

  		return $output;
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
      }catch (Exception $e){
            Mage::logException($e->getMessage());
      }
    }
	/**
	 *
	 */
	public function renderContent( $id ){
		$output = '<div class="ves-widget" data-id="wid-'.$id.'">';
		if(empty($this->widgets)){
			$this->loadWidgets();
		}

		if( isset($this->widgets[$id]) ){
			$output .= $this->getWidgetContent( $this->widgets[$id]->getType(), unserialize(base64_decode($this->widgets[$id]->getParams())), $this->widgets[$id]->getName() );
		}
		$output .= '</div>';
		return $output;
	}
	/**
	 *
	 */
	public function loadWidgets(){
		if( empty($this->widgets) ){
			$widgets = $this->getCollection();
			foreach( $widgets as $widget ){
				$this->widgets[$widget->getId()] =$widget;
			}
		}
	}
	 /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
    	$test = Mage::getSingleton('catalog/config')->getProductAttributes();

        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addUrlRewrite();
    }
	/**
	 *
	 */
	protected function renderLayoutHtml( $layout, $data = array() ) {
		$custom_layout = (isset($data['layout'])&&$data['layout'])?"_".$data['layout']:'';
		$templatePath = 'ves' . DS . 'megamenu'.DS.'widgets'.DS.$layout.$custom_layout.'.phtml';

        $output = Mage::app()->getLayout()
            ->createBlock("core/template")
            ->setData('area','frontend')
            ->setData('widget', $data)
            ->setTemplate($templatePath)
            ->toHtml();
		return $output;
	}
}

