<?php

class Webtex_Giftcards_Model_Giftcards extends Mage_Core_Model_Abstract
{
    /**
     * Cards statuses
     * INACTIVE - just created
     * USED - gift card used and its balance is 0
     * ACTIVE - ready for using for discount
     */
    const INACTIVE = 0;
    const ACTIVE = 1;
    const USED = 2;
    const EXPIRED = 3;
    const REFUNDED = 4;
    
    protected $_helper;

    protected function _construct()
    {
        $this->_init('giftcards/giftcards');
        $this->_helper = Mage::helper('giftcards');
        parent::_construct();
    }

    public function activateCard()
    {
        $this->setCardStatus(1);
        $this->save();
    }

    /**
     * Assign card to customer and set active (ready for use)
     *
     * @param $customerId
     */
    public function activateCardForCustomer($customerId)
    {
        if ($this->getId() && $customerId) {
            $this->setCardStatus(1);
            $this->setCustomerId($customerId);
            $this->save();
        }
    }

    /**
     * Generates unique gift card code string
     *
     * @return string
     */
    public function getUniqueCardCode()
    {
        $cardCodes = $this->getResourceCollection()->getColumnValues('card_code');
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $mask = '#####-#####-#####';

        do {
            $cardCode = $mask;
            while (strpos($cardCode, '#') !== false) {
                $cardCode = substr_replace($cardCode, $characters[mt_rand(0, strlen($characters)-1)], strpos($cardCode, '#'), 1);
            }
        } while (in_array($cardCode, $cardCodes));
        
        return $cardCode;
    }

    protected function _sendEmailCard($storeId = 0)
    {
        if($order = Mage::getModel('sales/order')->load($this->getOrderId())){
            $storeId = $order->getStoreId();
        } else {
            $storeId = 1;
        }

        $amount = Mage::app()->getStore($storeId)->formatPrice($this->getCardAmount(), false);
  
        if(Mage::helper('giftcards')->isUseDefaultPicture() || !$this->getProductId()) {
           $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
        } else {
           $product = Mage::getModel('catalog/product')->load($this->getProductId());
           if (!$product->getId() || $product->getImage() != 'no_selection') {
               $picture = Mage::helper('catalog/image')->init($product, 'image');
           } else {
                $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
           }
        }
         $post = array(
             'amount'        => $amount, //$this->_addCurrencySymbol($amount,$this->getCardCurrency()),
             'code'          => $this->getCardCode(),
             'email-to'      => $this->getMailTo(),
             'email-from'    => $this->getMailFrom(),
             'recipient'     => $this->getMailToEmail(),
             'email-message' => nl2br($this->getMailMessage()),
             'store-phone'   => Mage::getStoreConfig('general/store_information/phone'),
             'picture'       => $picture,
             );

         $mail = trim($this->getMailToEmail()) ;
         
         if(empty($mail)) {
              $mail = $order->getCustomerEmail() ;
         }

         $this->_send($post, 'giftcards/email/email_template', $mail, $storeId);
    }

    protected function _sendPrintCard($storeId)
    {
        if($order = Mage::getModel('sales/order')->load($this->getOrderId())){
            $storeId = $order->getStoreId();
        } else {
            $storeId = 1;
        }

        $amount = Mage::app()->getStore($storeId)->formatPrice($this->getCardAmount(), false);

        if(Mage::helper('giftcards')->isUseDefaultPicture() || !$this->getProductId()) {
           $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
        } else {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            if (!$product->getId() || $product->getImage() != 'no_selection') {
                $picture = Mage::helper('catalog/image')->init($product, 'image');
            } else {
                $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
            }
        }

        $post = array(
            'amount'        => $amount, //$this->_addCurrencySymbol($amount,$this->getCardCurrency()),
            'code'          => $this->getCardCode(),
            'email-to'      => $this->getMailTo(),
            'email-from'    => $this->getMailFrom(),
            'link'          => Mage::app()->getStore($storeId)->getUrl('customer/giftcards/print/', array('code' => $this->getCardCode())),
            'email-message' => nl2br($this->getMailMessage()),
            'store-phone'   => Mage::getStoreConfig('general/store_information/phone'),
            'picture'       => $picture,
        );
        $this->_send($post, 'giftcards/email/print_template', $order->getCustomerEmail(), $storeId);
    }

