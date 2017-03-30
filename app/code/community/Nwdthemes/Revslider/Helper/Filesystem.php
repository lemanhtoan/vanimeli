<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Helper_Filesystem extends Mage_Core_Helper_Abstract {

	/**
	 *	Init filesystem class
	 */

	public function WP_Filesystem() {
		global $wp_filesystem;
		$wp_filesystem = $this;
		return true;
	}

	/**
	 *	Unzip file
	 *
	 *	@param	string	Zip file
	 *	@param	string	Destination path
	 *	@return boolean
	 */

	public function unzip_file($file, $path) {
		if (class_exists('ZipArchive'))
		{
			$zip = new ZipArchive;
			$zipResult = $zip->open($file, ZIPARCHIVE::CREATE);
			if ($zipResult === true)
			{
				$zip->extractTo($path);
				$zip->close();
			}
		}
		else
		{
			include_once Mage::getBaseDir('lib') . '/Nwdthemes/Revslider/framework/pclzip.lib.php';
			$pclZip = new PclZip($file);
			$pclZipResult = $pclZip->extract(PCLZIP_OPT_PATH, $path);
			$zipResult = $pclZipResult !== 0;
		}
		return $zipResult;
	}

	public function recurse_move($src, $dst) {
		$dir = opendir($src);
		$this->wp_mkdir_p($dst);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) )
				{
					$this->recurse_move($src . '/' . $file,$dst . '/' . $file);
				}
				else
				{
					$this->rename($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
		rmdir($src);
	}

	/**
	 *	Make writable directory
	 *
	 *	@param	string	$path
	 *	@return	bool
	 */

	public function wp_mkdir_p($dir) {
		if (file_exists($dir) && is_dir($dir))
		{
			return true;
		}
		else
		{
			return @mkdir($dir);
		}
	}

	/**
	 *	Check if file exists
	 *
	 *	@param	string	Path to file
	 *	@return boolean
	 */

	public function exists($path) {
		return file_exists($path);
	}

	/**
	 *	Read file
	 *
	 *	@param	string	Path to file
	 *	@return string
	 */

	public function get_contents($path) {
		return file_get_contents($path);
	}

	/**
	 *	Delete file
	 *
	 *	@param	string	Path to file
	 *	@param	boolean	Is recursive
	 *	@return string
	 */

	public function delete($path, $recursive = false) {
		if (is_dir($path))
		{
			$dir = opendir($path);
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($path . '/' . $file) )
					{
						if ($recursive)
						{
							$this->delete($path . '/' . $file, $recursive);
						}
					}
					else
					{
						unlink($path . '/' . $file);
					}
				}
			}
			closedir($dir);
			rmdir($path);
		}
		else
		{
			unlink($path);
		}
	}
	
}