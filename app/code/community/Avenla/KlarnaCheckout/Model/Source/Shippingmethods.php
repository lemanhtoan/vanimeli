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

class Avenla_KlarnaCheckout_Model_Source_Shippingmethods
{
	public function toOptionArray()
	{
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		$options = array();

		foreach($methods as $_code => $_method){
			if ($methods = $_method->getAllowedMethods()){
				foreach ($methods as $_mcode => $_mname){
					$code = $_code . '_' . $_mcode;
					$title = $_mname;

					if(!$title = Mage::getStoreConfig("carriers/$_code/title"))
						$title = $_code;

					$options[] = array(
						'value' => $code,
						'label' => $title
					);
				}
			}
		}

		return $options;
	}
}