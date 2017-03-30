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

class Ves_BlockBuilder_Model_System_Config_Source_Install_CmsPages{
	public function toOptionArray()
	{
		$staticblocks = Mage::getModel('cms/block')->getCollection();
		$outputs = array();

		$collection = Mage::getModel('cms/page')->getCollection();

		foreach ($collection as $page) {
			$outputs[] = array(
				'label' => $page->getTitle(),
				'value' => $page->getPageId(),
				);
		}
		return $outputs;
	}
}