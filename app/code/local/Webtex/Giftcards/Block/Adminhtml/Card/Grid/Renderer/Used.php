<?php

class Webtex_Giftcards_Block_Adminhtml_Card_Grid_Renderer_Used extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $cardOrders = Mage::getModel('giftcards/order')->getCollection()->addFieldToFilter('id_giftcard', $row->getId());
        $value = '';
        $orderModel = Mage::getModel('sales/order');
        if(count($cardOrders)){
            foreach($cardOrders as $order){
                $value .= '<a href="'.$this->getUrl('*/sales_order/view', array('order_id' => $order->getIdOrder())).'">#'.$orderModel->load($order->getIdOrder())->getIncrementId().'</a><br/>';
            }
        }
        return $value;
    }
}









