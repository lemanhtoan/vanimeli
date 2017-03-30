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
class Ves_BlockBuilder_Model_Mysql4_Block_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    /**
     * Constructor method
     */
    protected function _construct() {
		  parent::_construct();
		  $this->_init('ves_blockbuilder/block');
    }

    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _beforeLoad()
    {
       parent::_beforeLoad();
    }
    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _afterLoad()
    {
      $connection = $this->getConnection();
      foreach ($this as $item) {
        $page_stores = array();

        if($item->getData('block_id')) {

          $page_stores = Mage::getResourceModel("ves_blockbuilder/block")->lookupStoreIds($item->getData('block_id'));

        }

        if($alias = $item->getData("alias")) {
          $cms_page = Mage::getModel("ves_blockbuilder/block")->loadCMSPage($alias, "identifier", $page_stores);
          if($cms_page->getPageId()) {
            if(!$page_stores) {
               $page_stores = $cms_page->getStoreId();
            }
            
            $select = $connection->select()
                        ->from(array('cps'=>$this->getTable('cms/page_store')))
                        ->where('cps.page_id = (?)', $cms_page->getPageId());

            if ($result = $connection->fetchPairs($select)) {
                if ($result[$cms_page->getPageId()] == 0) {
                    $stores = Mage::app()->getStores(false, true);
                    $storeId = current($stores)->getId();
                    $storeCode = key($stores);
                } else {
                    $storeId = $result[$cms_page->getPageId()];
                    $storeCode = Mage::app()->getStore($storeId)->getCode();
                }

                $item->setData('_first_store_id', $storeId);
                $item->setData('store_code', $storeCode);
            }
            
            //$stores = (isset($stores) && $stores)?$stores:array(0);
            $item->setData("store_id", $page_stores);
                        
          }
        }
      }

      parent::_afterLoad();
    }

    public function addProductIdFilter($product_id = 0, $store_id = 0, $frontend = false) {
      if($product_id) {
        $this->getSelect()->join(
                              array('block_product_table' => $this->getTable('ves_blockbuilder/block_product')),
                              'main_table.block_id = block_product_table.block_id',
                              array()
                          )
                      ->where('block_product_table.product_id = (?)', $product_id)
                      ->group('main_table.block_id');
        if(!$store_id) {
           $store_id = 0;
        }
        if($frontend) {
          $this->getSelect()->where('block_product_table.store_id IN (?)', array(0, $store_id));
        } else {
          $this->getSelect()->where('block_product_table.store_id = (?)', $store_id);
        }
        
        
      }
      return $this;
    }

    public function addStoreFilter($store = "") {
      return $this;
    }

    public function addFooterFilter() {
      $this->getSelect()
                    ->where('(main_table.block_type IS NULL) OR (main_table.block_type != "page")')
                    ->where("main_table.alias like 'footer%'");

      return $this;
    }
    public function addHeaderFilter() {

      $this->getSelect()
                    ->where('(main_table.block_type IS NULL) OR (main_table.block_type != "page")')
                    ->where("main_table.alias like 'header%'");

      return $this;
    }


    
}