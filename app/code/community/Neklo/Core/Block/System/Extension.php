<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Block_System_Extension extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderHtml($element)
    {
        return parent::_getHeaderHtml($element) . $this->_getContentHtml();
    }

    protected function _getContentHtml()
    {
        $extensionListBlock = $this->getLayout()->createBlock('neklo_core/system_extension_list', 'neklo_core_extension_list');
        $extensionListBlock->setTemplate('neklo/core/system/extension/list.phtml');
        return $extensionListBlock->toHtml();
    }
}