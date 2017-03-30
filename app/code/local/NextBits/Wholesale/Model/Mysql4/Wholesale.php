<?php

class NextBits_Wholesale_Model_Mysql4_Wholesale extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the wholesale_id refers to the key field in your database table.
        $this->_init('wholesale/wholesale', 'wholesale_id');
    }
}