<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Model_Feed_Extension
{
    const FEED_URL = 'http://store.neklo.com/feed.json';
    const CACHE_ID = 'NEKLO_EXTENSION_FEED';
    const CACHE_LIFETIME = 172800;

    public function getFeed()
    {
        if (!$feed = Mage::app()->loadCache(self::CACHE_ID)) {
            $feed = $this->_getFeedFromResource();
            $this->_save($feed);
        }
        $feedArray = Mage::helper('core')->jsonDecode($feed);
        if (!is_array($feedArray)) {
            $feedArray = array();
        }
        return $feedArray;
    }

    protected function _getFeedFromResource()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::FEED_URL);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json'
            )
        );
        $result = curl_exec($ch);
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $result = '{}';
        }
        curl_close($ch);
        return $result;
    }

    protected function _save($feed)
    {
        Mage::app()->saveCache($feed, self::CACHE_ID, array(), self::CACHE_LIFETIME);
        return $this;
    }
}