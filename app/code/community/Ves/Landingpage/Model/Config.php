<?php

class Ves_Landingpage_Model_Config extends Mage_Catalog_Model_Product_Media_Config {

    const CACHE_BLOCK_TAG = 'ves_landingpage_block';
    const CACHE_WIDGET_TAG = 'ves_landingpage_widget';

    public function getBaseMediaPath() {
        return Mage::getBaseDir('media') . DS . 'tabshome';
    }

    public function getBaseMediaUrl() {
        return Mage::getBaseUrl('media') . 'tabshome';
    }

    public function getBaseTmpMediaPath() {
        return Mage::getBaseDir('media') . DS . 'tmp' . DS . 'tabshome';
    }

    public function getBaseTmpMediaUrl() {
        return Mage::getBaseUrl('media') . 'tmp/tabshome';
    }

}