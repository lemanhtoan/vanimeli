<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Landingpage_Model_Mysql4_Slider_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ves_landingpage/slider');
    }

     /**
     * After load processing - adds store information to the datasets
     *
     */
    protected function _afterLoad()
    {  
        parent::_afterLoad();
    }
    
    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store Store to be filtered
     * @return ves_landingpage_Model_Mysql4_Megamenu_Collection Self
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array (
                 $store->getId()
            );
        }
        $this->getSelect()->join(
            array('store_table' => $this->getTable('ves_landingpage/slider_store')),
            'main_table.slider_id = store_table.slider_id', array ()
        )->where('store_table.store_id in (?)', array (
            0, 
            $store
        ))->group('main_table.slider_id');
        
        return $this;
    }

    public function addIdFilter($megamenuIds) {
    	if (is_array($megamenuIds)) {
            if (empty($megamenuIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $megamenuIds);
            }
        } elseif (is_numeric($megamenuIds)) {
            $condition = $megamenuIds;
        } elseif (is_string($megamenuIds)) {
            $ids = explode(',', $megamenuIds);
            if (empty($ids)) {
                $condition = $megamenuIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('parent_id', $condition);
        return $this;
    }
}