<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Block_Adminhtml_System_Config_Frontend_Status extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setBold(true);

        if ($this->_getConfig()->isConnected()) {
            $element->setValue($this->__('Connected to Instagram'));
            $element->addClass('instagram_status')->addClass('success');
        } else {
            $element->setValue($this->__('Not connected to Instagram'));
            $element->addClass('instagram_status')->addClass('error');
        }
        return '<p id="'. $element->getHtmlId() . '_label" ' . $element->serialize($element->getHtmlAttributes()) . '>' . parent::_getElementHtml($element) . '</p><input id="' . $element->getHtmlId() . '" value="' . (int)$this->_getConfig()->isConnected() . '" type="hidden"/>';
    }

    protected function _prepareLayout()
    {
        /* @var $head Mage_Page_Block_Html_Head */
        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->addItem('skin_css', 'neklo/instagram/css/styles.css');
        }
        return parent::_prepareLayout();
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }
}
