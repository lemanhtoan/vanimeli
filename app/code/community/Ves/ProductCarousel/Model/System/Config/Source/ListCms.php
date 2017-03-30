<?php


class Ves_ProductCarousel_Model_System_Config_Source_ListCms
{	


    public function toOptionArray()
    {
		$collection = Mage::getModel('cms/block')->getCollection();
		$output = array();
		$output[] = array('value'=>0, 'label'=> Mage::helper('ves_productcarousel')->__("Use Pretext") );
		foreach( $collection as $cms ){
			$output[] = array('value'=>$cms->getId(), 'label'=>$cms->getTitle() );
		}
        return $output ;
    }    
}
