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
class Ves_BlockBuilder_Block_Adminhtml_Install_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * Preparing form
	 *
	 * @return Mage_Adminhtml_Block_Widget_Form
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(
			array(
				'id'		=> 'edit_form',
				'method'	=> 'post',
				'enctype'	=> 'multipart/form-data'
				)
			);

		$fieldset = $form->addFieldset('display', array(
			'legend'	=> Mage::helper('ves_blockbuilder')->__('Import settings'),
			'class'		=> 'fieldset-wide',
			));

		$fieldDataImportFile = $fieldset->addField('file_path', 'text', array(
			'name'		=> 'file_path',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Input file path'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Input file path'),
			'note'		=> Mage::helper('ves_blockbuilder')->__('For example: <strong>var/profile1.json</strong> .It will import file profile1.json in the folder var/')
			));

		$fieldDataImportFile = $fieldset->addField('data_import_file', 'file', array(
			'name'		=> 'data_import_file',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select File With Saved Configuration to Import'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select File With Saved Configuration to Import'),
			'after_element_html' => '
			'
			));

		$fieldPreset = $fieldset->addField('overwrite_blocks', 'select', array(
			'name'		=> 'overwrite_blocks',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Overwrite Existing Blocks'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Overwrite Existing Blocks'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')
			->toOptionArray(),
			'note'		=> Mage::helper('ves_blockbuilder')->__("<span>- If set to <b>Yes</b>, the import data will override exist data. Check exits data according to the field <b>URL Key</b> of <b>Cms Pages</b> and the field <b>Identifier</b> of <b>Static Block</b>.<br/>- If set to <b>No</b>, the function import will empty data of all table of <b>CMS Page</b> and <b>Static Block</b>, then insert import data.</span>
				")
			));

		$fieldStores = $fieldset->addField('store_id', 'select', array(
			'name'		=> 'stores',
			'label'		=> Mage::helper('cms')->__('Configuration Scope'),
			'title'		=> Mage::helper('cms')->__('Configuration Scope'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Configuration Scope'),
			'note'		=> Mage::helper('ves_blockbuilder')->__("Imported configuration settings will be applied to selected scope (selected store view or website). If you're not sure what is 'scope' in Magento system configuration, it is highly recommended to leave the default scope <strong>'Default Config'</strong>. In this case imported configuration will be applied to all existing store views."),
			'required'	=> true,
			'values'	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			));
		$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
		$fieldStores->setRenderer($renderer);

		$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
		$fieldStores->setRenderer($renderer);
		$fieldset->addField('action_type', 'hidden', array(
			'name'  => 'action_type',
			'value' => 'import',
			));

		$actionUrl = $this->getUrl('*/*/import');
		$form->setAction($actionUrl);
		$form->setUseContainer(true);

		$this->setForm($form);
		return parent::_prepareForm();
	}
}