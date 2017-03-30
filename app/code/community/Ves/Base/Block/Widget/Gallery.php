<?php
class Ves_Base_Block_Widget_Gallery extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface
{
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
	 	parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        } elseif($this->hasData("custom_template")) {
        	$my_template = $this->getData("custom_template");
        } elseif(isset($attributes["template"]) && $attributes["template"]) { 
        	$my_template = $attributes["template"];
        }else{
 			$my_template = "ves/base/gallery_list.phtml";
 		}

        $this->setTemplate($my_template);
	}

	public function _toHtml() {
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$widget_heading = $this->getConfig("title");
		
		$galleries = array();
		$limit = $this->getConfig("limit_item", 10);
		$keep_ratio = $this->getConfig("keep_ratio", 1);

		if($this->getConfig("source") == "folder") { //If source gallery is folder images

			$folder = $this->getConfig("image_folder","gallery/upload");
			$path = str_replace( DS.DS,DS, Mage::getBaseDir('media') . DS . str_replace("/",DS, $folder ));		
			$files = array();
			
			if( is_dir($path) ){ 
				$files = $this->dirFiles( $path );
			}
			$mediaURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
			//$this->thumbdir = 
			$count = 1;
			if( $files ){
				foreach( $files as $file ){
					if($count <= $limit) {
						$tmp 					= array();
						$tmp['title'] 			= $file;
						$tmp['imageURL'] 		= $mediaURL. str_replace(DS,"/",$folder)."/".$file;
						$tmp['thumbnailURL'] 	= $mediaURL.$this->resizeImage( $folder.DS.$file, $this->getConfig("thumb_width",200), $this->getConfig("thumb_height",200), $keep_ratio );
						$tmp['description'] 	= "";
						$tmp['link'] 			= "";

						if(!$this->getConfig('popup') && $tmp['link']) {
				        	$tmp['imageURL'] = $tmp['link'];
				        }

						$galleries[] 			= $tmp;
					} else {
						break;
					}
					$count++;
				}
			}

		} else { //If source gallery is file banner

	 		for($i=1; $i<=$limit; $i++) {
				$tmp = array();
				$tmp['link'] = $this->getConfig("link_".$i);
				$tmp['title'] = $this->getConfig("title_".$i);
				$tmp['title'] = trim($tmp['title']);
				$tmp['product_id'] = $this->getConfig("product_id_".$i);
				$image_file = $this->getConfig("image_".$i);
				$imageurl = "";

				if($image_file) {
					if(!preg_match("/^http\:\/\/|https\:\/\//", $image_file)) {
			            $imageurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $image_file;
			        }

					$thumbnailurl = "";
					if ($image_file && !preg_match("/^http\:\/\/|https\:\/\//", $image_file)) {
			            $thumbnailurl = Mage::helper("ves_base")->resizeImage($image_file, (int)$this->getConfig("thumb_width",200), (int)$this->getConfig("thumb_height",200), 100, $keep_ratio);
			        } else {
			        	$thumbnailurl = $imageurl = $image_file;
			        }
			        /*Use holder image*/
			        if ($image_file && preg_match("/^holder.js/", $image_file)) {
			        	$thumbnailurl = $imageurl = $image_file;
			        }
			        $tmp['imageURL'] = $imageurl;
			        if(!$this->getConfig('popup') && $tmp['link']) {
			        	$tmp['imageURL'] = $tmp['link'];
			        }
			        $tmp['thumbnailURL'] 	= $thumbnailurl;
			        $tmp['products']	 	= array();
			        $tmp['description']		= "";
			        if($tmp['product_id']) {
			        	$arr = explode(',', $tmp['product_id']);
						if($arr){
							$tmp['products'] = $this->getListProducts($arr);//Get collection products by ids
						}
			        }

					$galleries[] = $tmp;
				}
			}

	 	}

	 	$this->assign('widget_heading', $widget_heading);
		$this->assign('images', $galleries);
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('ispopup',$this->getConfig('popup'));
		$this->assign('popup_plugin',$this->getConfig('popup_plugin', "colorbox"));
		$this->assign('enable_thumb',$this->getConfig('enable_thumb'));
		$this->assign('use_custom_button',$this->getConfig('use_custom_button'));
		$this->assign('popup_thumb_width',$this->getConfig('popup_thumb_width', 50));
		$this->assign('popup_thumb_height',$this->getConfig('popup_thumb_height', 50));
		
        return parent::_toHtml();
	}
	
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = ""){
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {

	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    }
	    return $default;
	}

	public function resizeImage( $image, $width, $height, $keep_ratio = true ){
		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}".DS.$image;
	
		if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->quality(100);
			$imageObj->constrainOnly(TRUE);
			$imageObj->keepFrame(FALSE);

			if($keep_ratio) {

				$imageObj->keepAspectRatio(TRUE);
				$imageObj->resize( $width );
			} else {
				$imageObj->keepAspectRatio(FALSE);
				$currentRatio = $imageObj->getOriginalWidth() / $imageObj->getOriginalHeight();
				$targetRatio = $width / $height;
				if ($targetRatio > $currentRatio) {
		            $imageObj->resize($width, null);
		        } else {
		            $imageObj->resize(null, $height);
		        }

		        $diffWidth  = $imageObj->getOriginalWidth() - $width;
		        $diffHeight = $imageObj->getOriginalHeight() - $height;

		        /*POSTION Bottom*/
	              $_topRate = 1;
	              $_bottomRate = 0;
	              /*
	              //POSTION Top
	              $_topRate = 0;
	              $_bottomRate = 1;
	              */
	              /*
	              //POSTION Center
	              $_topRate = 0.5;
	              $_bottomRate = 0.5;
	              */

		        $imageObj->crop(
		            floor($diffHeight * $_topRate),
		            floor($diffWidth / 2),
		            ceil($diffWidth / 2),
		            ceil($diffHeight * $_bottomRate)
		        );
			}
			
			$imageObj->save($imageResized);
			
		}
		return 'resized/'."{$width}x{$height}/".str_replace(DS,"/",$image);
	}

	
	function dirFiles($directry) {
		$dir = dir($directry);
		$filesall = array();
		while (false!== ($file = $dir->read())) 
		{
			$extension = substr($file, strrpos($file, '.')); 
			if($extension == ".png" || $extension == ".gif" || $extension == ".jpg" |$extension == ".jpeg") 
			$filesall[$file] = $file; 
		}
		$dir->close(); // Close Directory
		asort($filesall); // Sorts the Array
		return $filesall;
	}

	public function getListProducts( $productIds = array()) {

		$products   = $this->getCollectionPro()
								->addIdFilter($productIds)
						        ->addAttributeToSort('updated_at', 'desc')
							    ->addMinimalPrice()
							    ->addFinalPrice()
							    ->addStoreFilter($storeId)
							    ->addUrlRewrite()
							    ->addTaxPercents()
							    ->groupByAttribute('entity_id');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $this->_addProductAttributesAndPrices($products);
        return $products->getItems();
	}
	public function getCollectionPro($model_type = 'catalog/product_collection')
    {
	    $storeId = Mage::app()->getStore()->getId();        
	    $productFlatTable = Mage::getResourceSingleton('catalog/product_flat')->getFlatTableName($storeId);
	    $attributesToSelect = array('name','entity_id','price', 'small_image','short_description');
	    try{
	        /**
	        * init resource singleton collection
	        */
	        $products = Mage::getResourceModel($model_type);//Mage::getResourceSingleton('reports/product_collection');
	        if(Mage::helper('catalog/product_flat')->isEnabled()){
	          $products->joinTable(array('flat_table'=>$productFlatTable),'entity_id=entity_id', $attributesToSelect);
	        }else{
	          $products->addAttributeToSelect($attributesToSelect);
	        }
	        $products->addStoreFilter($storeId);
	        return $products;
	    }catch (Exception $e){
	        Mage::logException($e->getMessage());
	    }
    }
}