<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function __()
    {
        $args = func_get_args();
        if ($args[0] == '{{connect_hint}}') {
            if ($this->_getConfig()->isConnected()) {
                return '';
            }
            $args[0] = 'Add <b>%s</b> to redirect urls for Instagram application';
            $args[1] = Mage::helper("adminhtml")->getUrl("adminhtml/neklo_instagram_api/connect");
        }
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_getModuleName());
        array_unshift($args, $expr);
        return Mage::app()->getTranslator()->translate($args);
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }
}
