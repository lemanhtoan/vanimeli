<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Block_Adminhtml_System_Config_Frontend_Oauth extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setScope(false);
        $element->setCanUseWebsiteValue(false);
        $element->setCanUseDefaultValue(false);
        return parent::render($element);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (!$this->_getConfig()->isConnected()) {
            $button = $this->getLayout()->createBlock('neklo_instagram_adminhtml/system_config_frontend_oauth_connect', 'neklo_instagram_oauth');
            $button->setTemplate('neklo/instagram/system/config/oauth/connect.phtml');
        } else {
            $button = $this->getLayout()->createBlock('neklo_instagram_adminhtml/system_config_frontend_oauth_disconnect', 'neklo_instagram_oauth');
            $button->setTemplate('neklo/instagram/system/config/oauth/disconnect.phtml');
        }
        $button->setContainerId($element->getContainer()->getHtmlId());
        return $button->toHtml();
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }
}