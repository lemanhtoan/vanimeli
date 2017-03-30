<?php

class NextBits_HidePrice_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    public function testIsExtensionActive()
    {
        $this->assertTrue(
            Mage::helper('hideprice')->isExtensionActive(),
            'Extension is not active please check config'
        );
    }
}