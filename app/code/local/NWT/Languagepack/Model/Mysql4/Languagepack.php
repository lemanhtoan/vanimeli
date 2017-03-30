<?php

class NWT_Languagepack_Model_Mysql4_Languagepack extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the languagepack_id refers to the key field in your database table.
        $this->_init('languagepack/languagepack', 'languagepack_id');
    }
}