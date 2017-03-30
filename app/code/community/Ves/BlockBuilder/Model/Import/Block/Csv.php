<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_BlockBuilder_Model_Import_Block_Csv extends Ves_BlockBuilder_Model_Import_Abstract_Csv {

	private $array_delimiter = ';';
	private $delimiter = ',';
	private $header_columns;

	protected $_modelname = 'ves_blockbuilder/block';

}
