<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Model_Optionscss extends Mage_Core_Model_Abstract
{
	/**
	 * config path
	 * @var string
	 */
	private $_module_config_path;

	/**
	 * config section
	 * @var string
	 */
	private $_module_config_section;

	/**
	 * css file prefix
	 * @var string
	 */
	private $_css_prefix;

	/**
	 * css file name
	 * @var string
	 */
	private $_css_file;

	/**
	 * css path
	 * @var string
	 */
	private $_css_path;

	/**
	 * css template path
	 * @var string
	 */
	private $_css_template_path;

	public function _construct()
	{
		$this->_css_file = '_%WEBSITE%_%STORE%.css';
		$this->_css_path = Mage::getBaseDir() . '/skin/frontend/base/default/css/nwdthemes/';
	}

	/**
	 * @return string
	 */
	public function getCssFile()
	{
		return $this->_css_file;
	}

	/**
	 * @return string
	 */
	public function getCssPath()
	{
		return $this->_css_path;
	}

	/**
	 * @param string $module_config_path
	 */
	public function setModuleConfigPath($module_config_path)
	{
		$this->_module_config_path = $module_config_path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getModuleConfigPath()
	{
		return $this->_module_config_path;
	}

	/**
	 * @param string $module_config_section
	 */
	public function setModuleConfigSection($module_config_section)
	{
		$this->_module_config_section = $module_config_section;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getModuleConfigSection()
	{
		return $this->_module_config_section;
	}

	/**
	 * @param string $css_prefix
	 */
	public function setCssPrefix($css_prefix)
	{
		$this->_css_prefix = $css_prefix;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCssPrefix()
	{
		return $this->_css_prefix;
	}

	/**
	 * @param string $css_template_path
	 */
	public function setCssTemplatePath($css_template_path)
	{
		$this->_css_template_path = $css_template_path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCssTemplatePath()
	{
		return $this->_css_template_path;
	}

	protected function _storeCss($website, $store)
	{
		$css = Mage::app()
			->getLayout()
			->createBlock("core/template")
			->setData('area', 'frontend')
			->setData('website', $website)
			->setData('store', $store)
			->setData('cfg',
				Mage::helper('nwdall')->getCfg(
					$this->getModuleConfigSection(),
					$this->getModuleConfigPath(),
					$store->getId()
				)
			)
			->setTemplate( $this->getCssTemplatePath() )
			->toHtml();

		if ( empty($css) ) {
			throw new Exception(  Mage::helper('nwdall')->__('Css generation error: using template %s', $this->getCssTemplatePath()) );
		}

		$filename = str_replace(
			array('%WEBSITE%', '%STORE%'),
			array($website->getCode(), $store->getCode()),
			$this->getCssPrefix() . $this->getCssFile()
		);
		try {
			$file = new Varien_Io_File();
			$file->setAllowCreateFolders(true)
				->open(array('path' => $this->getCssPath()));
			$file->streamOpen($filename, 'w+');
			$file->streamWrite($css);
			$file->streamClose();
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('nwdall')->__('Css generation error: %s', $this->getCssPath() . $filename) . '<br/>' . $e->getMessage());
			Mage::logException($e);
		}
	}

	/**
	 * parse css based on config settings
	 *
	 * @param $website
	 * @param $store
	 * @throws Exception
	 */
	public function parse( $website = '', $store = '' )
	{
		Mage::getConfig()->reinit();

		if ( $website ) {
			if ( $store ) {
				$this->_storeCss($website, $store);
				return;
			}

			foreach ($website->getStores() as $_store) {
				$this->_storeCss($website, $_store);
			}
			return;
		}

		$websites = Mage::app()->getWebsites();
		foreach ($websites as $_website) {
			foreach ($_website->getStores() as $_store) {
				$this->_storeCss($_website, $_store);
			}
		}
	}

}