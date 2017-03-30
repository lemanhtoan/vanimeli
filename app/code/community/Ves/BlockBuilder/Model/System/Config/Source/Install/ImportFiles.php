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

class Ves_BlockBuilder_Model_System_Config_Source_Install_ImportFiles{
	public function toOptionArray($key)
	{
		$key = str_replace("/", DS, $key);
		$importDir = Mage::getBaseDir('skin') . DS . 'frontend' . DS . $key . DS . 'import' . DS ;
		$fileType = '.json';
		$files = glob($importDir.'*'.$fileType);
		$outputs = array();
		$outputs[] = array('label'=>'-- Please Select --','value'=>'');
		foreach ($files as $k => $v) {
			$labelFile = str_replace($importDir, "", $v);
			$file_content = file_get_contents($v);
			$file_content = Mage::helper('core')->jsonDecode($file_content);
			$created_at = $comment = '';
			if(isset($file_content['created_at']) && $file_content['created_at']!=''){
				$created_at = ' - '.$file_content['created_at'];
			}
			if(isset($file_content['comment']) && $file_content['comment']!=''){
				$comment = ' - '.$file_content['comment'];
			}
			$labelFile = $labelFile.' '.$created_at.' '.$comment;
			$outputs[] = array(
				'label' => $labelFile,
				'value' => $v,
				);
		}
		$outputs[] = array(
			'value' => 'data_import_file',
			'label' => Mage::helper('blockbuilder')->__('Upload custom file...'));
		return $outputs;
	}
}