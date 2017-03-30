<?php

/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_List extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection = null;

    protected $_config;

    public function __construct($attributes = array()) {
        $this->convertAttributesToConfig($attributes);
        parent::__construct();
    }

    public function convertAttributesToConfig($attributes = array()) {
        if ($attributes) {
            foreach ($attributes as $key => $val) {
                $this->setConfig($key, $val);
            }
        }
    }

    public function checkGroupCustomer($groupid) {
        $check = false;
        foreach ($groupid as $key => $value) {
            $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
            if ($groupId == $value){
                $check = true;
            }
        }
        return $check;
    }

    /**
     * Get value of the extension's configuration
     *
     * @return string
     */
    public function getConfig($key, $default = "", $panel = 'general_setting') {
        $return = "";
        $value = $this->getData($key);

        //Check if has widget config data
        if ($this->hasData($key) && $value !== null) {

            if ($value == "true") {
                return 1;
            }elseif ($value == "false") {
                return 0;
            }

            return $value;
        }else {

            if (isset($this->_config[$key])) {
                $return = $this->_config[$key];
            }else {
                $return = Mage::getStoreConfig("productlist/$panel/$key");
            }
            if ($return == "" && $default) {
                $return = $default;
            }
        }
        return $return;
    }

    /**
     * overrde the value of the extension's configuration
     *
     * @return string
     */
    function setConfig($key, $value) {

        if ($value == "true") {
            $value = 1;
        }elseif ($value == "false") {
            $value = 0;
        }

        if ($value != "") {
            $this->_config[$key] = $value;
        }

        return $this;
    }

    /**
     * Check product is new
     *
     * @param  Mage_Catalog_Model_Product $_product
     * @return bool
     */
    public function checkProductIsNew($_product = null) {
        $from_date = $_product->getNewsFromDate();
        $to_date = $_product->getNewsToDate();
        $is_new = false;
        $is_new = $this->isNewProduct($from_date, $to_date);
        $today = strtotime("now");

        if ($from_date && $to_date) {
            $from_date = strtotime($from_date);
            $to_date = strtotime($to_date);
            if ($from_date <= $today && $to_date >= $today) {
                $is_new = true;
            }
        }
        elseif ($from_date && !$to_date) {
            $from_date = strtotime($from_date);
            if ($from_date <= $today) {
                $is_new = true;
            }
        }elseif (!$from_date && $to_date) {
            $to_date = strtotime($to_date);
            if ($to_date >= $today) {
                $is_new = true;
            }
        }

        return $is_new;
    }

    public function isNewProduct( $created_date, $num_days_new = 3) {
        $check = false;

        $startTimeStamp = strtotime($created_date);
        $endTimeStamp = strtotime("now");

        $timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
            $check = true;
        }

        return $check;
    }

    public function subString($text, $length = 100, $replacer = '...', $is_striped = true) {
        $text = ($is_striped == true) ? strip_tags($text) : $text;
        if (strlen($text) <= $length) {
            return $text;
        }
        $text = substr($text, 0, $length);
        $pos_space = strrpos($text, ' ');
        return substr($text, 0, $pos_space) . $replacer;
    }

    public function getProductImage($product_id, $image_index = 0, $image_width = 200, $image_height = 200){
            $_product = Mage::getModel('catalog/product')->load($product_id);
            $collection = $_product->getMediaGalleryImages();
            if ( count($collection) > 0) {
                $image = null;
                $i = 0;
                foreach($collection as $_image){
                    if($i == $image_index){
                        $image = $_image;
                        break;
                    }
                    $i++;
                }
                if($image){

                    return (string)Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($image_width, $image_height);
                }

            }
            return false;
    }

    public function getBought($product_sku = "") {
        $sku = nl2br($product_sku);
        $product = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToFilter('sku', $sku)
            ->setOrder('ordered_qty', 'desc')
            ->getFirstItem();
        return (int)$product->getOrderedQty();
    }
}
