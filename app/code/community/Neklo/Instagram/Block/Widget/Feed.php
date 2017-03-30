<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/

/**
 * @method string getIsEnabled()
 * @method string getMode()
 * @method string getUserId()
 * @method string getUserName()
 * @method string getHashtag()
 * @method string getLimitItems()
 * @method string getImageWidth()
 * @method string getImageHeight()
 */
class Neklo_Instagram_Block_Widget_Feed extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    const CACHE_KEY = 'NEKLO_WIDGET_INSTAGRAM_CACHE_KEY';

    protected $_api = null;

    public function setNameInLayout($name)
    {
        $this->addData(
            array(
                'cache_lifetime' => $this->_getConfig()->getCacheLifetime(),
                'cache_key'      => self::CACHE_KEY . '-' . $name,
            )
        );
        return parent::setNameInLayout($name);
    }

    public function canShow()
    {
        return $this->_getConfig()->isConnected() && ($this->getIsEnabled() || $this->getIsEnabled() === null);
    }

    public function getTitle()
    {
        $title = $this->getData('title');
        $hashtag = $this->getHashtag();
        if ($hashtag) {
            $title = str_replace('%s', $hashtag, $title);
        }
        return $title;
    }

    public function getDescription()
    {
        $description = $this->getData('description');
        $hashtag = $this->getHashtag();
        if ($hashtag) {
            $description = str_replace('%s', $hashtag, $description);
        }
        return $description;
    }

    public function getImageList()
    {
        switch ($this->getMode()) {
            case Neklo_Instagram_Model_Source_Mode::BY_USER_ID_CODE:
                return $this->_getUserMediaById();
            case Neklo_Instagram_Model_Source_Mode::BY_HASHTAG_CODE:
                return $this->_getTagMedia();
            case Neklo_Instagram_Model_Source_Mode::BY_PRODUCT_HASHTAG_CODE:
                return $this->_getProductTagMedia();
            case Neklo_Instagram_Model_Source_Mode::BY_USER_NAME_CODE:
                return $this->_getUserMediaByName();
            default:
                $imageList = array();
                break;
        }
        return $imageList;
    }

    protected function _getUserMediaById()
    {
        return $this->_getApi()->getUserMediaById(
            $this->getUserId(), $this->getLimitItems()
        );
    }

    protected function _getTagMedia()
    {
        if (!$this->getHashtag()) {
            return array();
        }
        return $this->_getApi()->getTagMedia(
            $this->getHashtag(), $this->getLimitItems()
        );
    }

    protected function _getProductTagMedia()
    {
        $product = Mage::registry('current_product');
        if (!$product || !$product->getId() || !$product->getInstagramHashtag()) {
            return array();
        }
        $this->setHashtag($product->getInstagramHashtag());
        return $this->_getApi()->getTagMedia(
            $this->getHashtag(), $this->getLimitItems()
        );
    }

    protected function _getUserMediaByName()
    {
        return $this->_getApi()->getUserMediaByName(
            $this->getUserName(), $this->getLimitItems()
        );
    }

    /**
     * @return Neklo_Instagram_Model_Instagram
     */
    protected function _getApi()
    {
        if ($this->_api === null) {
            $this->_api = Mage::getModel('neklo_instagram/instagram');
        }
        return $this->_api;
    }

    /**
     * @return Neklo_Instagram_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('neklo_instagram/config');
    }
}