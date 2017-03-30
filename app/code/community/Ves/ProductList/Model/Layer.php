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
class Ves_ProductList_Model_Layer extends Mage_Catalog_Model_Layer
{
	/**
     * Product collections array
     *
     * @var array
     */
	protected $_productCollections;

	/**
	 * Current Rule
	 * @var Ves_ProductList_Rule
	 */
	protected $_rule = null;

	protected $_rule_id = 0;

	public function setRule($rule = null) {
		$this->_rule = $rule;
		return $this;
	}

	public function setRuleId($rule_id = 0) {
		$this->_rule_id = $rule_id;
		return $this;
	}
	/**
	 * Retrive current rule
	 * @return Ves_ProductList_Model_Rule
	 */
	public function getRule() {
		$this->setData('_filterable_attributes',null);
		if($this->_rule){
			return $this->_rule;
		}
		$this->_rule = Mage::registry('current_rule');
		return $this->_rule;
	}

	/**
     * Retrieve current layer product collection
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
	public function getProductCollection( $is_block_mode = false)
	{
		if(isset($this->_productCollections)){
			$collection = $this->_productCollections;
		}else{
			$rule = $this->getRule();

			//If load product list collection in widget block
			if($is_block_mode) {
				$source_type = $rule->getSourceType();
	            if(in_array($source_type, array("most_viewed", "best_seller", "top_rate")) ) {
	                $collection = Mage::getResourceModel('reports/product_collection');
	            } else {
	                $collection = Mage::getResourceModel('productlist/product_collection');
	            }
			} else { //Is product list page
				$collection = Mage::getResourceModel('productlist/product_collection');
			}
			

			$ruleProductTable = Mage::getSingleton('core/resource')->getTableName('productlist/rule_product');
			$collection->getSelect()->where('t2.rule_id = (?)',$rule->getRuleId())->join(array('t2'=>$ruleProductTable),'e.entity_id = t2.rule_product_id');
			
			/*Apply data source type*/
            $collection = $rule->applySourceType( $collection , $is_block_mode);

			$this->prepareProductCollection($collection);
			if($product_number = $rule->getProductNumber()){
				$collection->setPageSize($product_number);
			}
			$this->_productCollections = $collection;
		}
		return $collection;
	}

	/**
     * Filter product collection
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Layer
     */
	public function prepareProductCollection($collection)
	{
		$rule = $this->getRule();
		if($rule->getData('show_outofstock') == 2){
			$collection->joinField('stock_status','cataloginventory/stock_status','stock_status',
				'product_id=entity_id', array(
					'stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,
					'website_id' => Mage::app()->getWebsite()->getWebsiteId(),
					));
		}

		$collection->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents();
		/*Filter min, max price*/
		$this->currentRate = Mage::app()->getStore()->getCurrentCurrencyRate();;
		$max=$this->getMaxPriceFilter();
		$min=$this->getMinPriceFilter();
		
		if($min && $max){
			$collection->getSelect()->where(' final_price >= "'.$min.'" AND final_price <= "'.$max.'" ');
		}
		/*End Filter min, max price*/
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

		//$this->_productCollections = $collection;
		return $this;
	}

	/*
	* convert Price as per currency
	*
	* @return currency
	*/
	public function getMaxPriceFilter(){
		return isset($_GET['max'])?round($_GET['max']/$this->currentRate):0;
	}
	
	
	/*
	* Convert Min Price to current currency
	*
	* @return currency
	*/
	public function getMinPriceFilter(){
		return isset($_GET['min'])?round($_GET['min']/$this->currentRate):0;
	}

}