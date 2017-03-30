<?php
/**
 * This file is released under a custom license by Avenla Oy.
 * All rights reserved
 *
 * License and more information can be found at http://productdownloads.avenla.com/magento-modules/klarna-checkout/
 * For questions and support - klarna-support@avenla.com
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 * @copyright  Copyright (c) Avenla Oy
 * @link       http://www.avenla.fi
 */

/**
 * Avenla KlarnaCheckout
 *
 * @category   Avenla
 * @package    Avenla_KlarnaCheckout
 */
class Avenla_KlarnaCheckout_Block_Adminhtml_System_Config_Field_Pclass extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (!$this->getTemplate())
			$this->setTemplate('KCO/system/config/field/pclass.phtml');

		return $this;
	}

	/**
	 * Unset some non-related element parameters
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
		return parent::render($element);
    }

	/**
	 * Return element html
	 *
	 * @param  Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$oData = $element->getOriginalData();
		$buttonData = array(
			'button_label' => Mage::helper('klarnaCheckout')->__($oData['button_label']),
			'html_id' => $element->getHtmlId()
		);
		$this->addData($buttonData);

		return $this->_toHtml();
	}

	/**
	 * Get url for update action
	 *
	 * @return string
	 */
	public function getAjaxUpdateUrl()
	{
		return Mage::getUrl('adminhtml/klarnaCheckout_KCO/updatePClasses/');
	}
}