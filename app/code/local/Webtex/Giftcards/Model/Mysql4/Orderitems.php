<?php

class Webtex_Giftcards_Model_Mysql4_Orderitems extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('giftcards/order_items', 'id');
    }

    public function loadItem($cardId, $quoteId, $quoteItemId) {
        $select = $this->_getReadAdapter()->select()->from($this->getTable('giftcards/order_items'))->where('`giftcard_id` = ' . $cardId . ' AND `quote_id` = ' . $quoteId . ' AND `quote_item_id` = ' . $quoteItemId);
        return $this->_getReadAdapter()->fetchRow($select);
    }
    
    public function loadOrderItem($cardId, $orderId, $orderItemId) {
        $select = $this->_getReadAdapter()->select()->from($this->getTable('giftcards/order_items'))->where('`giftcard_id` = ' . $cardId . ' AND `order_id` = ' . $orderId . ' AND `order_item_id` = ' . $orderItemId);
        return $this->_getReadAdapter()->fetchRow($select);
    }
}