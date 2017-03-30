<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Block_System_Extension_List extends Mage_Adminhtml_Block_Template
{
    const DOMAIN = 'http://store.neklo.com/';
    const IMAGE_EXTENSION = '.jpg';

    protected $_feedData = null;

    /**
     * @param string $code
     *
     * @return bool
     */
    public function canShowExtension($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        return !!count($feedData);
    }

    /**
     * @return array
     */
    public function getExtensionList()
    {
        return Mage::helper('neklo_core/extension')->getModuleConfigList();
    }

    /**
     * @param string $code
     *
     * @return mixed
     */
    public function getExtensionName($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        if (!array_key_exists('name', $feedData)) {
            return $code;
        }
        return $feedData['name'];
    }

    /**
     * @param string $code
     * @param $config
     *
     * @return bool
     */
    public function isExtensionVersionOutdated($code, $config)
    {
        $currentVersion = $this->getExtensionVersion($config);
        $lastVersion = $this->getLastExtensionVersion($code);
        return version_compare($currentVersion, $lastVersion) === -1;
    }

    public function getExtensionVersion($config)
    {
        $version = (string)$config->version;
        if (!$version) {
            return '';
        }
        return $version;
    }

    public function getLastExtensionVersion($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        if (!array_key_exists('version', $feedData)) {
            return '0';
        }
        return $feedData['version'];
    }

    public function getExtensionUrl($code)
    {
        $feedData = $this->_getExtensionInfo(strtolower($code));
        if (!array_key_exists('url', $feedData)) {
            return null;
        }
        return $feedData['url'];
    }

    public function getImageUrl($code)
    {
        $imgUrl = self::DOMAIN . 'cache/' . ($this->_getCacheKey() ? $this->_getCacheKey() . '/' : '') . strtolower($code) . self::IMAGE_EXTENSION;
        return $imgUrl;
    }

    protected function _getCacheKey()
    {
        return Mage::helper('neklo_core/extension')->getCacheKey();
    }

    protected function _getExtensionInfo($code)
    {
        if (is_null($this->_feedData)) {
            $this->_feedData = Mage::getModel('neklo_core/feed_extension')->getFeed();
        }
        if (!array_key_exists($code, $this->_feedData)) {
            return array();
        }
        return $this->_feedData[$code];
    }
}