    protected function _sendOfflineCard($storeId)
    {
        if($order = Mage::getModel('sales/order')->load($this->getOrderId())){
            $storeId = $order->getStoreId();
        } else {
            $storeId = 1;
        }

        $amount = Mage::app()->getStore($storeId)->formatPrice($this->getCardAmount(), false);

        if(Mage::helper('giftcards')->isUseDefaultPicture() || !$this->getProductId()) {
             $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
        } else {
             $product = Mage::getModel('catalog/product')->load($this->getProductId());
             if (!$product->getId() || $product->getImage() != 'no_selection') {
                 $picture = Mage::helper('catalog/image')->init($product, 'image');
             } else {
                 $picture = Mage::getDesign()->getSkinUrl('images/giftcard.png',array('_area'=>'frontend'));
             }
        }
        $post = array(
            'amount'        => $amount, //$this->_addCurrencySymbol($amount,$this->getCardCurrency()),
            'code'          => $this->getCardCode(),
            'email-to'      => $this->getMailTo(),
            'email-from'    => $this->getMailFrom(),
            'link'          => Mage::app()->getStore($storeId)->getUrl('customer/giftcards/print/', array('id' => $this->getId())),
            'email-message' => nl2br($this->getMailMessage()),
            'store-phone'   => Mage::getStoreConfig('general/store_information/phone'),
            'picture'       => $picture,
        );
        $this->_send($post, 'giftcards/email/offline_template', $order->getCustomerEmail(), $storeId);
    }


    protected function _send($post, $template, $email, $storeId)
    {
        if ($email) {
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            $postObject = new Varien_Object();
            $postObject->setData($post);
            $postObject->setStoreId($storeId);
            $mailTemplate = Mage::getModel('core/email_template');
            $pdfGenerator = new Webtex_Giftcards_Model_Email_Pdf();
            //$this->_addAttachment($mailTemplate, $pdfGenerator->getPdf($postObject), 'giftcard.pdf');
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional(
                    Mage::getStoreConfig($template, $storeId),
                    'general',
                    $email,
                    null,
                    array('data' => $postObject)
                );
            $translate->setTranslateInline(true);
        } else {
            throw new Exception('Invalid recipient email address.');
        }
    }

    private function _addAttachment($mailTemplate, $file, $filename){
        $attachment = $mailTemplate->getMail()->createAttachment($file);
        $attachment->type = 'application/pdf';
        $attachment->filename = $filename;
    }

    /**
     * Send card email (code, amount etc) to customer
     * TODO: remade this
     *
     * @param null $storeId
     */
    public function send($storeId = null)
    {
        if (!$storeId && $this->getData('order_id')) {
            $order = Mage::getModel('sales/order')->load($this->getData('order_id'));
            $storeId = $order->getStoreId();
        }
        if ($this->getData('card_type') == 'email') {
            $this->_sendEmailCard($storeId);
        } else if ($this->getData('card_type') == 'print') {
            $this->_sendPrintCard($storeId);
        } else if ($this->getData('card_type') == 'offline') {
            $this->_sendOfflineCard($storeId);
        }
        return true;
    }

    public function isValidCode($card)
    {
        $cardCodes = $this->getResourceCollection()->getColumnValues('card_code');
        $code = $card->getCardCode();
        if(in_array($code,$cardCodes) || empty($code)){
           return false;
        }
        return true;
    }

    /**
     * If card is just created, adds card code and set initial balance equal to card amount
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $code = $this->getData('card_code');
            if(!isset($code)) {
                $this->setData('card_code', $this->getUniqueCardCode());
            }
            $this->setData('card_balance', $this->getData('card_amount'));
        }
    }


   public function _addCurrencySymbol($amount, $currencyCode)
   {
        if(empty($currencyCode)) {
            $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        
        $currencySymbol = Mage::app()->getLocale()->currency($currencyCode)->getSymbol();

        $currencySymbol = str_replace(array('€', '£'), array('&euro', '&pound'), $currencySymbol);
        
        return $currencySymbol.$amount;
   }

   public function getOriginalBalance()
   {
       return $this->getData('card_balance');
   }

   public function getBaseBalance()
   {
       $originalCurrency = $this->getData('card_currency');
       if(!$originalCurrency){
            $originalCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
       }
       $baseCurrency     = Mage::app()->getStore()->getBaseCurrencyCode();
       if($originalCurrency != $baseCurrency) {
           return Mage::helper('giftcards')->currencyConvert($this->getData('card_balance'), $originalCurrency, $baseCurrency);
       }
       return $this->getData('card_balance');
   }

   public function getCurrentBalance()
   {
       $originalCurrency = $this->getData('card_currency');
       if(!$originalCurrency){
            $originalCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
       }
       $currentCurrency  = Mage::app()->getStore()->getCurrentCurrencyCode();
       if($originalCurrency != $currentCurrency) {
           return Mage::helper('giftcards')->currencyConvert($this->getData('card_balance'), $originalCurrency, $currentCurrency);
       }
       return $this->getData('card_balance');
   }
   
   public function getCardAmount($convertValue = false)
   {
       if($convertValue){
           $cardCurrency = $this->getCardCurrency();
           if(!$cardCurrency){
               $cardCurrency = Mage::app()->getStore()->getBaseCurrencyCode();
           }
           $currentCurrency  = Mage::app()->getStore()->getCurrentCurrencyCode();
           return $this->_helper->currencyConvert($this->getData('card_amount'), $cardCurrency, $currentCurrency);
       } else {
           return $this->getData('card_amount');
       }
   }
}
