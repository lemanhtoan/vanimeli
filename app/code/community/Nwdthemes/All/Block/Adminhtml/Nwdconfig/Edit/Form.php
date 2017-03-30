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

class Nwdthemes_All_Block_Adminhtml_Nwdconfig_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _buildFieldSet($name, $items)
    {
        $form = $this->getForm();
        $modules = Mage::helper('nwdall')->getInstalledModules();

        $fieldSet = $form->addFieldset('nwdthemes_'.$name, array('legend' => Mage::helper('nwdall')->__($name)));
        $item_keys = array_keys($items);
        foreach ($item_keys as $moduleName) {
            if ( in_array($moduleName, $modules) ) {
                $fieldSet->addField($name.'[' . $moduleName . ']', 'checkbox', array(
                    'label' => $items[$moduleName],
                    'name' => $name.'[]',
                    'value' => $moduleName,
                ));
            }
        }
    }

	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
	          'id' 		=> 'edit_form',
	          'action' 	=> $this->getUrl('*/*/save'),
	          'method' 	=> 'post',
			  'enctype'	=> 'multipart/form-data'
	    ));
		$form->setUseContainer(true);
		$this->setForm($form);

        $fieldSet = $form->addFieldset('nwdthemes', array('legend' => Mage::helper('nwdall')->__('General')));
        $fieldSet->addField('filename', 'text', array(
            'name' => 'filename',
            'label' => Mage::helper('nwdall')->__('Filename'),
            'class' => 'validate-data',
            'required' => true,
            'value' => 'nwd_settings',
        ));
        $fieldSet->addField('store_id', 'select', array(
            'name' => 'store',
            'label' => Mage::helper('nwdall')->__('Store View'),
            'title' => Mage::helper('nwdall')->__('Store View'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));

        $this->_buildFieldSet('Extensions', Mage::helper('nwdall')->getExtensionsList());
        $this->_buildFieldSet('Themes', Mage::helper('nwdall')->getThemesList());

		return parent::_prepareForm();
	}

}
