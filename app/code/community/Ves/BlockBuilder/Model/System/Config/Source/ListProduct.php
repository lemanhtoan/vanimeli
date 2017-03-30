<?php

class Ves_BlockBuilder_Model_System_Config_Source_ListProduct
{
    public function toOptionArray()
    {

		$this->_options  = array( array("value"=>"0", "label"=>"-- Select A Profile --") );
        $collection = Mage::getModel( "ves_blockbuilder/block" )->getCollection()
        							->addFieldToFilter("block_type", "product");
		
		foreach( $collection as $banner ){
			$this->_options[] = array("value"=>$banner->getId(), "label"=>$banner->getTitle() ); 
		}			
        
        return $this->_options;
    }
}