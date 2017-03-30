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
class Ves_ProductList_Block_Adminhtml_Rule_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$design = $form->addFieldset('custom_fieldset', array(
			'legend' => $this->__('Custom Design')
			));

		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
		$design->addField('custom_design_from', 'date', array(
			'name'   => 'custom_design_from',
			'label'  => $this->__('Active From'),
			'title'  => $this->__('Active From'),
			'image'  => $this->getSkinUrl('images/grid-cal.gif'),
			'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
			'format'       => $dateFormatIso,
			));

		$design->addField('custom_design_to', 'date', array(
			'name'   => 'custom_design_to',
			'label'  => $this->__('Active To'),
			'title'  => $this->__('Active To'),
			'image'  => $this->getSkinUrl('images/grid-cal.gif'),
			'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
			'format'       => $dateFormatIso,
			));

		$design->addField('page_layout', 'select', array(
			'label'     => Mage::helper('productlist')->__('Page Layout'),
			'name'      => 'page_layout',
			'values'    => Mage::helper('productlist')->getPageLayoutList(),
			));

		$design->addField('custom_layout_update', 'textarea', array(
			'label'     => Mage::helper('productlist')->__('Custom Layout Update'),
			'name'      => 'custom_layout_update',
			'style'     => 'width:600px;height:100px;',
			));


		if (Mage::getSingleton('adminhtml/session')->getProductlistData())
		{
			$form->setValues(Mage::getSingleton('adminhtml/session')->getProductlistData());
			Mage::getSingleton('adminhtml/session')->setProductlistData(null);
		}
		elseif (Mage::registry('productlist_data'))
		{
			$form->setValues(Mage::registry('productlist_data')->getData());
		}

		return parent::_prepareForm();
	}
}