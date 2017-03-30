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

class Avenla_KlarnaCheckout_Model_Source_FlowType
{
	public function toOptionArray()
	{
		$options = array(
			array(
				'label' => Mage::helper('klarnaCheckout')->__('Disabled'),
				'value' => Avenla_KlarnaCheckout_Model_Config::B2B_DISABLED
			),
			array(
				'label' => Mage::helper('klarnaCheckout')->__('Enabled'),
				'value' => Avenla_KlarnaCheckout_Model_Config::B2B_ENABLED
			),
			array(
				'label' => Mage::helper('klarnaCheckout')->__('Enabled (B2B as default)'),
				'value' => Avenla_KlarnaCheckout_Model_Config::B2B_ENABLED_B2B_DEFAULT
			)
		);

		return $options;
	}
}
