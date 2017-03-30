<?php
if(Mage::helper("productlist")->checkModuleInstalled("Ves_PriceSlider")) {
	/**
	 * Catalog Price Slider
	 *
	 * @category   Venustheme
	 * @class    Ves_ProductList_Block_Catalog_Layer_Filter_Price
	 * @author     Mrugesh Mistry <core@magentocommerce.com>
	 */
	class Ves_ProductList_Block_Catalog_Layer_Filter_Price extends Ves_PriceSlider_Block_Catalog_Layer_Filter_Price 
	{
	    	
		public $_currentCategory;
		public $_searchSession;
		public $_productCollection;
		public $_maxPrice;
		public $_minPrice;
		public $_currMinPrice;
		public $_currMaxPrice;
		public $_imagePath;
		
		
		/*
		* 
		* Set all the required data that our slider will require
		* Set current _currentCategory, _searchSession, setProductCollection, setMinPrice, setMaxPrice, setCurrentPrices, _imagePath
		* 
		* @set all required data
		* 
		*/
		public function __construct(){
			parent::__construct();	
		}

		/*
		* Set the Product collection based on the page server to user 
		* Might be a category or search page
		*
		* @set /*
		* Set the Product collection based on the page server to user 
		* Might be a category or search page
		*
		* @set Mage_Catalogsearch_Model_Layer 
		* @set Mage_Catalog_Model_Layer    
		*/
		public function setProductCollection(){
			if($current_rule = Mage::registry('current_rule') ){
				$layer = Mage::getSingleton('productlist/layer');
				$this->_productCollection = $layer->getProductCollection();	
			} else {
				parent::setProductCollection();
			}
						
		}

	}
} else {
	class Ves_ProductList_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price 
	{
		/*
		* 
		* Set all the required data that our slider will require
		* Set current _currentCategory, _searchSession, setProductCollection, setMinPrice, setMaxPrice, setCurrentPrices, _imagePath
		* 
		* @set all required data
		* 
		*/
		public function __construct(){
			parent::__construct();	
		}

	}
}
