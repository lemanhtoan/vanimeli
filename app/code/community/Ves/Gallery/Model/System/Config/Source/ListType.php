<?php

class Ves_Gallery_Model_System_Config_Source_ListType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'', 'label'=>Mage::helper('ves_gallery')->__('-- Please select --')),
            array('value'=>'latest', 'label'=>Mage::helper('ves_gallery')->__('Latest')),
            array('value'=>'best_buy', 'label'=>Mage::helper('ves_gallery')->__('Best Buy')),
            array('value'=>'most_viewed', 'label'=>Mage::helper('ves_gallery')->__('Most Viewed')),
            array('value'=>'most_reviewed', 'label'=>Mage::helper('ves_gallery')->__('Most Reviewed')),
            array('value'=>'top_rated', 'label'=>Mage::helper('ves_gallery')->__('Top Rated')),
            array('value'=>'attribute', 'label'=>Mage::helper('ves_gallery')->__('Featured Product'))
        );
    }    
}
