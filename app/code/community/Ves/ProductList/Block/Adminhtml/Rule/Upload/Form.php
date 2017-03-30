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
class Ves_ProductList_Block_Adminhtml_Rule_Upload_Form extends Mage_Adminhtml_Block_Widget_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form(array(
			'id' => 'upload_form',
			'action' => $this->getUrl('*/*/importCsv'),
			'method' => 'post',
			'enctype' => 'multipart/form-data'
			)
		);

		$fieldset = $form->addFieldset('upload_json', array('legend' => Mage::helper('productlist')->__('Import Profile From CSV')));

		$fieldset->addField('importfile', 'file', array(
			'label'     => Mage::helper('productlist')->__('Upload CSV File'),
			'required'  => true,
			'name'      => 'importfile',
			));

		$fieldset->addField('submit', 'note', array(
			'type' => 'submit',
			'text' => $this->getButtonHtml(
				Mage::helper('productlist')->__('Upload & Import'),
				"upload_form.submit();",
				'upload'
				)
			));

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();

	}

}