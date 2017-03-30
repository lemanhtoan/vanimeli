<?php

class Ves_ProductCarousel_Block_List extends Mage_Catalog_Block_Product_Abstract
{
	/**
	 * @var string $_config
	 *
	 * @access protected
	 */
	protected $_config = array();

	protected $_current_page = 1;

	/**
	 * @var string $_config
	 *
	 * @access protected
	 */
	protected $_listDesc = array();

	/**
	 * @var string $_config
	 *
	 * @access protected
	 */
	protected $_show = 0;

	protected $_theme = "";

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		$this->setDefaultSettings();

		$this->convertAttributesToConfig($attributes);

		$theme = ($this->getConfig('theme')!="") ? $this->getConfig('theme') : "default";

		parent::__construct();


		if(isset($attributes['template']) && $attributes['template']) {
			$this->setTemplate($attributes['template']);
		} elseif($this->hasData("template")) {
			$this->setTemplate($this->getData('template'));
		} elseif($this->getConfig("enable_owl_carousel", "carousel_setting", 0)) {
			$template = 'ves/productcarousel/default_owl.phtml';
			$this->setTemplate( $template );
		} else {
			$template = 'ves/productcarousel/default.phtml';
			$this->setTemplate( $template );
		}

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
          Ves_ProductCarousel_Model_Config::CACHE_BLOCK_TAG
        ));

        /*End Cache Block*/

	}
	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'VES_PRODUCTCAROUSEL_BLOCK_LIST',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

    public function setDefaultSettings() {
    	$groups = array("ves_productcarousel", "catalog_source_setting", "carousel_setting", "effect_setting");
    	$tmp_config = array();
    	foreach($groups as $group) {
    		$tmp_array_config = Mage::getStoreConfig('ves_productcarousel/'.$group); //array
    		if($tmp_array_config) {
	          foreach($tmp_array_config as $key => $val) {
	              if(!isset($tmp_config[$key])) {
	                $tmp_config[$key] = $val;
	              }
	          }
    		}
    	}
      $this->_config = array_merge($this->_config, $tmp_config);

    }

    public function convertAttributesToConfig($attributes = array()) {
      if($attributes) {
        foreach($attributes as $key=>$val) {
            $this->setConfig($key, $val);
        }
      }
    }
	public function _toHtml() {

		if( !$this->getConfig('show') ) return;
		$cms = "";

 		$cms_block_id = $this->getConfig('cmsblock');
 		if($cms_block_id){
 			$cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
 		}

		$items = $this->getListProducts();

		$this->assign( "items", $items );

		$this->assign( "cms", $cms );

        return parent::_toHtml();
	}

	public function getEffectConfig( $key ){
		return $this->getConfig( $key, "effect_setting" );
	}

	public function getCarouselConfig( $key, $default = null ){
		return $this->getConfig( $key, "carousel_setting", $default );
	}
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	function getConfig( $key, $panel='ves_productcarousel', $default = "" ){

		$return = "";
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {
	      if($key == "pretext") {
	      	$value = base64_decode($value);
	      }
	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }

	      return $value;

	    } else {

	      if(isset($this->_config[$key])){
	        	$return = $this->_config[$key];

		        if($return == "true") {
		            $return = 1;
		        } elseif($return == "false") {
		            $return = 0;
		        }
	      }else{
	        $return = Mage::getStoreConfig("ves_productcarousel/$panel/$key");
	      }
	      if($return == "" && $default) {
	        $return = $default;
	      }

	    }

	    return $return;
	}

	/**
     * overrde the value of the extension's configuration
     *
     * @return string
     */
    function setConfig($key, $value) {
		if($value == "true") {
	        $value =  1;
	    } elseif($value == "false") {
	        $value = 0;
	    }
    	if($value != "") {
	      	$this->_config[$key] = $value;
	    }
    	return $this;
    }



	function set($params){

	}
	public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
    		$text = ($is_striped==true)?strip_tags($text):$text;
    		if(strlen($text) <= $length){
    			return $text;
    		}
    		$text = substr($text,0,$length);
    		$pos_space = strrpos($text,' ');
    		return substr($text,0,$pos_space).$replacer;
	}
	public function getListProducts()
    {
    	$products = null;
    	$mode = $this->getConfig('source_products_mode', "catalog_source_setting" );
		switch ($mode) {
			case 'latest' :
				$products = $this->getListLatestProducts();
				break;
			case 'bestvalue' :
				$products = $this->getListBestValueProducts();
				break;
			case 'sale' :
				$products = $this->getListSaleProducts();
				break;
			case 'best_buy' :
				$products = $this->getListBestSellerProducts();
				break;
			case 'most_viewed' :
				$products = $this->getListMostViewedProducts();
				break;
			case 'featured' :
				$products = $this->getListFeaturedProducts();
				break;
			case 'top_rated' :
				$products = $this->getListTopRatedProducts();
				break;
			case 'attribute' :
				$products = $this->getListAttributeProducts();
				break;
			case 'random' :
				$products = $this->getListRandomProducts();
				break;
			default   :
				$products = $this->getListNewProducts();
				break;

		}
		return $products;
    }
    public function getListRandomProducts( )
    {
    	$fieldorder = 'created_at';
    	$order = 'desc';
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
		$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
           $arr_catsid =  array($cateids);
	    }else{
	        $arr_catsid = explode(",", $cateids);
	    }

        $resource = Mage::getSingleton('core/resource');

    	if($cateids && $cateids != "1") {
    		
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

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
      	$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);

      	$list = array();
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			$list = $products;
		}

		return $list;
    }
    public function getListBestValueProducts( )
    {
    	$fieldorder = 'position';
    	$order = 'DESC';
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
		$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
           $arr_catsid =  array($cateids);
	    }else{
	        $arr_catsid = explode(",", $cateids);
	    }

        $resource = Mage::getSingleton('core/resource');

    	if($cateids && $cateids != "1") {
    		
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
    	$products->addAttributeToSort($fieldorder, $order);

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
      	$this->setProductCollection($products);
		$this->_addProductAttributesAndPrices($products);

      	$list = array();
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			$list = $products;
		}

		return $list;
    }
	public function getListTopRatedProducts() {
		$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();

    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
      }
      $resource = Mage::getSingleton('core/resource');
      $products   = $this->getCollectionPro()
                   ->addAttributeToFilter(array( array('attribute' =>'visibility', array('neq'=>1))))
                   ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
		         	 		->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
		         	 		->groupByAttribute('entity_id');

			$products->joinField('rating_summary_field', 'review/review_aggregate', 'rating_summary', 'entity_pk_value=entity_id',  array('entity_type' => 1, 'store_id' => Mage::app()->getStore()->getId()), 'left');
			$products->addAttributeToSort('rating_summary_field', 'desc');

      Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
      Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

      $products->setPageSize( $limit )->setCurPage($this->_current_page);
      $this->setProductCollection($products);
			$this->_addProductAttributesAndPrices($products);

      $list = array();
			if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
				$list = $products;
			}

			return $list;
	}

	public function getListSaleProducts(){

		  $limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();

    	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
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

        $products ->getSelect()
               ->where('price_index.final_price < price_index.price');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;
	}

	public function getListNewProducts($fieldorder = 'updated_at', $order = 'desc')
    {
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();

    	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      	$todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
      }
      	$resource = Mage::getSingleton('core/resource');
    	 $products   = $this->getCollectionPro()
						        ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
				         		->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
						        ->addAttributeToSort('product_id', 'desc')
						        ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;

    }
    public function getListLatestProducts($fieldorder = 'updated_at', $order = 'desc')
    {
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();

    	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      	$todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
      }
      	$resource = Mage::getSingleton('core/resource');
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
						          ->addAttributeToSort('news_from_date', 'desc')
						          ->addAttributeToSort($fieldorder, $order)
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;

    }

    public function getListBestSellerProducts($fieldorder = 'ordered_qty', $order = 'desc')
    {

    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
      }

      $date = new Zend_Date();
      $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
      $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');

			if($this->getConfig('bestseller_from_date')!=''){
				$fromDate = $this->getConfig('bestseller_from_date');
			}

			if($this->getConfig('bestseller_to_date')!=''){
				$toDate = $this->getConfig('bestseller_to_date');
			}
		$resource = Mage::getSingleton('core/resource');
      $products   = $this->getCollectionPro()
									->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
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

      Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
      Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);

        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;
    }

    public function getListMostViewedProducts()
    {

    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$storeId    = Mage::app()->getStore()->getId();
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
	    }else{
	          $arr_catsid = explode(",", $cateids);
	    }
	    $resource = Mage::getSingleton('core/resource');
      $products   = $this->getCollectionPro('reports/product_collection')
									->addMinimalPrice()
									->addUrlRewrite()
									->addTaxPercents()
									->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
		         					->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
									->setStoreId($storeId)
									->addStoreFilter($storeId)
									->addViewsCount()
									->groupByAttribute('entity_id');


        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;
    }

    public function getListFeaturedProducts()
    {
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
			$limit = empty($limit)?6:(int)$limit;
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');

    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
          $arr_catsid =  array($cateids);
      }else{
          $arr_catsid = explode(",", $cateids);
      }

      $resource = Mage::getSingleton('core/resource');
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

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
				if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
					$list = $products;
				}

				return $list;
    }

    public function getListAttributeProducts()
    {
    	$limit = $this->getConfig('limit_item', 'catalog_source_setting');
		$limit = empty($limit)?6:(int)$limit;
    	$cateids = $this->getConfig('catsid', 'catalog_source_setting');
    	$attribute_code = $this->getConfig('attribute_code', 'catalog_source_setting');
    	$attribute_value = $this->getConfig('attribute_value', 'catalog_source_setting');
    	$attribute_value = !$attribute_value?"1":$attribute_value;

    	if(!$attribute_code) {
    		return false;
    	}
    	
    	$arr_catsid = array();
    	if(stristr($cateids, ',') === FALSE) {
            $arr_catsid =  array($cateids);
	     }else{
	        $arr_catsid = explode(",", $cateids);
	     }

      	$resource = Mage::getSingleton('core/resource');
      	$products = $this->getCollectionPro()
										    ->addMinimalPrice()
										    ->addUrlRewrite()
										    ->addTaxPercents()
									  		->addAttributeToFilter( array(
														    array( 'attribute'=>$attribute_code, 'eq' => $attribute_value )
														))
										    ->joinTable($resource->getTableName('catalog_category_product'), 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
										    ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $arr_catsid))))
								    		->addAttributeToSort('news_from_date','desc')
										    ->addAttributeToSort('created_at', 'desc')
										    ->addAttributeToSort('updated_at', 'desc')
										    ->groupByAttribute('entity_id');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->setPageSize( $limit )->setCurPage($this->_current_page);
        $this->setProductCollection($products);
				$this->_addProductAttributesAndPrices($products);

        $list = array();
		if (($_products = $this->getProductCollection ()) && $_products->getSize ()) {
			$list = $products;
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
      }catch (Exception $e){
            Mage::logException($e->getMessage());
      }
    }

    function inArray($source, $target) {

			for($i = 0; $i < sizeof ( $source ); $i ++) {
				if (in_array ( $source [$i], $target )) {
				return true;
				}
			}
    }
}
