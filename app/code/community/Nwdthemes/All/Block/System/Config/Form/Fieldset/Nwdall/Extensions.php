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

class Nwdthemes_All_Block_System_Config_Form_Fieldset_Nwdall_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
		$html = $this->_getHeaderHtml($element);

        $modules = Mage::helper('nwdall')->getInstalledModules();
        foreach ($modules as $moduleName) {
        	$html.= $this->_getFieldHtml($element, $moduleName);
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

	protected function _getFieldHtml($fieldset, $moduleName)
    {
    	$ver = Mage::getConfig()->getModuleConfig($moduleName)->version;
		if($ver){
			$field = $fieldset->addField($moduleName, 'label',
				array(
					'name' => $moduleName,
					'label' => $moduleName,
					'value' => $ver,
				))
				->setRenderer( Mage::getBlockSingleton('adminhtml/system_config_form_field') );
			return $field->toHtml();
		}
		return '';
    }
}
