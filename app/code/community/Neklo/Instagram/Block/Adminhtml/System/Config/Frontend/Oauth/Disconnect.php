<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Block_Adminhtml_System_Config_Frontend_Oauth_Disconnect extends Mage_Adminhtml_Block_Template
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getButton()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button
            ->setType('button')
            ->setLabel($this->__('Disconnect'))
            ->setStyle("width:280px")
            ->setId('neklo_instagram_oauth')
            ->setClass('delete')
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

    public function getDisconnectUrl()
    {
        return Mage::helper("adminhtml")->getUrl("adminhtml/neklo_instagram_api/disconnect");
    }
}