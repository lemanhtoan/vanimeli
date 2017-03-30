<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_BlockBuilder_Model_Import_Block extends Mage_Core_Model_Abstract {


	public function process($filepath, $stores = array()) {
		
		// get the file extension
		$array = pathinfo($filepath);
		switch ($array["extension"] ) {

			case "csv":
			case "txt":
				Mage::getModel('ves_blockbuilder/import_block_csv')->process($filepath, $stores);
			break;

			default:
				Mage::throwException("File is of unknown format, cannot process to import");
			break;
 		} // end

	} // end

}
