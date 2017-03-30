<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */

class Ves_Base_Model_System_Config_ListCms
{	

    public function toOptionArray()
    {
		$collection = Mage::getModel('cms/block')->getCollection();
		$output = array();
		$output[] = array('value'=>0, 'label'=> Mage::helper('ves_base')->__("Use Custom Content") );
		foreach( $collection as $cms ){
			$output[] = array('value'=>$cms->getId(), 'label'=>$cms->getTitle() );
		}
        return $output ;
    }    
}
