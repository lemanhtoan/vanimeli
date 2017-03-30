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
class Ves_ProductList_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_ruleProductTable;

	public function _construct()
	{
		$this->_init('productlist/rule', 'rule_id');
        $this->_ruleProductTable = $this->getTable('productlist/rule_product');
	}

	public function lookupStoreIds($category_id = 0){
		$select = $this->_getReadAdapter()->select()->from(
			$this->getTable('productlist/rule_store')
			)->where('id = ?', (int)$category_id);

		$storesArray = array ();

		if ($data = $this->_getReadAdapter()->fetchAll($select)) {
			foreach ($data as $row) {
				$storesArray[] = $row['store_id'];
			}
		}
		return $storesArray;
	}

    /**
     * Retrieve load select with filter by identifier, store and activity
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
    		array('cps' => $this->getTable('productlist/rule_store')),
    		'cp.rule_id = cps.rule_id',
    		array())
    	->where('cp.identifier = ?', $identifier)
    	->where('cps.store_id IN (?)', $store);

    	if (!is_null($isActive)) {
    		$select->where('cp.is_active = ?', $isActive);
    	}

    	return $select;
    }

    /**
     * Check for unique of identifier of page to selected store(s).
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniquePageToStores(Mage_Core_Model_Abstract $object)
    {
    	if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
    		$stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
    	} else {
    		$stores = (array)$object->getData('store');
    	}

    	$select = $this->_getLoadByIdentifierSelect($object->getData('identifier'), $stores);

    	if ($object->getId()) {
    		$select->where('cps.rule_id <> ?', $object->getId());
    	}

    	if ($this->_getWriteAdapter()->fetchRow($select)) {
    		return false;
    	}

    	return true;
    }

    /**
     *  Check whether page identifier is valid
     *
     *  @param    Mage_Core_Model_Abstract $object
     *  @return   bool
     */
    protected function isValidPageIdentifier(Mage_Core_Model_Abstract $object)
    {
    	return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     *  Check whether page identifier is numeric
     *
     * @date Wed Mar 26 18:12:28 EET 2008
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    protected function isNumericPageIdentifier(Mage_Core_Model_Abstract $object)
    {
    	return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Ves_ProductList_Model_Mysql4_Productlist
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
    	if (!$this->getIsUniquePageToStores($object)) {
    		Mage::throwException(Mage::helper('productlist')->__('A page rule URL key for specified store already exists.'));
    	}

    	if (!$this->isValidPageIdentifier($object)) {
    		Mage::throwException(Mage::helper('productlist')->__('The page URL key contains capital letters or disallowed symbols.'));
    	}

    	if ($this->isNumericPageIdentifier($object)) {
    		Mage::throwException(Mage::helper('productlist')->__('The page URL key cannot consist only of numbers.'));
    	}
    	return parent::_beforeSave($object);
    }

    /**
     * Assign page to store views
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
    // process rule item to store relation
    	$condition = $this->_getWriteAdapter()->quoteInto('rule_id = ?', $object->getId());

    // process rule item to store relation
        $stores = $object->getData('stores');
        if($stores && is_array($stores)){
            $this->_getWriteAdapter()->delete($this->getTable('productlist/rule_store'), $condition);
            $stores = $object->getData('stores');
            foreach ($stores as $_store) {
                $store = array ();
                $store['rule_id'] = $object->getId();
                $store['store_id'] = $_store;
                $this->_getWriteAdapter()->insert(
                    $this->getTable('productlist/rule_store'), $store
                    );
            }
        }

        // process rule item to customer group relation
        $customer_group = $object->getData('customer_group');
        if($customer_group && is_array($customer_group)){
            $this->_getWriteAdapter()->delete($this->getTable('productlist/rule_customer'), $condition);
            foreach ((array) $customer_group as $_customer) {
                $customer = array ();
                $customer['rule_id'] = $object->getId();
                $customer['customer_group_id'] = $_customer;
                $this->_getWriteAdapter()->insert($this->getTable('productlist/rule_customer'), $customer);
            }
        }

        if($ids = $object->getMatchingProductIds()){
            $this->_getWriteAdapter()->delete($this->getTable('productlist/rule_product'), $condition);
            foreach ($ids as $k => $_id ) {
                $rule_product = array();
                $rule_product['rule_id'] = $object->getId();
                $rule_product['rule_product_id'] = $_id;
                $rule_product['position'] = $k;
                $this->_getWriteAdapter()->insert($this->getTable('productlist/rule_product'), $rule_product);
            }
        }
        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('productlist/rule_store')
            )->where('rule_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $stores = array ();
            foreach ($data as $row) {
                $stores[] = $row['store_id'];
            }
            $object->setData('stores', $stores);
        }
        $select = $this->_getReadAdapter()->select()->from(
            $this->getTable('productlist/rule_customer')
            )->where('rule_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $customer_group = array ();
            foreach ($data as $row) {
                $customer_group[] = $row['customer_group_id'];
            }
            $object->setData('customer_group', $customer_group);
        }

        $product_order = $object->getProductOrder();
        if($product_order == "position") {
            $object->setData("product_order", "best");
        }
        return parent::_afterLoad($object);
    }

    /**
     * Get positions of associated to rule products
     *
     * @param Mage_Catalog_Model_Category $rule
     * @return array
     */
    public function getProductsPosition($rule)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->_ruleProductTable, array('rule_product_id', 'position'))
            ->where('rule_id = :rule_id');
        $bind = array('rule_id' => (int)$rule->getId());

        return $this->_getWriteAdapter()->fetchPairs($select, $bind);
    }
}