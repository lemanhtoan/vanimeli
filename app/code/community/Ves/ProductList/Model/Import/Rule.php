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
class Ves_ProductList_Model_Import_Rule extends Mage_Core_Model_Abstract {

	private $array_delimiter = ';';
	private $delimiter = ',';
	private $header_columns;

	public function process($filepath, $stores = array()) {
		$array = pathinfo($filepath);
		switch ($array["extension"] ) {
			case "csv":
			$this->importCsv($filepath, $stores);
			break;
			default:
			Mage::throwException("File is of unknown format, cannot process to import");
			break;
		}
	}

	private function openFile($filepath) {
		$handle = null;
		if (($handle = fopen($filepath, "r")) !== FALSE) {
			return $handle;
		} else {
			throw new Exception('Error opening file ' . $filepath);
		}
	}

	public function restoreArray($default_array = array(), $import_array = array()) {
		if(!empty($import_array)) {
			$tmp = array();
			foreach($import_array as $k=>$v) {
				if(in_array($v, $default_array) || $v == 0) {
					$tmp[] = $v;
				}
			}
		}
		if(empty($tmp)) {
			$tmp = array(0);
		}
		return $tmp;
	}

	public function importCsv($filepath, $stores = array()) {
		$handle = $this->openFile($filepath);
		$row = 0;
		if ( $handle != null ) {
			while (($data = fgetcsv($handle, 110000, $this->delimiter)) !== FALSE) {
				$row++;
				// if this is the head row keep this as a column reference
				if ($row == 1) {
					$this->mapHeader($data);
					continue;
				}
				// make sure we have a reset model object
				$model = Mage::getModel('productlist/rule');
				$name = "";

				// loop through each column
				foreach ($this->header_columns as $index => $keyname) {
					$keyname = strtolower($keyname);
					$keyname = str_replace(" ", "_", $keyname);
					$import_stores = $stores;
					// switch statement incase we need to do logic depending on the column name
					switch ($keyname) {
						case "rule_id":
						break;
						case "name";
						$name = $data[$index];
						$model->setData('name', html_entity_decode($data[$index]));
						break;
						case "product_list_rule";
						$model->setData('product_list_rule', html_entity_decode($data[$index]));
						break;
						case "identifier":
						$identifier = $data[$index];
						$resource = Mage::getSingleton('core/resource');
						$readConnection = $resource->getConnection('core_read');
						$table = $resource->getTableName('productlist/rule');
						$db_identifier = $readConnection->fetchCol('SELECT identifier FROM ' . $table.' WHERE identifier = "'.$identifier.'"');
						if(is_array($db_identifier) && count($db_identifier)>0){
							$identifier = $db_identifier[0].'-'.time();
						}
						$model->setData('identifier',$identifier);
						break;
						case "stores":
						$tmp_stores = $data[$index];
						$stores_array = explode('-', $tmp_stores);
						$import_stores = $this->restoreArray($stores, $stores_array);
						if(!empty($import_stores)) {
							$model->setData('stores', $import_stores);
						}
						break;
						case "customer_group":
						$customers = Mage::helper('productlist')->getCustomerGroups();
						$tmp_customergroups = $data[$index];
						$customer_array = explode('-', $tmp_customergroups);
						$import_stores = $this->restoreArray($customers, $customer_array);
						if(!empty($import_stores)) {
							$model->setData('customer_group', $import_stores);
						}
						break;
						default:
						$model->setData($keyname, html_entity_decode($data[$index]));
						break;
					} // end switch
				} // end foreach

				// save our block
				try {
					$model->save();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productlist')->__('Updated: ' . $name));
				} catch (Exception $e) {
					Mage::throwException($e->getMessage() . ' Category ' . $data[$this->getTitleColumnIndex()]);
				}
			}
		}
	}

	private function mapHeader($data_array) {
		$this->header_columns = $data_array;
	}

	private function getTitleColumnIndex() {
		$header = $this->header_columns;
		$index = array_search('Title', $header);
		return $index;
	}
}