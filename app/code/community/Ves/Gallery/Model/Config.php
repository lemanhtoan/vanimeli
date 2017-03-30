<?php
class Ves_Gallery_Model_Config extends Mage_Catalog_Model_Product_Media_Config {

    public function getBaseMediaPath() {
        return Mage::getBaseDir('media') .DS. 'gallery';
    }

    public function getBaseMediaUrl() {
        return Mage::getBaseUrl('media') . 'gallery';
    }

    public function getBaseTmpMediaPath() {
        return Mage::getBaseDir('media') .DS. 'tmp' .DS. 'gallery';
    }

    public function getBaseTmpMediaUrl() {
        return Mage::getBaseUrl('media') . 'tmp/gallery';
    }

}