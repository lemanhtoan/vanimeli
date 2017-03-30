<?php

class Webtex_Giftcards_Adminhtml_Giftcards_GiftcardsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addBreadcrumb($this->__('Gift Cards'), $this->__('Gift Cards'));
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('giftcards/giftcards')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('giftcards_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('customer/giftcards');
            $this->_addBreadcrumb($this->__('Gift Cards'), $this->__('Gift Cards'));
            $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card_edit'))
                ->_addLeft($this->getLayout()->createBlock('giftcards/adminhtml_card_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__('Card does not exists'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function importAction()
    {
        $this->_title($this->__('Import Gift Cards from CSV'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_card_load'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $_logger = Mage::getModel('giftcards/logger');
        $user = Mage::getSingleton('admin/session')->getUser()->getFirstname() . ' ' . Mage::getSingleton('admin/session')->getUser()->getLastname();
        $action = 'Updated';
        
        if ($data = $this->getRequest()->getPost()) {
        
            $card_codes = array();
            
            if(!isset($data['card_count'])){
                $data['card_count'] = 1;
            }

            try {
                for($i = 1; $i <= $data['card_count']; $i++) {
                    $model = Mage::getModel('giftcards/giftcards');
                    if(strlen($data['card_currency']) != 3){
                        $data['card_currency'] = Mage::getStoreConfig('currency/options/base');
                    }
                    $model->setData($data);
                    $model->setId($this->getRequest()->getParam('id'));
                    $model->save();
                    $card_codes[$i]['code'] = $model->getCardCode();
                    $card_codes[$i]['amount'] = $model->getCardAmount();
                    $card_codes[$i]['currency'] = $model->getCardCurrency();
                    if(!$this->getRequest()->getParam('id')){
                        $action = 'Created';
                        if($data['card_count'] > 1){
                            $action = 'Bulk';
                        }
                    }
                    $model->load($model->getId());
                    $logData = array(
                        'card_action' => $action,
                        'card_id'     => $model->getId(),
                        'card_code'   => $model->getCardCode(),
                        'card_amount' => $model->getCardAmount(),
                        'card_balance'=> $model->getCardBalance(),
                        'card_status' => $model->getCardStatus(),
                        'user_name'   => $user,
                        'user'        => 'Staff',
                        );
                    $_logger->writelog($logData);
                }
                
                if(isset($data['save_codes'])){
                    if(!isset($data['save_path'])){
                        $data['save_path'] = Webtex_Giftcards_Block_Adminhtml_Card_Edit_Tab_Form::CODES_EXPORT_PATH . 'cardslist_'.date('d-m-Y-His') . '.csv';
                    }
                    $this->_printList($card_codes, $data['save_path']);
                }

                Mage::getSingleton('adminhtml/session')->setFormData(false);
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift Card(s) was successfully saved'));
                
                $this->_redirect('*/*/');
                return;
                    
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable find gift card data to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if (($id = $this->getRequest()->getParam('id')) > 0) {
            try {
                Mage::getModel('giftcards/giftcards')->load($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gift card was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $cardIds = $this->getRequest()->getParam('card');
        if (!is_array($cardIds)) {
            $this->_getSession()->addError($this->__('Please select gift card(s)'));
        } else {
            try {
                foreach ($cardIds as $cardId) {
                    Mage::getModel('giftcards/giftcards')->load($cardId)->delete();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d geftcard(s) were successfully deleted', count($cardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function resendAction()
    {
        try {
            if (($cardId = $this->getRequest()->getParam('id')) > 0) {
                $card = Mage::getModel('giftcards/giftcards')->load($cardId);
                if($card->getCardType() == 'email'){
                    $card->send();
                } else {
                    $this->_getSession()->addError($this->__('Unable to send this Gift Card type.'));
                }
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }

    public function massResendAction()
    {
        $cardIds = $this->getRequest()->getParam('card');
        if (!is_array($cardIds)) {
            $this->_getSession()->addError($this->__('Please select gift card(s)'));
        } else {
            try {
                foreach ($cardIds as $cardId) {
                    Mage::getModel('giftcards/giftcards')->load($cardId)->send();
                }
                $this->_getSession()->addSuccess(
                    $this->__('%d giftcard(s) were successfully resent', count($cardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function printAction()
    {
        if (($cardId = $this->getRequest()->getParam('id')) > 0) {
            echo $this->getLayout()->createBlock('giftcards/adminhtml_print')->toHtml();
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function activateGiftCardAction()
    {
        $giftCardCode = trim((string)$this->getRequest()->getParam('giftcard_code'));
        $amount = (float)$this->getRequest()->getParam('giftcard_amount');
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');
        $result = array();
        if($amount > $card->getCardBalance()){
            $this->_getSession()->addError(
                $this->__('Invalid Card Amount')
            );
        } else {
            $storeId = Mage::app()->getStore()->getWebsiteId();
            $currDate = date('Y-m-d');

            if($card->getId() && ($card->getWebsiteId() == $storeId || $card->getWebsiteId() == 0) && (!$card->getDateEnd() || $card->getDateEnd() >= $currDate)){
                if ($card->getId() && ($card->getCardStatus() == 1)) {
                    $card->activateCard();

                    $this->_getSession()->addSuccess(
                        $this->__('Gift Card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
                    );
                    Mage::getSingleton('giftcards/session')->setActive(1);
                    $this->_setSessionVars($card, $amount);
                    $result['success'] = true;
                    $result['card_code'] = $card->getCardCode();
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
        }
        die(json_encode($result));
    }



    public function deActivateGiftCardAction()
    {
        $_session = Mage::getSingleton('giftcards/session');
        $cardId = $this->getRequest()->getParam('id');
        $_cards = $_session->getCards();

        unset($_cards[$cardId]);
        if(empty($_cards))
        {
            Mage::getSingleton('giftcards/session')->clear();
        }
        exit;
    }

    private function _setSessionVars($card, $amount)
    {
        $_session = Mage::getSingleton('giftcards/session');

        if($amount == 0){
            $amount = min($card->getCurrentBalance(), Mage::helper('giftcards')->getAdminCleanGrandTotal());
        }

        $cards = $_session->getCards();
        if(!$cards){
            $cards = array();
        }

        $cards[$card->getId()] =  array('card_code' => $card->getCardCode(),
                                        'card_balance' => $card->getCurrentBalance(),
                                        'base_card_balance' => $card->getBaseBalance(),
                                        'original_card_balance' => $card->getCurrentBalance(),
                                        'original_base_card_balance' => $card->getBaseBalance());
                
        $_session->setCards($cards);
    }

    public function ajaxUpdateGiftCardBlockAction()
    {
        $oGiftCardSession = Mage::getSingleton('giftcards/session');
        $response = $oGiftCardSession->getFrontOptions();
        die(json_encode($response));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/giftcards');
    }

    private function _printList($cards, $path)
    {
        try {
            $io = new Varien_Io_File();
	    $fullPath = Mage::getBaseDir() . $path;
	    $parts = pathinfo($fullPath);
	    if(!isset($parts['extension']) || strtolower($parts['extension']) != 'csv'){
		Mage::throwException('Error in file extension. Only *.csv files are supported');
	    }

            $delimiter = ';';
            $enclosure = '"';
	    $io->open(array('path' => $parts['dirname']));
            $io->streamOpen($fullPath, 'w+');
            $io->streamLock(true);

	    $header = array('card_id'   => 'Gift Card Code',
	                    'amount'    => 'Card Amount',
	                    'currency'    => 'Card Currency',
	                    );
	    $io->streamWriteCsv($header, $delimiter, $enclosure);

            $content = array();
	    foreach($cards as $card){
	            $content['card_id'] = $card['code'];
	            $content['amount']  = $card['amount'];
	            $content['currency']  = $card['currency'];
	            $io->streamWriteCsv($content, $delimiter, $enclosure);
	    }
	    $io->streamUnlock();
            $io->streamClose();
            $list = Mage::getModel('giftcards/cardslist')->load($fullPath,'file_path');
            $list->setFilePath($fullPath)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('giftcards')->__('Gift Cards list was saved.'));
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__('An error occurred while save cards list.'));
        }
		
    }

}