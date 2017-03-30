<?php

class Ves_BlockBuilder_Model_System_Config_Source_ListCssProfiles
{
    public function toOptionArray()
    {

     	$storeId = $this->getCurrentStoreId();
     	$defaultStoreId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();

     	//Get current store Id if = 0 will use default store id
     	$storeId = $storeId?$storeId:$defaultStoreId;

     	$package_name = Mage::getStoreConfig('design/package/name', $storeId);
     	$design_layout = Mage::getStoreConfig('design/theme/default', $storeId);
     	$design_skin = Mage::getStoreConfig('design/theme/skin', $storeId);

     	if(!$package_name) {
     		$package_name = "base";
     	}
     	if(!$design_layout) {
     		$design_layout = "default";
     	}
     	if(!$design_skin) {
     		$design_skin = $design_layout;
     	}

     	$default_theme = $package_name."/".$design_skin;

     	$themePath = Mage::getBaseDir('skin')."/frontend/".$default_theme."/" ;

     	$themeCustomizePath = $themePath.'css/customize/';

		$files = Mage::helper('ves_blockbuilder')->getFileList( $themeCustomizePath , '.css' );

		$options  = array( array("value"=>"0", "label"=> Mage::helper('ves_blockbuilder')->__("-- Select A Profile --") ) );

      	if($files) { 
            foreach( $files as $file )  { 
            	$file = str_replace(".css", "", $file);
            	$options[] = array("value"=>$file, "label"=>$file );
            }
        }
		
        return $options;
    }

    public function getCurrentStoreId() {
    	if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
		{
		    $store_id = Mage::getModel('core/store')->load($code)->getId();
		}
		elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
		{
		    $website_id = Mage::getModel('core/website')->load($code)->getId();
		    $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
		}
		else // default level
		{
		    $store_id = 0;
		}
		return $store_id;
    }
}