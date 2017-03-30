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


class Plumrocket_ComingSoon_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('comingsoon/config', 'config_id');
    }

    public function insertOnDuplicate(array $data, array $fields = array()) {
    	$table = $this->getTableName('comingsoon/config');
        return $this->_getWriteAdapter()->insertOnDuplicate($table, $data, $fields);
    }

    public function getTableName($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

}