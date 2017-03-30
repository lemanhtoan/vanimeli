<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Adminhtml_Neklo_Instagram_ApiController extends Mage_Adminhtml_Controller_Action
{
    protected $_api = null;

    public function connectAction()
    {
        $code = $this->getRequest()->getParam('code', null);
        if ($code === null) {
            $this->_getConfig()->disconnect();
            $this->loadLayout();
            $this->renderLayout();
            $this->_getSession()->addError(Mage::helper('neklo_instagram')->__('Incorrect Instagram authorization code.'));
            return $this;
        }
        try {
            $accessToken = $this->_getApi()->getOAuthToken($code, true);
        } catch (Exception $e) {
            $this->_getConfig()->disconnect();
            $this->loadLayout();
            $this->renderLayout();
            $this->_getSession()->addError(Mage::helper('neklo_instagram')->__($e->getMessage()));
            return $this;
        }

        if (!$accessToken) {
            $this->_getConfig()->disconnect();
            $this->loadLayout();
            $this->renderLayout();
            $this->_getSession()->addError(Mage::helper('neklo_instagram')->__('Incorrect Instagram authorization code.'));
            return $this;
        }

        $this->_getConfig()->connect($accessToken);
        $this->loadLayout();
        $this->renderLayout();
        $this->_getSession()->addSuccess(Mage::helper('neklo_instagram')->__('Instagram connect is successful.'));
        return $this;
    }

    public function disconnectAction()
    {
        $this->_getConfig()->disconnect();
        $this->_getSession()->addSuccess(Mage::helper('neklo_instagram')->__('Instagram disconnect is successful.'));
        return $this->_redirect('adminhtml/system_config/edit', array('section' => 'neklo_instagram'));
    }

    public function getRedirectUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/neklo_instagram_api/connect");
    }

    /**
     * @return Neklo_Instagram_Model_Instagram_Api
     */
    protected function _getApi()
    {
        if ($this->_api === null) {
            $this->_api = $this->_api = Mage::getModel(
                'neklo_instagram/instagram_api',
                array(
                    'apiKey'      => $this->_getConfig()->getClientId(),
                    'apiSecret'   => $this->_getConfig()->getClientSecret(),
                    'apiCallback' => $this->getRedirectUrl(),
                )
            );
        }
        return $this->_api;
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/neklo_instagram');
    }
}