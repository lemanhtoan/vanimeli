<?php
 /*------------------------------------------------------------------------
  # VenusTheme Block Builder Module
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_BlockBuilder_Helper_Cssgen extends Mage_Core_Helper_Abstract
{
	/**
	 * Path and directory of the automatically generated CSS
	 *
	 * @var string
	 */
	protected $_generatedCssFolder;
	protected $_generatedCssPath;
	protected $_generatedCssDir;
	protected $_templatePath;
	
	public function __construct()
	{
		//Create paths
		$theme_name =  Mage::getDesign()->getTheme('frontend');
	    $package = Mage::getSingleton('core/design_package')->getPackageName();
		$this->_generatedCssFolder = 'css/customize/';
		$this->_generatedCssPath = 'frontend/'.$package.'/'.$theme_name.'/' . $this->_generatedCssFolder;
		$this->_generatedCssDir = Mage::getBaseDir('skin') . '/' . $this->_generatedCssPath;
		$this->_templatePath = 'frontend/'.$package.'/'.$theme_name.'/css/';
	}
	
	/**
	 * Get directory of automatically generated CSS
	 *
	 * @return string
	 */
	public function getGeneratedCssDir()
    {
        return $this->_generatedCssDir;
    }

	/**
	 * Get path to CSS template
	 *
	 * @return string
	 */
	public function getTemplatePath()
    {
        return $this->_templatePath;
    }

	/**
	 * Get file path: CSS design
	 *
	 * @return string
	 */
	public function getProfileFile()
	{
		$css_profile = Mage::getStoreConfig("ves_livecss/general/css_profile");
		if($css_profile) {
			return $this->_generatedCssFolder . $css_profile . '.css';
		}
		return ;
	}
}
