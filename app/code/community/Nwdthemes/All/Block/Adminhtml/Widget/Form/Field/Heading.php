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

/**
 * Renderer for sub-heading in fieldset
 */
class Nwdthemes_All_Block_Adminhtml_Widget_Form_Field_Heading
	extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
	/**
	 * Render element html
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$after_element_html = sprintf('<script>new widgetHeading("%s");</script>', $element->getHtmlId());
		return sprintf('<tr id="row_%s"><td colspan="5"><h4 id="%s">%s ( <a href="#" id="%s_show">show</a> )</h4></td></tr>'. $after_element_html,
			$element->getHtmlId(), $element->getHtmlId(), $element->getLabel(), $element->getHtmlId()
		);
	}
}
