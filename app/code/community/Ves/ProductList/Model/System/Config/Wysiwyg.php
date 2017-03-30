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
class Ves_ProductList_Model_System_Config_Wysiwyg extends Mage_Cms_Model_Wysiwyg_Config{
	public function getConfig($data = array())
	{
		$config = parent::getConfig($data);
		$urlModel = Mage::getSingleton('adminhtml/url');
		$config->addData(
			array(
				'files_browser_window_url' => $urlModel->getUrl('adminhtml/cms_wysiwyg_images/index/'),
				'directives_url'           => $urlModel->getUrl('adminhtml/cms_wysiwyg/directive'),
				'directives_url_quoted'    => preg_quote($config->getData('directives_url')),
				'widget_window_url'        => $urlModel->getUrl('adminhtml/widget/index'),
				)
			);
		return $config;
	}
}