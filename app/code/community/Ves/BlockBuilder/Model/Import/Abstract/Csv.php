<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_BlockBuilder_Model_Import_Abstract_Csv extends Mage_Core_Model_Abstract {

	private $array_delimiter = ';';
	private $delimiter = ',';
	private $header_columns;
	protected $_modelname; // = 'cms/block'; = 'cms/page';

	private function openFile($filepath) {
		$handle = null;
		
		if (($handle = fopen($filepath, "r")) !== FALSE) {
			return $handle;
		} else {
			throw new Exception('Error opening file ' . $filepath);
		} // end

	} // end

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
	public function process($filepath, $stores = array()) {

		$handle = $this->openFile($filepath);
		$row = 0;
		if ( $handle != null ) {

			// loop thru all rows
			while (($data = fgetcsv($handle, 110000, $this->delimiter)) !== FALSE) {
				$row++;

				// if this is the head row keep this as a column reference
				if ($row == 1) {
					$this->mapHeader($data);
					continue;
				}

				// make sure we have a reset model object
				//$staticblock = Mage::getSingleton($this->_modelname)->clearInstance();

				$model = Mage::getModel($this->_modelname);
				$identifier = "";
				// get the identifier column for this row
				if( $id_key = $this->getIdentifierColumnIndex() ) {
					$identifier = $data[$id_key];

					// if a static block already exists for this identifier - load the data
					$model->load($identifier);
				} else {
					$model->load(0);
				}


				// loop through each column
				foreach ($this->header_columns as $index => $keyname) {
					$keyname = strtolower($keyname);
					$keyname = str_replace(" ", "_", $keyname);
					$keyname = trim($keyname);
					$import_stores = $stores;
					// switch statement incase we need to do logic depending on the column name
					switch ($keyname) {

						case "stores":
							// stores are separated with ";" when they're exported
							$tmp_stores = $data[$index];
							$stores_array = explode(';', $tmp_stores);
							$import_stores = $this->restoreArray($stores, $stores_array);
							//$model->setData("stores", $stores);
							//$model->setData('store_id', $stores);
						break;

						case "block_id":
						case "template_id":
						case "selector_id":
							// dont need to add this. @todo remove this column from export.
						break;

						default:
							// fgetcsv encodes everything
							$model->setData($keyname, html_entity_decode($data[$index]));
						break;

					} // end switch
				} // end for
				
				if(!empty($import_stores)) {
					$model->setData('store_id', $import_stores);
					$model->setData('stores', $import_stores);
				}
				
				// save our block
				try {
					$model->save();

					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_blockbuilder')->__('Updated ' . $identifier));
				} catch (Exception $e) {
					Mage::throwException($e->getMessage() . ' URL Key = ' . $data[$this->getIdentifierColumnIndex()]);
				}
			} // end while
		}// end if

	} // end

	private function mapHeader($data_array) {
		$this->header_columns = $data_array;
	}

	private function getIdentifierColumnIndex() {
		$header = $this->header_columns;
		$index = array_search('Identifier', $header);
		return $index;
	}

}
