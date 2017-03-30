<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_USE_HTTPS_PATH    = 'neklo_core/notification/use_https';
    const XML_FEED_URL_PATH     = 'neklo_core/notification/feed_url';
    const XML_FREQUENCY_PATH    = 'neklo_core/notification/frequency';

    const LAST_CHECK_CACHE_KEY  = 'neklo_core_admin_notifications_last_check';

    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache(self::LAST_CHECK_CACHE_KEY);
    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), self::LAST_CHECK_CACHE_KEY);
        return $this;
    }

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://') . Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();
        $feedXml = $this->getFeedData();
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                if (!$this->_isAllowedItem($item)) {
                    continue;
                }
                $feedData[] = array(
                    'severity'      => (int)$item->severity,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }
            if ($feedData) {
                $inboxParser = Mage::getModel('adminnotification/inbox');
                if ($inboxParser) {
                    $inboxParser->parse(array_reverse($feedData));
                }
            }
        }
        $this->setLastUpdate();
        return $this;
    }

    protected function _isAllowedItem($item)
    {
        $itemType = $item->type ? $item->type : Neklo_Core_Model_Source_Subscription_Type::INFO_CODE;
        $allowedTypeList = Mage::helper('neklo_core/config')->getNotificationTypeList();
        if ($itemType == Neklo_Core_Model_Source_Subscription_Type::UPDATE_CODE) {
            if (in_array(Neklo_Core_Model_Source_Subscription_Type::UPDATE_ALL_CODE, $allowedTypeList)) {
                return true;
            }
            if (in_array(Neklo_Core_Model_Source_Subscription_Type::UPDATE_CODE, $allowedTypeList)) {
                $installedExtensionList = array_keys(Mage::helper('neklo_core/extension')->getModuleList());
                $isPresent = false;
                foreach ($item->extension->children() as $extensionCode) {
                    if (in_array((string)$extensionCode, $installedExtensionList)) {
                        $isPresent = true;
                    }
                }
                return $isPresent;
            }
        }
        if (!in_array($itemType, $allowedTypeList)) {

            return false;
        }
        return true;
    }
}