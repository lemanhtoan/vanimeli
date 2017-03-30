<?php

class Ves_ProductList_Model_Mysql4_Reports_Collection extends Mage_Reports_Model_Resource_Product_Collection
{
	public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
    	if($attribute == 'best'){
            $this->getSelect()->order("t2.position " . $dir);
            return $this;
        }
        return parent::addAttributeToSort($attribute, $dir);
    }
}