<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Model_Source_Mode
{
    const BY_USER_ID_CODE          = 1;
    const BY_USER_ID_LABEL         = 'By User ID';

    const BY_HASHTAG_CODE          = 2;
    const BY_HASHTAG_LABEL         = 'By Hashtag';

    const BY_PRODUCT_HASHTAG_CODE  = 3;
    const BY_PRODUCT_HASHTAG_LABEL = 'By Product Hashtag';

    const BY_USER_NAME_CODE        = 4;
    const BY_USER_NAME_LABEL       = 'By User Name';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('neklo_instagram');
        return array(
            array(
                'value' => self::BY_USER_NAME_CODE,
                'label' => $helper->__(self::BY_USER_NAME_LABEL)
            ),
            array(
                'value' => self::BY_USER_ID_CODE,
                'label' => $helper->__(self::BY_USER_ID_LABEL)
            ),
            array(
                'value' => self::BY_PRODUCT_HASHTAG_CODE,
                'label' => $helper->__(self::BY_PRODUCT_HASHTAG_LABEL)
            ),
            array(
                'value' => self::BY_HASHTAG_CODE,
                'label' => $helper->__(self::BY_HASHTAG_LABEL)
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('neklo_instagram');
        return array(
            self::BY_USER_NAME_CODE       => $helper->__(self::BY_USER_NAME_LABEL),
            self::BY_USER_ID_CODE         => $helper->__(self::BY_USER_ID_LABEL),
            self::BY_PRODUCT_HASHTAG_CODE => $helper->__(self::BY_PRODUCT_HASHTAG_LABEL),
            self::BY_HASHTAG_CODE         => $helper->__(self::BY_HASHTAG_LABEL),
        );
    }
}
