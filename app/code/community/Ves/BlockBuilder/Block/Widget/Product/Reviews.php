<?php
class Ves_BlockBuilder_Block_Widget_Product_Reviews extends Mage_Catalog_Block_Product_View implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/productbuilder/widget/reviews.phtml";
 		}
        $this->setTemplate($my_template);
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		if(!Mage::getStoreConfig("ves_productbuilder/general/show")) {
			return ;
		}

		/*
		$review_pager  = Mage::app()->getLayout()->createBlock('page/html_pager','product_review_list.toolbar');
		$review_count  = Mage::app()->getLayout()->createBlock('core/template','product_review_list.count')
												->setTemplate('review/product/view/count.phtml');
		$review_list  = Mage::app()->getLayout()->createBlock('review/product_view_list','product.info.product_additional_data')
												->setTemplate('review/product/view/list.phtml');
		$review_form  = Mage::app()->getLayout()->createBlock('review/form','product.review.form');

		$review_tab = Mage::app()->getLayout()->createBlock('catalog/product_view_tabs', 'product.info.custom.review')
											->setTemplate("ves/productbuilder/reviewtab.phtml");
		
		$review_tab->setChild("product_review_list.toolbar", $review_pager);
		$review_tab->setChild("review_count", $review_count);
		$review_tab->setChild("info_tabs", $review_list);
		$review_tab->setChild("review_form", $review_form);

		$this->setChild( "custom_review_form", $review_tab->toHtml() );*/

		return parent::_toHtml();
	}
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = ""){
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {

	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    }
	    return $default;
	}
}