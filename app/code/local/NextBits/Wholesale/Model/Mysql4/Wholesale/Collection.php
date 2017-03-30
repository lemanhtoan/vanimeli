<?php

class NextBits_Wholesale_Model_Mysql4_Wholesale_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('wholesale/wholesale');
    }
}