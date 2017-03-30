<?php

class Ves_ProductCarousel_Model_System_Config_Source_ListType
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('ves_productcarousel')->__('-- Please select --')),
            array('value'=>'latest', 'label'=>Mage::helper('ves_productcarousel')->__('Latest')),
            array('value'=>'bestvalue', 'label'=>Mage::helper('ves_productcarousel')->__('Best Value - Position')),
            array('value'=>'sale', 'label'=>Mage::helper('ves_productcarousel')->__('On Sales')),
            array('value'=>'best_buy', 'label'=>Mage::helper('ves_productcarousel')->__('Best Buy')),
            array('value'=>'most_viewed', 'label'=>Mage::helper('ves_productcarousel')->__('Most Viewed')),
            array('value'=>'top_rated', 'label'=>Mage::helper('ves_productcarousel')->__('Top Rated')),
            array('value'=>'featured', 'label'=>Mage::helper('ves_productcarousel')->__('Featured Product')),
            array('value'=>'attribute', 'label'=>Mage::helper('ves_productcarousel')->__('Product Attribute')),
            array('value'=>'new', 'label'=>Mage::helper('ves_productcarousel')->__('New Products')),
            array('value'=>'random', 'label'=>Mage::helper('ves_productcarousel')->__('Random Products'))
        );
    }    
}
