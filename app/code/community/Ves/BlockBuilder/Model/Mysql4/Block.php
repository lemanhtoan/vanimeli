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
class Ves_BlockBuilder_Model_Mysql4_Block extends Mage_Core_Model_Mysql4_Abstract {

    /**
     * Initialize resource model
     */
    protected function _construct() {
	
        $this->_init('ves_blockbuilder/block', 'block_id');
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
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Cms_Model_Resource_Page
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
        if($object->getData("block_type") == "page") {
          if (!$this->getIsUniquePageToStores($object)) {
              Mage::throwException(Mage::helper('cms')->__('A CMS page URL key for specified store already exists.'));
          }

          if (!$this->isValidPageIdentifier($object)) {
              Mage::throwException(Mage::helper('cms')->__('The page URL key contains capital letters or disallowed symbols.'));
          }

          if ($this->isNumericPageIdentifier($object)) {
              Mage::throwException(Mage::helper('cms')->__('The page URL key cannot consist only of numbers.'));
          }
        }
        return parent::_beforeSave($object);
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

        $condition = array(
            'block_id = ?'     => (int) $object->getId(),
        );

        $this->_getWriteAdapter()->delete($this->getTable('ves_blockbuilder/block_cms'), $condition);

        return parent::_beforeDelete($object);
    }
    /**
   * Assign page to store views
   *
   * @param Mage_Core_Model_Abstract $object
   */
  protected function _afterSave(Mage_Core_Model_Abstract $object)
  {
    $oldStores = $this->lookupStoreIds($object->getId());
    $newStores = (array)$object->getStores();
    if (empty($newStores)) {
        $newStores = (array)$object->getStoreId();
    }
    $table  = $this->getTable('ves_blockbuilder/block_cms');
    $insert = array_diff($newStores, $oldStores);
    $delete = array_diff($oldStores, $newStores);

    if ($delete) {
        $where = array(
            'block_id = ?'     => (int) $object->getId(),
            'store_id IN (?)' => $delete
        );

        $this->_getWriteAdapter()->delete($table, $where);
    }

    if ($insert) {
        $data = array();

        foreach ($insert as $storeId) {
            $data[] = array(
                'block_id'  => (int) $object->getId(),
                'store_id' => (int) $storeId
            );
        }

        $this->_getWriteAdapter()->insertMultiple($table, $data);
    }

    //Store widget short code into table ves_blockbuilder_widget
    if($widgets = $object->getWpowidget()){
        $data = [];
        $table  = $this->getTable('ves_blockbuilder/widget');
        foreach($widgets as $wkey=>$val){
            $widget_shortcode = isset($val['config'])?$val['config']:"";
            if($widget_shortcode) {
                if ($wkey) {
                    $where = [
                        'block_id = ?'     => (int) $object->getId()
                    ];

                    $this->_getReadAdapter()->delete($table, $where);

                    $data[] = [
                        'block_id'   => (int) $object->getId(),
                        'widget_key' => $wkey,
                        'widget_shortcode'  => $widget_shortcode,
                        'created'    => date( 'Y-m-d H:i:s' )
                    ];
                }
                
            }
        }
        if ($data) {
            $this->_getReadAdapter()->insertMultiple($table, $data);
        }
        
    }

    // Code that flushes cache goes here
    Mage::app()->cleanCache( array(
        Mage_Core_Model_Store::CACHE_TAG,
        Mage_Cms_Model_Block::CACHE_TAG,
        Ves_BlockBuilder_Model_Block::CACHE_BLOCK_TAG
    ) );
    Mage::app()->cleanCache( array(
        Mage_Core_Model_Store::CACHE_TAG,
        Mage_Cms_Model_Block::CACHE_TAG,
        Ves_BlockBuilder_Model_Block::CACHE_PAGE_TAG
    ) );
    Mage::app()->cleanCache( array(
        Mage_Core_Model_Store::CACHE_TAG,
        Mage_Cms_Model_Block::CACHE_TAG,
        Ves_BlockBuilder_Model_Block::CACHE_PRODUCT_TAG
    ) );
    return parent::_afterSave($object);
  }

