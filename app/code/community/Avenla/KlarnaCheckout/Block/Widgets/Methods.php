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

class Avenla_KlarnaCheckout_Block_Widgets_Methods extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	protected function _toHtml()
	{
		$beforeBodyEnd = $this->getLayout()->getBlock('before_body_end');
		$script = $this->getLayout()->createBlock('core/text')->setText('<script async src="' . Avenla_KlarnaCheckout_Model_Config::KLARNA_WIDGET_SCRIPT . '"></script>');
		$beforeBodyEnd->append($script);

		$this->setTemplate('KCO/widget/methods.phtml');
		$width = intval($this->getData('width'));

		if($width < 1)
			$width = 350;

		$this->setWidth($width);
		$this->setLocale(strtolower(Mage::app()->getLocale()->getLocaleCode()));

		return parent::_toHtml();
	}
}