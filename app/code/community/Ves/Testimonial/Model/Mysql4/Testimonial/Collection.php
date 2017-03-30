<?php
 /*------------------------------------------------------------------------
  # VenusTheme testimonial Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Model_Mysql4_Testimonial_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected $_previewFlag = null;
    /**
     * Constructor method
     */
    protected function _construct() {
		parent::_construct();
		$this->_init('ves_testimonial/testimonial');
        $this->_previewFlag = false;
    }

    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Ves_Slider_Model_Mysql4_testimonial_Collection
     */
    public function addStoreFilter($store) {
        if (!Mage::app()->isSingleStoreMode()) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            $this->getSelect()->join(
                    array('store_table' => $this->getTable('ves_testimonial/testimonial_store')),
                    'main_table.testimonial_id = store_table.testimonial_id',
                    array()
                    )
                    ->where('store_table.store_id in (?)', array(0, $store))
                    ->group('main_table.testimonial_id');
            return $this;
        }
        return $this;
    }

    /**
     * Add Filter by status
     *
     * @param int $status
     * @return Ves_Slider_Model_Mysql4_testimonial_Collection
     */
    public function addEnableFilter($status = 1) {
        $this->getSelect()->where('main_table.is_active = ?', $status);
        return $this;
    }
	
	 public function addChildrentFilter( $parentId = 1) {
        $this->getSelect()->where( 'main_table.parent_id = ?', $parentId );
        return $this;
    }
    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _beforeLoad()
    {
       $store_id = Mage::app()->getStore()->getId();
       if($store_id){
         $this->addStoreFilter($store_id);
       }
       
       parent::_beforeLoad();
    }
    /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _afterLoad()
    {

        if ($this->_previewFlag) {

            $items = $this->getColumnValues('testimonial_id');
            if (count($items)) {
                $select = $this->getConnection()->select()->from(
                    $this->getTable('ves_testimonial/testimonial_store')
                )->where(
                    $this->getTable('ves_testimonial/testimonial_store') . '.testimonial_id IN (?)',
                    $items
                );
                if ($result = $this->getConnection()->fetchPairs($select)) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('testimonial_id')])) {
                            continue;
                        }
                        if ($result[$item->getData('testimonial_id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        }
                        else {
                            $storeId = $result[$item->getData('testimonial_id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                    }
                }
            }
        }
        
        parent::_afterLoad();
    }
}