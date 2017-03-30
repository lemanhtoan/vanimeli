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

class Avenla_KlarnaCheckout_Model_Source_Pplayout
{
	public function toOptionArray()
	{
		$options = array();
		$layouts  = array(
			'Pale',
			'Dark',
			'Deep',
			'Deep-extra'
		);

		foreach($layouts as $layout){
			$options[] = array(
				'label' => $layout,
				'value' => strtolower($layout)
			);
		}

		return $options;
	}
}