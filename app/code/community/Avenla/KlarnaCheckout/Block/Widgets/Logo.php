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

class Avenla_KlarnaCheckout_Block_Widgets_Logo extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface
{
	protected function _toHtml()
	{
		$width = intval($this->getData('width'));

		if($width < 1)
			$width = 350;

		$imgSrc = Mage::helper('klarnaCheckout')->getLogoSrc($width, $this->getData('background'));
		return '<img src='.$imgSrc.' />';
	}
}