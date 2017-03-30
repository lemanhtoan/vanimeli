<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author        Nwdthemes <mail@nwdthemes.com>
 * @link        http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Block_Adminhtml_System_Config_Form_Field_Radiosimage extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	/**
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return String
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$html = '';
		$value = $element->getValue();
		if ($values = $element->getValues()) {
			foreach ($values as $option) {
				$html .= $this->_optionToHtml($element, $option, $value);
			}
		}
		$html .= $element->getAfterElementHtml();

		return $html;
	}

	/**
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @param Array $option
	 * @param String $selected
	 * @return String
	 */
	protected function _optionToHtml($element, $option, $selected)
	{
		$html = '<div class="clear">';
		$html .= '<input type="radio"' . $element->serialize(array('name', 'class', 'style'));
		if (is_array($option)) {
			$html .= 'value="' . htmlspecialchars($option['value'], ENT_COMPAT) . '"  id="' . $element->getHtmlId() . $option['value'] . '"';
			if ($option['value'] == $selected) {
				$html .= ' checked="checked"';
			}
			$html .= ' />';
			$html .= '&nbsp;<label class="inline" for="' . $element->getHtmlId() . $option['value'] . '">' . $option['label'] . '</label>';
		} elseif ($option instanceof Varien_Object) {
			$html .= 'id="' . $element->getHtmlId() . $option->getValue() . '"' . $option->serialize(array('label', 'title', 'value', 'class', 'style'));
			if (in_array($option->getValue(), $selected)) {
				$html .= ' checked="checked"';
			}
			$html .= ' />';
			$html .= '&nbsp;<label class="inline" for="' . $element->getHtmlId() . $option->getValue() . '">' . $option->getLabel() . '</label>';
		}
		$html .= '<br/><img src="' . Mage::getDesign()->getSkinUrl('images/nwdthemes/' . $option['value'] . '.png') . '" alt="" />';
		$html .= '</div>';
		$html .= $element->getSeparator() . "\n";
		return $html;
	}

}