<?php

class Webtex_Giftcards_Block_Adminhtml_Card_Edit_Tab_Renderer_Used extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $used = $row->getData($this->getColumn()->getIndex());
        $card = Mage::getModel('giftcards/giftcards')->load($row->getData('id_giftcard'));
        $_order = Mage::getModel('sales/order')->load($row->getData('id_order'));
        $amount =  $used >= 0 ? $used : '+'.-$used;
        $amount = Mage::helper('giftcards')->currencyConvert($amount, $card->getData('card_currency'), $_order->getOrderCurrencyCode());
        return Mage::helper('core')->currency($amount);
    }
}