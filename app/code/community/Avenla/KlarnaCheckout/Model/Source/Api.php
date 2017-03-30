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

class Avenla_KlarnaCheckout_Model_Source_Api
{
	public function toOptionArray()
	{
		$kco2Countries = "FI, SE, NO, DE, AT";

		return array(
			array(
				'label' => 'KCO v2 ('. $kco2Countries .')',
				'value' => Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV2
			),
			array(
				'label' => 'KCO v3 (UK)',
				'value' => Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV3_UK,
			),
			array(
				'label' => 'KCO v3 (US)',
				'value' => Avenla_KlarnaCheckout_Model_Config::API_TYPE_KCOV3_US,
			)
		);
	}
}
