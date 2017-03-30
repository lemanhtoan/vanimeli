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
class Ves_ProductList_Block_Adminhtml_Rule_Edit_Tab_Display extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$sortby_list = Mage::getSingleton('catalog/category_attribute_source_sortby')->getAllOptions();

		$soure_type = $form->addFieldset('custom_fieldset', array(
            'legend' => $this->__('Default Products Carousel/Tab Data Source From')
            ));
		
        $soure_type->addField('source_type', 'select', array(
            'label'     => $this->__('Select Source'),
            'name'      => 'source_type',
            'options'   => array(
            	'best_value' => $this->__('Best Value'),
                'latest' => $this->__('Latest'),
                'new_arrival' => $this->__('New Arrival'),
                'special' => $this->__('Special'),
                'most_viewed' => $this->__('Most Popular (Most Viewed)'),
                'best_seller' => $this->__('Best Seller'),
                'top_rate' => $this->__('Top Rated'),
                'random' => $this->__('Random')
                ),
            'note' => $this->__('Sort Products From Source Type. Use to sort products on listing page and carousel/tab widget block. Default is Best Value (sort by position)'),
        ));

		$product_fieldset = $form->addFieldset('product_fieldset', array(
			'legend' => $this->__('Display Settings')
			));



        $product_fieldset->addField('available_sort_by', 'multiselect', array(
                'label'                      => 'Available Product Listing Sort By',
                'name'      				 => 'available_sort_by[]',
                'input'                      => 'multiselect',
                'values'   				 	 => $sortby_list
			));

		$product_fieldset->addField('product_order', 'select', array(
			'label'     => $this->__('Default Product Listing Sort By'),
			'name'      => 'product_order',
			'values'   	=> $sortby_list
			));

		$product_fieldset->addField('product_direction', 'select', array(
			'label'     => $this->__('Default Sort Direction'),
			'name'      => 'product_direction',
			'options'   => array(
				'desc' => $this->__('DESC'),
				'asc' => $this->__('ASC'),
				)
			));

		$product_fieldset->addField('product_number', 'text', array(
			'label'     => $this->__('Limit Products'),
			'name'      => 'product_number',
			'note'	  => Mage::helper('productlist')->__('Number products will show on a page. Leave empty to show all product')
			));

		$product_fieldset->addField('show_timer_countdown', 'select', array(
            'label'     => $this->__('Show Timer Countdown'),
            'name'      => 'show_timer_countdown',
            'options'   => array(
                '1' => $this->__('Yes'),
                '0' => $this->__('No'),
                ),
            'note' => $this->__('This option work when <b>Source</b> value is <b>Special</b>'),
            ));

		$product_fieldset->addField('show_outofstock', 'select', array(
			'label'     => $this->__('Show "Out Of Stock" Products'),
			'name'      => 'show_outofstock',
			'options'   => array(
				'1' => $this->__('Yes'),
				'2' => $this->__('No'),
				),
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