  /**
   * Do store and category processing after loading
   * 
   * @param Mage_Core_Model_Abstract $object Current faq item
   */
  protected function _afterLoad(Mage_Core_Model_Abstract $object)
  {
    $stores = array();
    if ($object->getId()) {
        $stores = $this->lookupStoreIds($object->getId());
    }
    // get cms page data
    if(($alias = $object->getData("alias")) && $object->getData("block_type") == "page") {
      $cms_page = Mage::getModel("cms/page")->load($alias, "identifier");
      $cms_page = Mage::getModel("ves_blockbuilder/block")->loadCMSPage($alias, "identifier", $stores);

      if($cms_page->getPageId()) {
        if(!$stores) {
            $stores = $cms_page->getStoreId();
        }
        //$stores = $cms_page->getStoreId();

        $object->setData("cmspage_id", $cms_page->getPageId());
        $object->setData("root_template", $cms_page->getRootTemplate());
        $object->setData("layout_update_xml", $cms_page->getLayoutUpdateXml());
        $object->setData("custom_theme_from", $cms_page->getCustomThemeFrom());
        $object->setData("custom_theme_to", $cms_page->getCustomThemeTo());
        $object->setData("custom_theme", $cms_page->getCustomTheme());
        $object->setData("custom_root_template", $cms_page->getCustomRootTemplate());
        $object->setData("custom_layout_update_xml", $cms_page->getCustomLayoutUpdateXml());
        $object->setData("meta_keywords", $cms_page->getMetaKeywords());
        $object->setData("meta_description", $cms_page->getMetaDescription());
      }
    }
    if($settings = $object->getData("settings")) {
      $settings = unserialize($settings);
      if($settings) {
        foreach($settings as $key => $val) {
          $object->setData($key, $val);
        }
      }
    }
    $stores = $stores?$stores:array(0);
    $object->setData("store_id", $stores);

    //Load params and widgets
    if($params = $object->getParams()) {
        $widgets = $this->lookupWidgets($object->getId());
        $data_widgets = array();
        if($widgets) {
            foreach($widgets as $key => $widget){
              $data_widgets[$widget['widget_key']] = $widget['widget_shortcode'];
            }
        }
        $object->setData("widgets", $data_widgets);
    }
    
    return parent::_afterLoad($object);
  }

  public function lookupWidgets($pageId) {
      $adapter = $this->_getReadAdapter();

      $select  = $adapter->select()
                          ->from($this->getTable('ves_blockbuilder/widget'), '*')
                          ->where('block_id = ?',(int)$pageId);

      return $adapter->fetchAll($select);
  }

  /**
     * Retrieve load select with filter by alias, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('cp' => $this->getMainTable()))
            ->join(
                array('cps' => $this->getTable('ves_blockbuilder/block_cms')),
                'cp.block_id = cps.block_id',
                array())
            ->where('cp.alias = ?', $identifier)
            ->where('cp.block_type = ?', "page")
            ->where('cps.store_id IN (?)', $store);

        if (!is_null($isActive)) {
            $select->where('cp.status = ?', $isActive);
        }
        return $select;
    }

   /**
     * Check for unique of alias of page to selected store(s).
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniquePageToStores(Mage_Core_Model_Abstract $object)
    {
        if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getLoadByIdentifierSelect($object->getData('alias'), $stores);

        if ($object->getId()) {
            $select->where('cp.block_id <> ?', $object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     *  Check whether page alias is numeric
     *
     * @date Wed Mar 26 18:12:28 EET 2008
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    protected function isNumericPageIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('alias'));
    }

    /**
     *  Check whether page alias is valid
     *
     *  @param    Mage_Core_Model_Abstract $object
     *  @return   bool
     */
    protected function isValidPageIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('alias'));
    }


    public function saveProduct( $block_id = 0, $product_id = 0, $store_id = 0) {
      $store_id = !$store_id?0:(int)$store_id;
      // process rule item to data relation
      $condition = $this->_getWriteAdapter()->quoteInto('product_id = ?', $product_id);
      //If current store view id
      $condition .=  " AND ".$this->_getWriteAdapter()->quoteInto('store_id = ?', $store_id);


      if($product_id ){
          //Delete block product if exists
          $this->_getWriteAdapter()->delete($this->getTable('ves_blockbuilder/block_product'), $condition);

          if($block_id) { //Insert block product
            $data = array ();
            $data['block_id'] = $block_id;
            $data['product_id'] = $product_id;
            $data['store_id'] = $store_id;
            $this->_getWriteAdapter()->insert(
                $this->getTable('ves_blockbuilder/block_product'), $data
                );
          }
          
      }
      return true;
    }
     /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('ves_blockbuilder/block_cms'), 'store_id')
            ->where('block_id = ?',(int)$pageId);

        return $adapter->fetchCol($select);
    }

}
