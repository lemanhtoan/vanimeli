<?php
class Venustheme_ProductCarousel_Model_Config_Source_Type
{
    const IMAGE       = 'image';
    const PRODUCT    = 'product';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::IMAGE, 'label'=>Mage::helper('adminhtml')->__('Image')),
            array('value' => self::PRODUCT, 'label'=>Mage::helper('adminhtml')->__('Product'))
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toGridOptionArray()
    {
        return array(
            self::IMAGE => Mage::helper('adminhtml')->__('Image'),
            self::PRODUCT => Mage::helper('adminhtml')->__('Product')
        );
    }
}
