<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_Model_System_Config_Source_Mailchimplist
{
    public function toOptionArray()
    {
        $values = $this->toOptionHash();
        $result = array();

        foreach ($values as $key => $value) {
            $result[] = array(
                'value' => $key,
                'label' => $value,
            );
        }
        return $result;
    }

    public function toOptionHash()
    {
        $model = Mage::helper('comingsoon')->getMcapi();
        $items = array();
        if ($model) {
            $result = $model->lists();
            $lists = $result['data'];

            if ($lists) {
                foreach ($lists as $list) {
                    $items[ $list['id'] ] = $list['name'];
                }
            }
        }
        return $items;
    }
}
