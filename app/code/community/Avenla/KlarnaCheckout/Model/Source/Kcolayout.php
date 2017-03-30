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

class Avenla_KlarnaCheckout_Model_Source_Kcolayout
{
	public function toOptionArray()
	{
		$options = array(
			array(
				'label' => Mage::helper('klarnaCheckout')->__('Default'),
				'value' => 'default'
			),
			array(
				'label' => Mage::helper('klarnaCheckout')->__('Two columns'),
				'value' => 'kco_twocolumns'
			)
		);

		return $options;
	}
}
