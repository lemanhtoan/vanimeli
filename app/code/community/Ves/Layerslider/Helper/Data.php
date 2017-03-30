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
class Ves_Layerslider_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function checkAvaiable( $controller_name = ""){
		 $arr_controller = array(  "Mage_Cms",
									"Mage_Catalog",
									"Mage_Tag",
									"Mage_Checkout",
									"Mage_Customer",
									"Mage_Wishlist",
									"Mage_CatalogSearch",
									"Venustheme_Brand" );
		 if( !empty($controller_name)){
			if( in_array( $controller_name, $arr_controller ) ){
				return true;
			}
		 }
		 return false;
   }
   
    public function getShortcode( $alias = "", $placeholder = ""){
		return $alias?'{{widget type="ves_layerslider/widget_slider" slider_code="'.$alias.'" show="1"}}': $placeholder;
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
							 'enable_jquery',
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
			$tags = array('ves_layerslider', 'effect_setting', 'file_source_setting','image_source_setting', 'main_layerslider');
			foreach($tags as $tag){
				if(Mage::getStoreConfig("ves_layerslider/$tag/$var")!=""){
					$data[$var] =  Mage::getStoreConfig("ves_layerslider/$tag/$var");
				}
			}
			if(isset($attributes[$var]))
			{
				$data[$var] =  $attributes[$var];
			}	
		}

    	return $data;
	}

	public function getHotspot() {
		$hotspot_icon = Mage::getStoreConfig("ves_layerslider/general_setting/hotspot_icon");
		if($hotspot_icon) {
            $hotspot_icon = "ves_layerslider/".$hotspot_icon;
            if(file_exists(Mage::getBaseDir('media').DS.$hotspot_icon)) {
	            $hotspot_icon = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$hotspot_icon;
	        } else {
	            $hotspot_icon = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."ves_layerslider/hotspot.png";
	        }
        }

        if($hotspot_icon) {
        	return $hotspot_icon;
        } else {
        	return "http://venustheme.com/guides/hotspot.png";
        }
		
	}  
	public function getImageUrl($url = null) {
        $parsed = parse_url($url);
		if (empty($parsed['scheme'])) {
	        return Mage::getSingleton('ves_layerslider/config')->getBaseMediaUrl() . $url;
	    } else {
	    	return $url;
	    }
    }

    /**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	function getConfig( $key, $panel='general_setting' ) {
		return Mage::getStoreConfig("ves_layerslider/$panel/$key");
	}

	public function resizeImage( $image, $width, $height ){


		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}".DS.$image;
		$quality = $this->getConfig("resize_quality");
		if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->quality(100);
			$imageObj->constrainOnly(true);
		    $imageObj->keepAspectRatio(true);
		    $imageObj->keepFrame(false);
		    $imageObj->keepTransparency(true);
			$imageObj->resize( $width, $height);
			if($quality) {
		    	$imageObj->quality($quality);
		    }
			$imageObj->save($imageResized);
			
		}
		return 'resized/'."{$width}x{$height}/".str_replace(DS,"/",$image);

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
		
		$collection =  Mage::getModel('ves_layerslider/category')->getCollection();
		
		if( $collection ){
			foreach( $collection as $category ){
				$output[$category->getId()]=$category->getTitle();
			}
		}
		return $output;
	}

	public function getImage($image_src = "") {
		if($image_src) {
			$parsed = parse_url($image_src);
			if (empty($parsed['scheme'])) {
				$media_base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
				$image_src = str_replace($media_base_url, "", $image_src);
				$media_base_url = str_replace("https://","http://", $media_base_url);
				$image_src = str_replace($media_base_url, "", $image_src);

				if(strpos($image_src, "ves_layerslider/upload/") !== false) {
					$image_src = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$image_src;
				} else {
					$image_src = $this->getImageBaseUrl().$image_src;
				}
			}
		}
		return $image_src;
	}


	public function getSliderProduct( $sku = ""){
    	$product_info = array();
    	if($sku) {
          if( $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku)){
          		$product_info['name'] = $product->getName();
	          	$product_info['price'] = $product->getPrice();
	          	$product_info['special_price'] = $product->getSpecialPrice();
	          	$product_info['url'] = $product->getProductUrl();
	          	$product_info['image'] = $product->getImageUrl();
	          	$product_info['small_image'] = $product->getSmallImageUrl();
	          	$product_info['thumbnail'] = $product->getThumbnailUrl();
	          	$product_info['object'] = $product;
          }
        }
        return $product_info;
    }
	
	public function getImageBaseDir() {
        return Mage::getBaseDir('media')."/ves_layerslider/upload/";
    }

    public function getImageBaseUrl() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."ves_layerslider/upload/";
    }

    public function mb_unserialize($string) {
        $string = preg_replace_callback(
            '!s:(\d+):"(.*?)";!s',
            function ($matches) {
                if ( isset( $matches[2] ) )
                    return 's:'.strlen($matches[2]).':"'.$matches[2].'";';
            },
            $string
        );
        return unserialize($string);
    }
}
?>