<?php

class Ves_ProductCarousel_Model_System_Config_Source_ListAttributes
{
 public function toOptionArray()
    {
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
		    ->getItems();

		$array = array(array('value'=>'', 'label'=>Mage::helper('ves_productcarousel')->__('-- Select a attribute --')));
		foreach ($attributes as $attribute){
			$tmp = array();
			$tmp['value'] = $attribute->getAttributecode();
		    $tmp['label'] = $attribute->getFrontendLabel();
		    $array[] = $tmp;
		}
		return $array;
	}
}