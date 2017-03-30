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
class Ves_ProductList_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('productlist/rule');
    }

    /**
     * Add Filter by store
     *
     * @param int $storeId
     * @return Ves_ProductList_Model_Mysql4_ProductList_Collection
     */
    public function addStoreFilter($store = null) {
        $store = Mage::app()->getStore($store);
        $this->getSelect()->join(
            array('store_table' => $this->getTable('productlist/rule_store')),
            'main_table.rule_id = store_table.rule_id',
            array()
            )
        ->where('store_table.store_id in (?)', array(0, $store->getId()))
        ->group('main_table.rule_id');
        return $this;
    }

    public function addCustomerGroupFilter(){
        $customer_group_id = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
        $this->getSelect()->join(
            array('table_customer' => Mage::getSingleton('core/resource')->getTableName('productlist/rule_customer')),
            'main_table.rule_id = table_customer.rule_id',
            array()
            )
        ->where('table_customer.customer_group_id in (?)', array($customer_group_id) );
        return $this;
    }

    public function addStatusFilter($enabled = true)
    {
        $this->getSelect()->where('status = ?', $enabled ? 1 : 2);
        return $this;
    }

    public function addDateFilter($date = null)
    {
        if ($date === null)
            $date = now(true);
        $this->getSelect()->where('(date_from IS NULL OR date_from <= ?) AND (date_to IS NULL OR date_to >= ?)', $date, $date);
        return $this;
    }

    public function addTypeFilter($type)
    {
        $this->getSelect()->where('type = ?', $type);
        return $this;
    }

    public function setPriorityOrder($dir = 'ASC')
    {
        $this->setOrder('main_table.priority', $dir);

        return $this;
    }

    protected function _afterLoad()
    {
        /*foreach ($this->getItems() as $item)
        {
            $item->callAfterLoad();
        }*/
        return parent::_afterLoad();
    }

}