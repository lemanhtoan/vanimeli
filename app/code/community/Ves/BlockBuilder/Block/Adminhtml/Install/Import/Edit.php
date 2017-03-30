<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Regular License.
 * You may not use any part of the code in whole or part in any other software
 * or product or website.
 *
 * @author		Infortis
 * @copyright	Copyright (c) 2014 Infortis
 * @license		Regular License http://themeforest.net/licenses/regular 
 */

class Ves_BlockBuilder_Block_Adminhtml_Install_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_blockGroup = 'ves_blockbuilder';
		$this->_controller = 'adminhtml_install_import';
		$this->_headerText = Mage::helper('ves_blockbuilder')->__('Import Configuration');
		$this->_updateButton('save', 'label', Mage::helper('ves_blockbuilder')->__('Import Configuration'));
		$this->_removeButton('back');
		$this->_updateButton('reset', 'label', Mage::helper('ves_blockbuilder')->__('Reset Form'));
	}
}
