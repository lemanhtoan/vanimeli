<?php

require_once 'Mage/Checkout/controllers/CartController.php';
class Webtex_Giftcards_CartController extends Mage_Checkout_CartController
{
    public function activateGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $amount = (float)$this->getRequest()->getParam('giftcard_amount');
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if($amount > $card->getCurrentBalance()){
            $this->_getSession()->addError(
                $this->__('Invalid Card Amount')
            );
            $this->_goBack();
        } else {

            $storeId = Mage::app()->getStore()->getId();
            $currDate = date('Y-m-d');

            if($card->getId() && ($card->getWebsiteId() == $storeId || $card->getWebsiteId() == 0) && (!$card->getDateEnd() || $card->getDateEnd() >= $currDate)){
                if ($card->getId() && ($card->getCardStatus() == 1)) {
                    $card->activateCard();

                    $this->_getSession()->addSuccess(
                        $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
                    );
                    Mage::getSingleton('giftcards/session')->setActive('1');
                    $this->_setSessionVars($card, $amount);
                    $this->_getQuote()->collectTotals()->save();
                } else {
                    if($card->getId() && ($card->getCardStatus() == 2)) {
                        $this->_getSession()->addError(
                            $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode))
                        );
                    } else {
                        $this->_getSession()->addError(
                            $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                        );
                    }
                }
            } else {
                $this->_getSession()->addError(
                    $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            }

            $this->_goBack();
        }
    }

    public function addGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');
        $response = array();
        $storeId = Mage::app()->getStore()->getId();
        $currDate = date('Y-m-d');

        if($card->getId() && ($card->getWebsiteId() == $storeId || $card->getWebsiteId() == 0) && (!$card->getDateEnd() || $card->getDateEnd() >= $currDate)){
            if ($card->getId() && ($card->getCardStatus() == 1)) {
                $card->activateCard();

                $response['succes'] = true;
                $response['message'] = $this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($giftCardCode));
                Mage::getSingleton('giftcards/session')->setActive('1');
                $this->_setSessionVars($card);
                $this->_getQuote()->collectTotals()->save();

            } else {
                if($card->getId() && ($card->getCardStatus() == 2)) {
                    $response['error'] = true;
                    $response['message'] = $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode));
                } else {
                    $response['error'] = true;
                    $response['message'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
        }
        $response['update'] = $this->getLayout()->createBlock('giftcards/items')->toHtml();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    public function removegiftcardAction()
    {
        $cardId = $this->getRequest()->getParam('id');
        $_session = Mage::getSingleton('giftcards/session');
        $cards = $_session->getCards();
        $cardCode = $cards[$cardId]['card_code'];
        unset($cards[$cardId]);
        if(empty($cards))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }

        $_session->setCards($cards);
        $this->_getQuote()->collectTotals()->save();

        $result = array('success' => true,
            'message' => $this->__('Gift Card "%s" was succefuly removed.', Mage::helper('core')->escapeHtml($cardCode)),
            'update' => $this->getLayout()->createBlock('giftcards/checkout_coupon')->toHtml(),
            'table' => $this->getLayout()->createBlock('giftcards/checkout_items')->toHtml());
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function applyamountAction()
    {
        $_session = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cardAmount = $this->getRequest()->getParam('amount', false);

        $card = Mage::getModel('giftcards/giftcards')->load($cardId);

        if(!$cardAmount){
            $cardAmount = min($card->getCurrentBalance(), Mage::helper('giftcards')->getCleanGrandTotal());
        }

        if($cardAmount > $card->getCurrentBalance()){
            $result = array('error' => true,
                            'message' => $this->__('Invalid Card Amount'),
                            'update' => $this->getLayout()->createBlock('giftcards/checkout_coupon')->toHtml(),
                            'table' => $this->getLayout()->createBlock('giftcards/checkout_items')->toHtml());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {

            $cards = $_session->getCards();

            $cards[$cardId] =  array('card_code' => $card->getCardCode(),
                                     'card_balance' => $card->getCurrentBalance()-$cardAmount, //$card->getCurrentBalance(),
                                     'base_card_balance' => $card->getBaseBalance()-$cardAmount, //$card->getBaseBalance(),
                                     'original_card_balance' => $card->getCurrentBalance(),
                                     'original_base_card_balance' => $card->getBaseBalance());
                
            $amounts[$card->getId()] = $cardAmount;
            $_session->setAmounts($amounts);
            $_session->setCards($cards);
            $this->_getQuote()->collectTotals()->save();

            $result = array('success' => true,
                            'message' => $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($card->getCardCode())),
                            'update' => $this->getLayout()->createBlock('giftcards/checkout_coupon')->toHtml(),
                            'table' => $this->getLayout()->createBlock('giftcards/checkout_items')->toHtml());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }


    public function deActivateGiftCardAction()
    {
        $_session = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cards  = $_session->getCards();

        unset($cards[$cardId]);
        if(empty($cards))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }
        $_session->setCards($cards);
        $this->_getQuote()->collectTotals()->save();

        $this->_goBack();
    }

    private function _setSessionVars($card, $amount)
    {
        $_session = Mage::getSingleton('giftcards/session');

        if($amount == 0){
            $amount = min($card->getCurrentBalance(), Mage::helper('giftcards')->getCleanGrandTotal());
        }

        $cards = $_session->getCards();
        if(!$cards){
            $cards = array();
        }

        $cards[$card->getId()] =  array('card_code' => $card->getCardCode(),
                                        'card_balance' =>  $card->getCurrentBalance()-$amount, //$card->getCurrentBalance(),
                                        'base_card_balance' => $card->getBaseBalance()-$amount, //$card->getBaseBalance(),
                                        'original_card_balance' => $card->getCurrentBalance(),
                                        'original_base_card_balance' => $card->getBaseBalance());
        $amounts[$card->getId()] = $amount;
        $_session->setAmounts($amounts);
        $_session->setCards($cards);
    }

    public function agreeToUseAction()
    {

        $q = Mage::getSingleton('giftcards/session')->getActive() ? 0 : 1;
        Mage::getSingleton('giftcards/session')->setActive($q);

        if($q == 0){
            Mage::getSingleton('giftcards/session')->clear();
        }

        $result['goto_section'] = 'payment';
        $this->_getQuote()->collectTotals()->save();
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => $this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function ajaxActivateGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $amount = (float)$this->getRequest()->getParam('giftcard_amount');

        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if($amount == 0){
            $amount = min($card->getCurrentBalance(), Mage::helper('giftcards')->getCleanGrandTotal());
        }

        $storeId = Mage::app()->getStore()->getId();
        $currDate = date('Y-m-d');

        if($card->getId() && ($card->getWebsiteId() == $storeId || $card->getWebsiteId() == 0) && (!$card->getDateEnd() || $card->getDateEnd() >= $currDate)){
            if ($card->getId() && ($card->getCardStatus() == 1)) {

                Mage::getSingleton('giftcards/session')->setActive('1');
                $this->_setSessionVars($card, $amount);
                $this->_getQuote()->collectTotals();

            } else {
                if($card->getId() && ($card->getCardStatus() == 2)) {
                    $result['error'] = $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode));
                } else {
                    $result['error'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
                }
            }
        } else {
            $result['error'] = $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
        }

        $result['goto_section'] = 'payment';
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => $this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function ajaxDeActivateGiftCardAction()
    {
        $_session = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $cards = $_session->getCards();

        unset($cards[$cardId]);

        if(empty($cards))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }

        $_session->setCards($cards);

        $this->_getQuote()->collectTotals()->save();

        $result['goto_section'] = 'payment';
        $result['update_section'] = array(
            'name' => 'payment-method',
            'html' => $this->_getPaymentMethodsHtml()
        );
        $result['giftcard_section'] = array(
            'html' => $this->_getUpdatedCoupon()
        );

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load(array('checkout_onepage_paymentmethod', 'giftcard_onepage_coupon'));
        $layout->generateXml();
        $layout->generateBlocks();
        $layout->removeOutputBlock('gc');
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getUpdatedCoupon()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load(array('checkout_onepage_paymentmethod', 'giftcard_onepage_coupon'));
        $layout->generateXml();
        $layout->generateBlocks();
        $layout->removeOutputBlock('root');
        $output = $layout->getOutput();
        return $output;
    }

    public function activateCheckoutGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $amount = (float)$this->getRequest()->getParam('giftcard_amount');

        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if($amount == 0){
            $amount = min($card->getCurrentBalance(), Mage::helper('giftcards')->getCleanGrandTotal());
        }

        $storeId = Mage::app()->getStore()->getId();
        $currDate = date('Y-m-d');

        if($card->getId() && ($card->getWebsiteId() == $storeId || $card->getWebsiteId() == 0) && (!$card->getDateEnd() || $card->getDateEnd() >= $currDate) && $card->getCardStatus() == 1){
        // if ($card->getId() && ($card->getCardStatus() == 1)) {

            $card->activateCard();

            Mage::getSingleton('core/session')->addSuccess(
                $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
            );

            Mage::getSingleton('giftcards/session')->setActive('1');

            $this->_setSessionVars($card, $amount);
            $this->_getQuote()->collectTotals()->save();
        } else {
            if($card->getId() && ($card->getCardStatus() == 2)) {
                Mage::getSingleton('core/session')->addError(
                    $this->__('Gift Card "%s" was used.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            } else {
                Mage::getSingleton('core/session')->addError(
                    $this->__('Gift Card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            }
        }
    }

    public function deActivateCheckoutGiftCardAction()
    {
        $_session = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $redirect = $this->getRequest()->getParam('redirect', 'onepage');
        $cards = $_session->getCards();

        unset($cards[$cardId]);
        if(empty($cards))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }

        $_session->setCards($cards);
        $this->_getQuote()->collectTotals()->save();
        $this->_redirect($redirect);
        return;
    }


}
