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
class Ves_ProductList_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('productlist_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('productlist')->__('Product List Rule Information'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array(
			'label'     => Mage::helper('productlist')->__('General'),
			'title'     => Mage::helper('productlist')->__('Product List Rule Information'),
			'content'   => $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_form')->toHtml(),
			));

		$this->addTab('conditions_section', array(
			'label'     => $this->__('Conditions'),
			'title'     => $this->__('Conditions'),
			'content'   => $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_conditions')->toHtml(),
			));

		$this->addTab('display_section', array(
			'label' => $this->__('Display Settings'),
			'title' => $this->__('Design Settings'),
			'content' => $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_display')->toHtml(),
			));

		$this->addTab('design_section', array(
			'label' => $this->__('Custom Design'),
			'title' => $this->__('Custom Design'),
			'content' => $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_design')->toHtml(),
			));

		$this->addTab('meta_section', array(
			'label' => $this->__('SEO'),
			'title' => $this->__('SEO'),
			'content' => $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_meta')->toHtml(),
			));
		return parent::_beforeToHtml();
	}
}