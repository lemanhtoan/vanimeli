<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_Model_Config extends Mage_Core_Model_Abstract
{
    const CACHE_ID = 'comingsoon_config_storage';

    protected $_scope = 'default';
    protected $_scopeId = 0;
    protected $_inherit = false;

    protected $_useCache = true;
    protected $_cache = array();

    protected $_dateTimeFields = array(
        'comingsoon_launch_time',
        'maintenance_launch_time',
        'date_from',
        'date_to',
    );

    public function _construct()
    {
        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            $this->_init('comingsoon/config');
        }
    }

    public function saveParams($params)
    {
        $data = array();
        $this->prepareTime($params, 'save');

        foreach($params as $key => $value) {

            if(is_array($value)) {
                if(!empty($value['inherit']) || (!empty($value[0]['inherit']) && is_array($value[0])) ) {
                    continue;
                }
            }

            if(is_array($value) && (empty($value) || (isset($value[0]) && !is_array($value[0])) ) ) {
                $value = implode(',', $value);
            }elseif(!is_scalar($value)) {
                $value = serialize($value);
            }

            $data[] = array(
                'path'      => $key,
                'value'     => $value,
                'scope'     => $this->getScope(),
                'scope_id'  => $this->getScopeId(),
            );
        }

        if($this->getScope() != 'default') {
            $collection = $this->getCollection()
                ->addFieldToFilter('scope', $this->getScope())
                ->addFieldToFilter('scope_id', $this->getScopeId());

            foreach ($collection as $item) {
                if(!isset($params[$item->path]) || (is_array($params[$item->path]) && (!empty($params[$item->path]['inherit']) || !empty($params[$item->path][0]['inherit'])) ) ) {
                    $item->delete();
                }
            }
        }

        if($data) {
            $this->_getResource()->insertOnDuplicate($data, array('value'));
        }

        Mage::app()->cleanCache(self::CACHE_ID);

        return $this;
    }

    public function loadParams($key = null)
    {
        $scope = $this->getScope();
        $scopeId = $this->getScopeId();

        if(empty($this->_cache[$scope][$scopeId])) {

            $cache = Mage::app()->loadCache(self::CACHE_ID ."_{$scope}_{$scopeId}");
            if($this->_useCache && $data = @unserialize($cache)) {
                $this->_cache[$scope][$scopeId] = $data;
            }else{
                $collection = $this->getCollection();
                $where = array();
                foreach (array('default', 'websites', 'stores') as $_scope) {
                    switch ($_scope) {
                        case 'stores':
                            $_scopeId = $scopeId;
                            break;

                        case 'websites':
                            if($scope == 'stores') {
                                $_scopeId = Mage::app()->getStore($scopeId)->getWebsiteId();
                            }else{
                                $_scopeId = $scopeId;
                            }
                            break;

                        case 'default':
                        default:
                            $_scopeId = 0;
                            break;
                    }
                    $where[] = '`scope` = '. Mage::getSingleton('core/resource')->getConnection('default_write')->quote($_scope) .' AND `scope_id` = '. (int)$_scopeId;
                    if($_scope == $scope) {
                        break;
                    }
                }

                $select = $collection->getSelect();
                $select->where('('. implode(') OR (', $where) .')');
                $select->order(new Zend_Db_Expr('CASE WHEN scope = "stores" THEN 1 WHEN scope = "websites" THEN 2 WHEN scope = "default" THEN 3 END DESC'));

                $this->_cache[$scope][$scopeId] = Mage::helper('comingsoon')->getFormElementsValues();
                foreach ($collection as $item) {
                    if(is_string($item->value) && 0 === strpos($item->value, 'a:')) {
                        $item->value = @unserialize($item->value);
                    }
                    if($this->_inherit) {
                        $this->_cache[$scope][$scopeId][$item->path] = array(
                            'inherit'   => ($item->scope != $scope),
                            'value'     => $item->value
                        );
                    }else{
                        $this->_cache[$scope][$scopeId][$item->path] = $item->value;
                    }
                }

                if ($this->_useCache) {
                    Mage::app()->saveCache(serialize($this->_cache[$scope][$scopeId]), self::CACHE_ID ."_{$scope}_{$scopeId}", array(self::CACHE_ID), 60 * 60);
                }
            }
        }

        if($key) {
            return isset($this->_cache[$scope][$scopeId][$key])? $this->_cache[$scope][$scopeId][$key] : null;
        }

        return $this->_cache[$scope][$scopeId];
    }

    public function setScope($scopeId, $scope = 'stores', $inherit = false)
    {
        $this->_scopeId = 0;
        if($scope == 'stores') {
            $this->_scope = 'stores';
            if($store = Mage::app()->getStore($scopeId)) {
                $this->_scopeId = $store->getId();
            }
        }elseif($scope == 'websites') {
            $this->_scope = 'websites';
            if($website = Mage::app()->getWebsite($scopeId)) {
                $this->_scopeId = $website->getId();
            }
        }else{
            $this->_scope = 'default';
        }

        $this->_inherit = (bool)$inherit;
        $this->_useCache = !$this->_inherit;

        return $this;
    }

    public function getScope()
    {
        return $this->_scope;
    }

    public function getScopeId()
    {
        return $this->_scopeId;
    }

    public function prepareTime(&$data, $mode = null)
    {
        $storeId = null;
        if($this->getScope() == 'stores') {
            $storeId = $this->getScopeId();
        }

        foreach ($data as $key => $value) {

            $isTimeField = $key && in_array($key, $this->_dateTimeFields);

            if(is_array($value)) {
                if ($isTimeField) {
                    $data[$key]['value'] = $this->_getTimeValue($key, $value['value'], $storeId, $mode);
                } else {
                    $this->prepareTime($data[$key], $mode);
                }
            } else {
                if($isTimeField) {
                    $data[$key] = $this->_getTimeValue($key, $value, $storeId, $mode);
                }
            }
        }
    }

    protected function _getTimeValue($key, $value, $storeId, $mode)
    {
        if (!$value) {
            return $value;
        }

        $helper = Mage::helper('comingsoon');
        switch ($mode) {
            case 'save' :
                $value = $helper->getDateTimeInternal($value);
                $value = Mage::app()->getLocale()->utcDate($storeId, $value, true)->getTimestamp();
            break;

            case 'admin_form' :
                $storeDate = Mage::app()->getLocale()->storeDate($storeId, $value, true);
                $value = strtotime($storeDate->toString($helper->getDateTimeFormat()));
            break;
        }

        return $value;
    }

}