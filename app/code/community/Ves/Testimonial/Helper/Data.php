<?php
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function checkAvaiable( $controller_name = ""){
		 $arr_controller = array(  "Mage_Cms",
									"Mage_Catalog",
									"Mage_Tag",
									"Mage_Checkout",
									"Mage_Customer",
									"Mage_Wishlist",
									"Mage_CatalogSearch",
									"Ves_Testimonial" );
		 if( !empty($controller_name)){
			if( in_array( $controller_name, $arr_controller ) ){
				return true;
			}
		 }
		 return false;
   }
   
    public function getBrandPerPage(){
		return 6;
	
    }
	public function checkMenuItem( $menu_name = "", $menuAssignment)
	{
		if(!empty( $menu_name)  ){
			$menus = isset( $menuAssignment ) ? $menuAssignment : "all";
			$menus = explode(",", $menus);
			if( in_array("all", $menus) || in_array( $menu_name, $menus) ){
				return true;
			}
		}
		return false;
	}
	
	function get( $attributes = NULL)
	{
		$data = array();
		$arrayParams = array('show', 
							 'autoplay',
							 'interval',
		                     'title',
							 'width',							 
		                     'height', 
		                     'columns', 
		                     'max_items_page', 
		                     'filter_group'
		);
		
	
		foreach ($arrayParams as $var)
		{	    	
			$tags = array('ves_testimonial', 'general_setting');
			foreach($tags as $tag){
				if(Mage::getStoreConfig("ves_testimonial/$tag/$var")!=""){
					$data[$var] =  Mage::getStoreConfig("ves_testimonial/$tag/$var");
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
        return Mage::getSingleton('ves_testimonial/config')->getBaseMediaUrl() . $url;
    }


	public function resize( $image, $width, $height ){


		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}".DS.$image;

		if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->constrainOnly(true);
		    $imageObj->keepAspectRatio(true);
		    $imageObj->keepFrame(false);
		    $imageObj->keepTransparency(true);
			$imageObj->resize( $width, $height);
			$imageObj->save($imageResized);
			
		}
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'resized/'."{$width}x{$height}/".str_replace(DS,"/",$image);
	}

	public function resizeImage( $image,$type="l", $width, $height ){
		 
		$imageProcessor =  Mage::helper('ves_testimonial/vesimage');
		$imageProcessor->setStoredFolder("resized");
		return  $imageProcessor->resize( "media/".$image,$width, $height );
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
	
	 /*
     * Recursively searches and replaces all occurrences of search in subject values replaced with the given replace value
     * @param string $search The value being searched for
     * @param string $replace The replacement value
     * @param array $subject Subject for being searched and replaced on
     * @return array Array with processed values
     */
    public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }
	
	public function getCategoriesList( $default=true ){
		$output = array();
		if( $default ){
			$output[0] = $this->__("Select A Category"); 
		}
		
		$collection =  Mage::getModel('ves_testimonial/category')->getCollection();
		
		if( $collection ){
			foreach( $collection as $category ){
				$output[$category->getId()]=$category->getTitle();
			}
		}
		return $output;
	}
	
	public function getTagUrl($tag){
		return Mage::getBaseUrl().Mage::getStoreConfig('ves_testimonial/general_setting/route')."/tag/".$tag.".html";
	}
	
	public function getPostURL( $id ){
		return Mage::getBaseUrl().Mage::getModel('core/url_rewrite')->loadByIdPath('venustestimonial/post/'.$id)->getRequestPath();
	}
	
	public function gettestimonialById( $id ){
		return Mage::getModel("ves_testimonial/testimonial")->load( $id ) ;
	}
}
?>