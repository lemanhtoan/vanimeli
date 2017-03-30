<?php

class Webtex_Giftcards_Block_Adminhtml_Card_Edit_Tab_Renderer_Balance extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $card = Mage::registry('giftcards_data');
        $_card  = Mage::getModel('giftcards/giftcards')->load($row->getData('id_giftcard'));
        $_order = Mage::getModel('sales/order')->load($row->getData('id_order'));
        $balance = Mage::helper('giftcards')->currencyConvert($card->getData('temp_amount'), $_card->getCardCurrency(), $_order->getOrderCurrencyCode()) - 
                   Mage::helper('giftcards')->currencyConvert($row->getData($this->getColumn()->getIndex()), $_card->getCardCurrency(), $_order->getOrderCurrencyCode());
        $card->setData('temp_amount', $balance);
        return Mage::helper('core')->currency($balance);
    }
}