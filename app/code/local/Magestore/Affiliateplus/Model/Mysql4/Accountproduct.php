<?php

class Magestore_Affiliateplus_Model_Mysql4_Accountproduct extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('affiliateplus/accountproduct', 'accountproduct_id');
    }
}