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
class Ves_ProductList_Model_System_Config_ListStyles{
	public function toOptionArray(){
		return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('Default')),
                  array('value' => "primary", 'label'=>Mage::helper('adminhtml')->__('Primary')),
                  array('value' => "danger", 'label'=>Mage::helper('adminhtml')->__('Danger')),
                  array('value' => "info", 'label'=>Mage::helper('adminhtml')->__('Info')),
                  array('value' => "warning", 'label'=>Mage::helper('adminhtml')->__('Warning')),
                  array('value' => "highlighted", 'label'=>Mage::helper('adminhtml')->__('Highlighted')),
                  array('value' => "nopadding", 'label'=>Mage::helper('adminhtml')->__('Nopadding'))
                  );
	}
}