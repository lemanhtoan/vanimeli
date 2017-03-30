<?php

class Webtex_Giftcards_Model_Orderitems extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('giftcards/orderitems');
        parent::_construct();
    }

    public function loadItem($cardId, $quoteId, $itemId)
    {
        $this->setData($this->getResource()->loadItem($cardId, $quoteId, $itemId));
        return $this;
    }
    
    public function loadOrderItem($cardId, $orderId, $itemId)
    {
        $this->setData($this->getResource()->loadOrderItem($cardId, $orderId, $itemId));
        return $this;
    }

}
