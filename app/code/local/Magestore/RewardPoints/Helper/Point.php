<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * RewardPoints Name and Image Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Helper_Point extends Mage_Core_Helper_Abstract
{
    const XML_PATH_POINT_NAME           = 'rewardpoints/general/point_name';
    const XML_PATH_POINT_NAME_PLURAL    = 'rewardpoints/general/point_names';
    const XML_PATH_POINT_IMAGE          = 'rewardpoints/general/point_image';
    
    const XML_PATH_DISPLAY_PRODUCT      = 'rewardpoints/display/product';
    const XML_PATH_DISPLAY_MINICART     = 'rewardpoints/display/minicart';
    
    /**
     * get Label for Point, default is "Point"
     * 
     * @param mixed $store
     * @return string
     */
    public function getName($store = null)
    {
        if ($pointName = trim(Mage::getStoreConfig(self::XML_PATH_POINT_NAME, $store))) {
            return $pointName;
        }
        return $this->__('Point');
    }
    
    /**
     * get reward Label for Points (plural), default is "Points"
     * 
     * @param mixed $store
     * @return string
     */
    public function getPluralName($store = null)
    {
        if ($pluralName = trim(Mage::getStoreConfig(self::XML_PATH_POINT_NAME_PLURAL, $store))) {
            return $pluralName;
        }
        return $this->__('Points');
    }
    
    /**
     * get point image on store, default is template image url
     * 
     * @param mixed $store
     * @return string image url
     */
    public function getImage($store = null)
    {
        if ($imgPath = Mage::getStoreConfig(self::XML_PATH_POINT_IMAGE, $store)) {
            return Mage::getBaseUrl('media') . 'rewardpoints/' . $imgPath;
        }
        return Mage::getDesign()->getSkinUrl('images/rewardpoints/point.png');
    }
    
    /**
     * get Image (by HTML code)
     * 
     * @param boolean $hasAnchor
     * @return string
     */
    public function getImageHtml($hasAnchor = false)
    {
        return Mage::getBlockSingleton('rewardpoints/image')
            ->setIsAnchorMode($hasAnchor)
            ->toHtml();
    }
    
    /**
     * format point with unit (name). Ex: 1 Point, 2 Points
     * 
     * @param int $points
     * @param mixed $store
     * @return string
     */
    public function format($points, $store = null)
    {
        $points = intval($points);
        if (abs($points) <= 1) {
            return $points . ' ' . $this->getName($store);
        }
        return $points . ' ' . $this->getPluralName($store);
    }
    
    /**
     * check show earning reward points on top link
     * 
     * @param type $store
     * @return string
     */
    public function showOnProduct($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DISPLAY_PRODUCT, $store);
    }
    
    /**
     * check show earning reward points on mini cart
     * 
     * @param type $store
     * @return string
     */
    public function showOnMiniCart($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DISPLAY_MINICART, $store);
    }
}
