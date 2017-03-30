<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Block_Adminhtml_Master_View extends Mage_Adminhtml_Block_Template
{
	public function __construct() {

		parent::__construct();

		global $revSliderVersion;

		$wrapperClass = "";
		if(RevSliderGlobals::$isNewVersion == false)
			$wrapperClass = " oldwp";

		$nonce = Mage::helper('nwdrevslider/framework')->wp_create_nonce("revslider_actions");

		$rsop = new RevSliderOperations();
		$glval = $rsop->getGeneralSettingsValues();

		$waitstyle = '';
		if(isset(Nwdthemes_Revslider_Helper_Data::$_REQUEST['update_shop'])){
			$waitstyle = 'display:block';
		}

		$this->assign(get_defined_vars());
	}
	
	/**
	 * Check is folders exist and have writable permissions
	 *
	 * @return string Error message if exist
	 */ 
	
	public function checkFolderPermissionsErrors() {
		$arrFolders = array(
			'image_dir'		=> Mage::getConfig()->getOptions()->getMediaDir() . DS . Mage::helper('nwdrevslider/images')->getImageDir(),
			'thumb_dir'		=> Mage::getConfig()->getOptions()->getMediaDir() . DS . Mage::helper('nwdrevslider/images')->getImageThumbDir()
		);
		
		$ioFile = new Varien_Io_File();

		$arrErrors = array();
		foreach ($arrFolders as $_folder) {
			try {
				if ( ! ( $ioFile->checkandcreatefolder($_folder) && $ioFile->isWriteable($_folder) ) )
				{
					$arrErrors[] = $_folder;
				}
			} catch (Exception $e) {
				$arrErrors[] = $_folder;
				Mage::logException($e);
			}			
		}
		
		$strError = $arrErrors ? Mage::helper('nwdrevslider')->__('Following directories not found or not writable, please change permissions to: ') . implode(' , ', $arrErrors) : '';
		
		return $strError;
	}
}
