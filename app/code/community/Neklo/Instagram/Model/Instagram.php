<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Instagram_Model_Instagram
{
    protected $_api = null;

    /**
     * Returns Instagram API model.
     *
     * @return Neklo_Instagram_Model_Instagram_Api
     */
    public function getApi()
    {
        if ($this->_api === null) {
            $this->_api = Mage::getModel(
                'neklo_instagram/instagram_api',
                $this->_getConfig()->getAccessToken()
            );
        }
        return $this->_api;
    }

    /**
     * @param string $name
     * @param int $limit
     *
     * @return Varien_Data_Collection
     */
    public function getTagMedia($name, $limit = 0)
    {
        $collection = new Varien_Data_Collection();
        try {
            $response = $this->getApi()->getTagMedia($name, $limit);
        } catch (Exception $e) {
            Mage::logException($e);
            return $collection;
        }
        if (!isset($response->data) || !is_array($response->data)) {
            return $collection;
        }
        foreach ($response->data as $item) {
            if (!isset($item->images->low_resolution->url)) {
                continue;
            }
            $image = new Varien_Object();
            $image->setUrl($item->images->low_resolution->url);
            if (isset($item->caption->text)) {
                $image->setName($item->caption->text);
            }
            if (isset($item->link)) {
                $image->setLink($item->link);
            }
            $collection->addItem($image);
        }
        return $collection;
    }

    /**
     * @param string $userId
     * @param int    $limit
     *
     * @return Varien_Data_Collection
     */
    public function getUserMediaById($userId, $limit = 0)
    {
        $collection = new Varien_Data_Collection();
        try {
            $response = $this->getApi()->getUserMedia($userId, $limit);
        } catch (Exception $e) {
            Mage::logException($e);
            return $collection;
        }
        if (!isset($response->data) || !is_array($response->data)) {
            return $collection;
        }
        foreach ($response->data as $item) {
            if (!isset($item->images->low_resolution->url)) {
                continue;
            }
            $image = new Varien_Object();
            $image->setUrl($item->images->low_resolution->url);
            if (isset($item->caption->text)) {
                $image->setName($item->caption->text);
            }
            if (isset($item->link)) {
                $image->setLink($item->link);
            }
            $collection->addItem($image);
        }
        return $collection;
    }

    /**
     * @param string $userName
     * @param int $limit
     *
     * @return Varien_Data_Collection
     */
    public function getUserMediaByName($userName, $limit = 0)
    {
        $collection = new Varien_Data_Collection();
        try {
            $response = $this->getApi()->searchUser($userName);
        } catch (Exception $e) {
            Mage::logException($e);
            return $collection;
        }
        if (!isset($response->data) || !is_array($response->data) || !count($response->data)) {
            return $collection;
        }
        $user = current($response->data);
        return $this->getUserMediaById($user->id, $limit);
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }
}
