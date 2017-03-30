<?php
class Ves_Gallery_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function checkAvaiable( $controller_name = ""){
		 $arr_controller = array(  "Mage_Cms",
											"Mage_Catalog",
											"Mage_Tag",
											"Mage_Checkout",
											"Mage_Customer",
											"Mage_Wishlist",
											"Mage_CatalogSearch");
		 if( !empty($controller_name)){
			if( in_array( $controller_name, $arr_controller ) ){
				return true;
			}
		 }
		 return false;
   }
	public function checkMenuItem( $menu_name = "", $config = array())
	{
		if(!empty( $menu_name) && !empty( $config)){
			$menus = isset($config["menuAssignment"])?$config["menuAssignment"]:"all";
			$menus = explode(",", $menus);
			if( in_array("all", $menus) || in_array( $menu_name, $menus) ){
				return true;
			}
		}
		return false;
	}
	public function getListMenu(){
		$arrayParams = array(
							 'all'=>Mage::helper('adminhtml')->__("All"),
							 'Mage_Cms_index'=>Mage::helper('adminhtml')->__("Mage Cms Index"),
							 'Mage_Cms_page'=>Mage::helper('adminhtml')->__("Mage Cms Page"),
							 'Mage_Catalog_category'=>Mage::helper('adminhtml')->__("Mage Catalog Category"),
							 'Mage_Catalog_product'=>Mage::helper('adminhtml')->__("Mage Catalog Product"),
							 'Mage_Customer_account'=>Mage::helper('adminhtml')->__("Mage Customer Account"),
							 'Mage_Wishlist_index'=>Mage::helper('adminhtml')->__("Mage Wishlist Index"),
							 'Mage_Customer_address'=>Mage::helper('adminhtml')->__("Mage Customer Address"),
							 'Mage_Checkout_cart'=>Mage::helper('adminhtml')->__("Mage Checkout Cart"),
							 'Mage_Checkout_onepage'=>Mage::helper('adminhtml')->__("Mage Checkout"),
							 'Mage_CatalogSearch_result'=>Mage::helper('adminhtml')->__("Mage Catalog Search"),
							 'Mage_Tag_product'=>Mage::helper('adminhtml')->__("Mage Tag Product")
							 );
		return $arrayParams;
	}
	function get( $attributes = NULL)
	{
		$data = array();
		$arrayParams = array('show', 
							 'name',
		                     'title',
							  'mage_folder',							 
		                     'theme', 
		                     'moduleHeight', 
		                     'moduleWidth', 
		                     'thumbnailMode',
							 'thumbWidth', 
		                     'thumbHeight',
							 'blockPosition', 
							 'customBlockPosition', 
							 'blockDisplay', 
							 'menuAssignment',
							 'source',
							'image_folder',
							 'catsid', 
							 'sourceProductsMode',
							 'quanlity', 
							 
							 'imagecategory', 
							 
							 'titleMaxchar',
							 'descMaxchar',
							 'slide_width',
							 'slide_height',
							 'auto_play',
							 'scroll_items',
							 'limit_cols',
							 'show_button',
							 'show_pager',
							 'show_title',
							 'show_desc',
							 'show_price',
							 'show_pricewithout',
							 'show_date',
							 'show_addcart',
							 'duration',
							 
		);
		
	
		foreach ($arrayParams as $var)
		{	    	
			$tags = array('ves_gallery', 'effect_setting', 'file_source_setting','image_source_setting', 'main_gallery');
			foreach($tags as $tag){
				if(Mage::getStoreConfig("ves_gallery/$tag/$var")!=""){
					$data[$var] =  Mage::getStoreConfig("ves_gallery/$tag/$var");
				}
			}
			if(isset($attributes[$var]))
			{
				$data[$var] =  $attributes[$var];
			}	
		}

    	return $data;
	}	  
	    public function getImageUrl($url = null) {
        return Mage::getSingleton('ves_gallery/config')->getBaseMediaUrl() . $url;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }
}
?>