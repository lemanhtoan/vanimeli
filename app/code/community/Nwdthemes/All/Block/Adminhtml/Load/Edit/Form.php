<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Block_Adminhtml_Load_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
	          'id' 		=> 'load_form',
	          'name' 	=> 'loadForm',
	          'action' 	=> $this->getUrl('*/*/load'),
	          'method' 	=> 'post',
			  'enctype'	=> 'multipart/form-data'
	    ));
		$form->setUseContainer(true);
		$this->setForm($form);

        $fieldSet = $form->addFieldset('nwdthemes', array('legend' => Mage::helper('nwdall')->__('Load Configuration')));
        $fieldSet->addField('config', 'file', array(
            'label' 	=> Mage::helper('nwdall')->__('Select File'),
            'required' 	=> true,
            'name' 		=> 'config'
        ));
        $fieldSet->addField('store_id', 'select', array(
            'name' => 'store',
            'label' => Mage::helper('nwdall')->__('Store View'),
            'title' => Mage::helper('nwdall')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));

		return parent::_prepareForm();
	}

}
