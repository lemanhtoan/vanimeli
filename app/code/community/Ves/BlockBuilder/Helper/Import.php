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
 * @package    Ves_BlockBuilder
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves BlockBuilder Extension
 *
 * @category   Ves
 * @package    Ves_BlockBuilder
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_BlockBuilder_Helper_Import extends Mage_Core_Helper_Abstract
{
	public function buildQueryImport($data = array(), $table_name = "", $override = true, $store_id = 0, $where = '') {
		$query = false;
		$binds = array();
		if($data) {
			if($override) {
				$query = "REPLACE INTO `".$table_name."` ";
			} else {
				$query = "INSERT IGNORE INTO `".$table_name."` ";
			}
			$stores = Mage::helper("ves_blockbuilder")->getAllStores();
			$fields = $values = array();
			foreach($data as $key=>$val) {
				if($val) {
					if($key == "store_id" && !in_array($val, $stores)){
						$val = $store_id;
					}
					$fields[] = "`".$key."`";
					$values[] = ":".strtolower($key);
					$binds[strtolower($key)] = $val;
				}
			}
			$query .= " (".implode(",", $fields).") VALUES (".implode(",", $values).")";
		}
		return array($query, $binds);
	}
}