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
class Ves_BlockBuilder_Block_Adminhtml_Install_Export_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
				)
			);

		$fieldset = $form->addFieldset('display', array(
			'legend'	=> Mage::helper('ves_blockbuilder')->__('Export settings'),
			'class'		=> 'fieldset-wide'
			));

		$fieldset->addField('file_name', 'text', array(
			'name'		=> 'file_name',
			'label'		=> Mage::helper('ves_blockbuilder')->__('File Name'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('File Name'),
			'note'		=> Mage::helper('ves_blockbuilder')->__('This will be the name of the file in which configuration will be saved. You can enter any name you want.'),
			'required'	=> true,
			));

		$fieldPreset = $fieldset->addField('isdowload', 'select', array(
			'name'		=> 'isdowload',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Download File'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Download File'),
			'values'	=> Mage::getModel('adminhtml/system_config_source_yesno')
			->toOptionArray(),
			));

		$fieldStores = $fieldset->addField('folder', 'text', array(
			'name'		=> 'folder',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Folder'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Folder'),
			'note'		=> Mage::helper('ves_blockbuilder')->__('For example: <strong>var</strong> .It will export file into var/ folder'),
			'value'	=> 'var'
			));

		$fieldset->addField('modules', 'multiselect', array(
			'name'		=> 'modules',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Modules'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Modules'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_packageModules')->toOptionArray()
			));
		
		$fieldStores = $fieldset->addField('store_id', 'select', array(
			'name'		=> 'stores',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Configuration Scope'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Configuration Scope'),
			'note'		=> Mage::helper('ves_blockbuilder')->__('Configuration of selected store will be saved in a file. Apply for all system config of modules'),
			'values'	=> Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
			));

		$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
		$fieldStores->setRenderer($renderer);

		$fieldset->addField('pagebuilder', 'multiselect', array(
			'name'		=> 'pagebuilder',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Page Builder Profiles'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Page Builder Profiles'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_pagebuilderProfiles')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('blockbuilder', 'multiselect', array(
			'name'		=> 'blockbuilder',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Block Builder Profiles'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Block Builder Profiles'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_blockbuilderProfiles')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('productbuilder', 'multiselect', array(
			'name'		=> 'productbuilder',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Product Builder Profiles'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Product Builder Profiles'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_productbuilderProfiles')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('livecss', 'multiselect', array(
			'name'		=> 'livecss',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Css Selectors'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Css Selectors'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_cssSelectorProfiles')
			->toOptionArray($this->getRequest()->getParam('package'))
			));


		$fieldset->addField('cmspages', 'multiselect', array(
			'name'		=> 'cmspages',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select CMS Pages'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select CMS Pages'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_cmsPages')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('staticblocks', 'multiselect', array(
			'name'		=> 'staticblocks',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Static Blocks to Export'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Static Blocks to Export'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_staticBlocks')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('widgets', 'multiselect', array(
			'name'		=> 'widgets',
			'label'		=> Mage::helper('ves_blockbuilder')->__('Select Widgets'),
			'title'		=> Mage::helper('ves_blockbuilder')->__('Select Widgets'),
			'values'	=> Mage::getModel('ves_blockbuilder/system_config_source_install_widgets')
			->toOptionArray($this->getRequest()->getParam('package'))
			));

		$fieldset->addField('action_type', 'hidden', array(
			'name'  => 'action_type',
			'value' => 'export',
			));

		//Set action and other parameters
		$actionUrl = $this->getUrl('*/*/export');
		$form->setAction($actionUrl);
		$form->setUseContainer(true);

		$this->setForm($form);
		return parent::_prepareForm();
	}
}