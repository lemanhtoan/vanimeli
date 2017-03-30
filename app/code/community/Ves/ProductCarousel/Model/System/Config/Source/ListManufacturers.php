<?php
class Ves_ProductCarousel_Model_System_Config_Source_ListManufacturers {

    public function toOptionArray() {
        $manufacturers = $this->getAllManu();
        $arr = array();
        if ($manufacturers) {
            foreach ($manufacturers as $manu) {
                $tmp = array();
                $tmp["value"] = $manu['value'];
                $tmp["label"] = $manu['label'];
                $arr[] = $tmp;
            }
        }
        return $arr;
    }

	public function getAllManu()
    {
		$product = Mage::getModel('catalog/product');
		$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                  ->setEntityTypeFilter($product->getResource()->getTypeId())
                  ->addFieldToFilter('attribute_code', 'computer_manufacturers');
		$attribute = $attributes->getFirstItem()->setEntity($product->getResource());
		$manufacturers = $attribute->getSource()->getAllOptions(false);
		return $manufacturers;
    }
}