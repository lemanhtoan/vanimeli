<?php 

class Webtex_Giftcards_Block_Coupon extends Mage_Checkout_Block_Cart_Abstract
{
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