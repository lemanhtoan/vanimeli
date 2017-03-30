<?php

class Ves_Layerslider_Model_System_Config_Source_ListBanner
{
    public function toOptionArray()
    {

		$this->_options  = array( array("value"=>"0", "label"=>"-- Select A Slider Banner --") );
        $collection = Mage::getModel( "ves_layerslider/banner" )->getCollection();
		
		foreach( $collection as $banner ){
			$this->_options[] = array("value"=>$banner->getId(), "label"=>$banner->getTitle() ); 
		}			
        
        return $this->_options;
    }
}