<?php
class ves_gallery_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * Initialize resource model
     */
    protected function _construct() {
        $this->_init('ves_gallery/banner', 'banner_id');
    }

    /**
     * Load images
     */
   // public function loadImage(Mage_Core_Model_Abstract $object) {
   //     return $this->__loadImage($object);
   // }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        //if (!$object->getIsMassDelete()) {
       //     $object = $this->__loadImage($object);
       // }
        if($extra_data = $object->getData("extra")) {
          $extra_data = unserialize($extra_data);
          if($extra_data) {
            foreach($extra_data as $key => $val) {
              $object->setData("extra__".$key, $val);
            }
          }
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {
        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }

    /**
     * Call-back function
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        return parent::_afterSave($object);
    }

    /**
     * Call-back function
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        // Cleanup stats on blog delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete blog/store
        //$adapter->delete($this->getTable('ves_gallery/banner_store'), 'banner_id='.$object->getId());
        // 2. Delete blog/post_cat

        return parent::_beforeDelete($object);
    }


}