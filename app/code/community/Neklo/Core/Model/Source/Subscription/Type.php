<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Model_Source_Subscription_Type
{
    const UPDATE_CODE  = 'UPDATE';
    const UPDATE_LABEL = 'My extensions updates';

    const RELEASE_CODE  = 'RELEASE';
    const RELEASE_LABEL = 'New Releases';

    const UPDATE_ALL_CODE  = 'UPDATE_ALL';
    const UPDATE_ALL_LABEL = 'All extensions updates';

    const PROMO_CODE  = 'PROMO';
    const PROMO_LABEL = 'Promotions / Discounts';

    const INFO_CODE  = 'INFO';
    const INFO_LABEL = 'Other information';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::UPDATE_CODE,
                'label' => Mage::helper('neklo_core')->__(self::UPDATE_LABEL),
            ),
            array(
                'value' => self::RELEASE_CODE,
                'label' => Mage::helper('neklo_core')->__(self::RELEASE_LABEL),
            ),
            array(
                'value' => self::UPDATE_ALL_CODE,
                'label' => Mage::helper('neklo_core')->__(self::UPDATE_ALL_LABEL),
            ),
            array(
                'value' => self::PROMO_CODE,
                'label' => Mage::helper('neklo_core')->__(self::PROMO_LABEL),
            ),
            array(
                'value' => self::INFO_CODE,
                'label' => Mage::helper('neklo_core')->__(self::INFO_LABEL),
            )
        );
    }
}