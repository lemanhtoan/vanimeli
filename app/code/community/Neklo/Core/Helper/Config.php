<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Helper_Config extends Mage_Compiler_Helper_Data
{
    const NOTIFICATION_TYPE = 'neklo_core/notification/type';

    /**
     * @param null|int|Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getNotificationTypeList($store = null)
    {
        return explode(',', Mage::getStoreConfig(self::NOTIFICATION_TYPE, $store));
    }
}