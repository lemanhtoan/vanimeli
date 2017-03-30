<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Block_Adminhtml_System_Config_Frontend_Oauth_Connect extends Mage_Adminhtml_Block_Template
{
    protected $_api = null;

    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getButton()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button
            ->setType('button')
            ->setLabel($this->__('Connect'))
            ->setStyle("width:280px")
            ->setId('neklo_instagram_oauth')
        ;
        return $button;
    }

    /**
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getButton()->toHtml();
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return parent::getContainerId();
    }

    public function getLoginUrl()
    {
        return $this->_getApi()->getLoginUrl(array('basic', 'public_content'));
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
}