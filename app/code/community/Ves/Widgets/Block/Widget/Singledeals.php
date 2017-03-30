<?php
class Ves_Widgets_Block_Widget_Singledeals extends Mage_Catalog_Block_Product_View implements Mage_Widget_Block_Interface{
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );

		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		} elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		} else{
			$my_template = "ves/widgets/singledeals.phtml";
		}
		$this->setTemplate($my_template);
		
	}

	public function _toHtml(){
		return parent::_toHtml();
	}

	public function getProduct(){
		$product = '';
		$id_path = $this->getData('id_path');
		$arr = explode('/', $id_path);
		$product_id = end($arr);
		if($product_id){
			$product = Mage::getModel('catalog/product')->load($product_id);
		}
		return $product;
	}

	protected function _prepareLayout()
	{
		$this->getLayout()->getBlock('head')->addJs('ves/widgets/countdown.js');
		return parent::_prepareLayout();
	}

}