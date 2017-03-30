<?php

class NWT_Languagepack_Model_Languagepack extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('languagepack/languagepack');
    }
}