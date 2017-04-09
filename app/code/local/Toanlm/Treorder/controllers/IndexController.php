<?php
class Toanlm_Treorder_IndexController extends Mage_Core_Controller_Front_Action{

    public function removeAjaxAction() {
      $productId = $_REQUEST['idproduct'];

      $cartHelper = Mage::helper('checkout/cart');
      $items = $cartHelper->getCart()->getItems();
      foreach($items as $item):
          if($item->getProduct()->getId() == $productId):
              $itemId = $item->getItemId();
              $cartHelper->getCart()->removeItem($itemId)->save();
              break;
          endif;
      endforeach;

      $response['status'] = 'SUCCESS';

      $totalExpress = 0;
      $tcart = Mage::getSingleton('checkout/session')->getQuote();
      foreach ($tcart->getAllItems() as $item) { 
          $productId = $item->getProduct()->getId();
          $product = Mage::getSingleton('catalog/product')->load($productId);
          if ($product->getFreshExpress() == '1') {
              $totalExpress += $item->getProduct()->getPrice()*$item->getQty();
          }

      }

      //New Code Here
      $this->loadLayout();
      $sidebar_block = $this->getLayout()->getBlock('cart_top');
      Mage::register('referrer_url', $this->_getRefererUrl());
      $sidebar = $sidebar_block->toHtml();
      $response['sidebar'] = $sidebar;
      $response['expressTotal'] = $totalExpress;

      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
      return;

    }
}