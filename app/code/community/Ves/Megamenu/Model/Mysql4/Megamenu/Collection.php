<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.9.x.x and Magento 1.9.x.x
 * @version 1.0.0.1
 * @author http://venustheme.com
 * @copyright	Copyright (C) December 2010 Venustheme.com <@emai:venustheme@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Megamenu_Model_Mysql4_Megamenu_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_previewFlag = null;
    public function _construct()
    {
        parent::_construct();
        $this->_init('ves_megamenu/megamenu');
        $this->_previewFlag = false;
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
     * @return Ves_Megamenu_Model_Mysql4_Megamenu_Collection Self
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array (
                 $store->getId()
            );
        }
        $store = is_array($store)?$store:array($store);

        //do stuff
        $this->getSelect()->join(
            array('store_table' => $this->getTable('ves_megamenu/megamenu_store')),
            'main_table.megamenu_id = store_table.megamenu_id', array ()
        )->where('store_table.store_id in (?)', $store)->group('main_table.megamenu_id');

        //echo $this->getSelect();die();
        return $this;
    }



    /**
     * Add Filter by store
     *
     * @param int|Mage_Core_Model_Store $store Store to be filtered
     * @return Ves_Megamenu_Model_Mysql4_Megamenu_Collection Self
     */
    public function addRootFilter($root_parent = 1)
    {
        //do stuff
        $this->getSelect()->where('main_table.parent_id = (?)', (int)$root_parent);

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