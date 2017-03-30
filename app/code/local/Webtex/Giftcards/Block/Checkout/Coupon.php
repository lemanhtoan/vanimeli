<?php
/*
*
*
*
*/

class Webtex_Giftcards_Block_Checkout_Coupon extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('webtex/giftcards/checkout/onepage/coupon.phtml');
    }

    public function isGiftCard()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        foreach($quote->getAllVisibleItems() as $item){
            if($item->getProduct()->getTypeId() != 'giftcards'){
                return false;
            }
        }
        return true;
    }

}