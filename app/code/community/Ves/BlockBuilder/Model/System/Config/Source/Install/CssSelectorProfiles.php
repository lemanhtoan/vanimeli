<?php

class Ves_BlockBuilder_Model_System_Config_Source_Install_CssSelectorProfiles
{
    public function toOptionArray()
    {

		$this->_options  = array( array("value"=>"0", "label"=>"-- Select A Profile --") );
        $collection = Mage::getModel( "ves_blockbuilder/selector" )->getCollection();
		
		foreach( $collection as $banner ){
            $label = $banner->getElementGroup()." > ".$banner->getElementType()." > ".$banner->getElementName();
			$this->_options[] = array("value"=>$banner->getId(), "label"=>$label ); 
		}			
        
        return $this->_options;
    }
}