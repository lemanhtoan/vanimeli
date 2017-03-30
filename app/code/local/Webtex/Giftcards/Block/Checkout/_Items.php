<?php
/*  Webtex GiftCards
 *
 *  Items.php
 *
 *  (C) Webtes Software 2015
 */

class Webtex_Giftcards_Block_Checkout_Items extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('webtex/giftcards/checkout/onepage/items.phtml');
    }
        
    public function getUsedGiftCards()
    {
        $usedGiftcards = array();
        $_session = Mage::getSingleton('giftcards/session');

        if($_session->getActive()) {
            foreach($_session->getGiftCardsIds() as $key => $_item){
                $usedGiftcards[] = array('giftcard_code' => $_item['code'], 'balance' => $_item['balance'], 'giftcard_id' => $key);
            }
        }

        return $usedGiftcards;
    }
    
}