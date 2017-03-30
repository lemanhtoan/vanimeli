<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Adminhtml_Neklo_Core_ContactController extends Mage_Adminhtml_Controller_Action
{
    const CONTACT_URL = 'http://store.neklo.com/neklo_support/index/index/';

    public function sendAction()
    {
        $result = array(
            'success' => true,
        );
        try {
            $data = $this->getRequest()->getPost();
            $data['version'] = Mage::getVersion();
            $data['url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $data['id'] = '504-624-961';
            $this->_sendContactEmail($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $result['success'] = false;
            $this->getResponse()->setBody(Zend_Json::encode($result));
            return;
        }
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    protected function _sendContactEmail($data)
    {
        $params = Mage::helper('core')->urlEncode(Mage::helper('core')->jsonEncode($data));
        if ($params) {
            $httpClient = new Varien_Http_Client();
            $httpClient
                ->setMethod(Zend_Http_Client::POST)
                ->setUri(self::CONTACT_URL)
                ->setConfig(
                    array(
                        'maxredirects' => 0,
                        'timeout'      => 30,
                    )
                )
                ->setRawData($params)
                ->request()
            ;
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/neklo_core');
    }
}