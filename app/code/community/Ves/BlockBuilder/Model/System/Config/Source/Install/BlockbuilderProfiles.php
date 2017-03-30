<?php

class Ves_BlockBuilder_Model_System_Config_Source_Install_BlockbuilderProfiles
{
    public function toOptionArray()
    {

		$this->_options  = array( array("value"=>"0", "label"=>"-- Select A Profile --") );
        $collection = Mage::getModel( "ves_blockbuilder/block" )->getCollection()
        							->addFieldToFilter("block_type",
                                                            array(
                                                                array('null' => true),

                                                                array("neq" => "page")
                                                            ));
		
		foreach( $collection as $banner ){
			$this->_options[] = array("value"=>$banner->getId(), "label"=>$banner->getTitle() ); 
		}			
        
        return $this->_options;
    }
}