<?php


class Ves_Gallery_Model_System_Config_Source_ListSource
{	
 
	
    public function toOptionArray()
    {
		 return array(
        	array('value' => "image", 'label'=>Mage::helper('adminhtml')->__('Images In Folder')),
            array('value' => "file", 'label'=>Mage::helper('adminhtml')->__('Gallery Banner Group'))
        ); 
    }    
}
