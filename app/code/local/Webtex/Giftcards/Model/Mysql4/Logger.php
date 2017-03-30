<?php

class Webtex_Giftcards_Model_Mysql4_Logger extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('giftcards/logger', 'entity_id');
    }
}