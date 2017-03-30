<?php

class NextBits_Wholesale_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public function getRegisterUrl()
    {
        return $this->_getUrl('wholesale/account/create');
    }

}