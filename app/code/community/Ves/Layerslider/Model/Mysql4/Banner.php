<?php
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Model_Mysql4_Banner extends Mage_Core_Model_Mysql4_Abstract {

    /**
     * Initialize resource model
     */
    protected function _construct() {
	
        $this->_init('ves_layerslider/banner', 'banner_id');
    }

    /**
     * Load images
     */
   // public function loadImage(Mage_Core_Model_Abstract $object) {
   //     return $this->__loadImage($object);
   // }

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
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {
        // Cleanup stats on brand delete
        $adapter = $this->_getReadAdapter();
        // 1. Delete brand/store
        //$adapter->delete($this->getTable('venustheme_brand/brand_store'), 'brand_id='.$object->getId());
        // 2. Delete brand/post_cat

        return parent::_beforeDelete($object);
    }
    /**
   * Assign page to store views
   *
   * @param Mage_Core_Model_Abstract $object
   */
  protected function _afterSave(Mage_Core_Model_Abstract $object)
  {
    $condition = $this->_getWriteAdapter()->quoteInto('banner_id = ?', $object->getId());
    // process faq item to store relation
    $this->_getWriteAdapter()->delete($this->getTable('ves_layerslider/banner_store'), $condition);
    $stores = (array) $object->getData('stores');

    if($stores){
      foreach ((array) $object->getData('stores') as $store) {
        $storeArray = array ();
        $storeArray['banner_id'] = $object->getId();
        $storeArray['store_id'] = $store;
        $this->_getWriteAdapter()->insert(
          $this->getTable('ves_layerslider/banner_store'), $storeArray
        );
      } 
    }else{
      $storeArray = array ();
      $storeArray['banner_id'] = $object->getId();
      $storeArray['store_id'] = $object->getStoreId();
      $this->_getWriteAdapter()->insert(
        $this->getTable('ves_layerslider/banner_store'), $storeArray
      );
    }
    
    
    return parent::_afterSave($object);
  }

  /**
   * Do store and category processing after loading
   * 
   * @param Mage_Core_Model_Abstract $object Current faq item
   */
  protected function _afterLoad(Mage_Core_Model_Abstract $object)
  {
      // process faq item to store relation
    $select = $this->_getReadAdapter()->select()->from(
      $this->getTable('ves_layerslider/banner_store')
    )->where('banner_id = ?', $object->getId());
    
    if ($data = $this->_getReadAdapter()->fetchAll($select)) {
      $storesArray = array ();
      foreach ($data as $row) {
        $storesArray[] = $row['store_id'];
      }
      $object->setData('store_id', $storesArray);
    }
        
    return parent::_afterLoad($object);
  }

  public function lookupStoreIds($theme_id = 0){
    $select = $this->_getReadAdapter()->select()->from(
      $this->getTable('ves_layerslider/banner_store')
    )->where('banner_id = ?', (int)$theme_id);

    $storesArray = array ();

    if ($data = $this->_getReadAdapter()->fetchAll($select)) {
      
      foreach ($data as $row) {
        $storesArray[] = $row['store_id'];
      }
    }
    return $storesArray;
    }

}
