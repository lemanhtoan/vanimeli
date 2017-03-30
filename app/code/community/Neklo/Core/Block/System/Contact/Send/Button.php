<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Block_System_Contact_Send_Button extends Mage_Adminhtml_Block_Template
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getButton()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button
            ->setType('button')
            ->setLabel($this->__('Send'))
            ->setStyle("width:280px")
            ->setId('neklo_core_contact_send')
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
}