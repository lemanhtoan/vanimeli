<?php

class Webtex_Giftcards_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('giftcards');
    }

    public function getCards()
    {
        if(!$this->getData('cards')){
            return array();
        } else {
            return $this->getData('cards');  
        }
    }

}
