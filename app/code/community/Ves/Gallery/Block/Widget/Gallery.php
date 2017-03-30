<?php
class Ves_Gallery_Block_Widget_Gallery extends Ves_Gallery_Block_List implements Mage_Widget_Block_Interface
{
	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
	 	parent::__construct($attributes);
		
	}

	public function _toHtml() {
		$this->_show = $this->getConfig("show");

		if(!$this->_show) return;
		if(isset($this->_config) && $this->_config) {
			foreach($this->_config as $key=>$val) {
				if($this->hasData($key)) {
					$this->setConfig($key, $this->getData($key));
				}
			}
		}

		//Convert widget config
		$layout_mode = $this->getConfig("layout_mode", "default");
		$is_crop = $this->getConfig("crop_image", 0);

		$module_height = $this->getData("module_height");
		if($module_height) {
			$this->setConfig("moduleHeight", $module_height);
		}
		
		$module_width = $this->getData("module_width");
		if($module_width) {
			$this->setConfig("moduleWidth", $module_width);
		}
		$thumbnail_mode = $this->getData("thumbnail_mode");
		if($thumbnail_mode) {
			$this->setConfig("thumbnailMode", $thumbnail_mode);
		}
		$thumb_height = $this->getData("thumb_height");
		if($thumb_height) {
			$this->setConfig("thumbHeight", $thumb_height);
		}

		$thumb_width = $this->getData("thumb_width");
		if($thumb_width) {
			$this->setConfig("thumbWidth", $thumb_width);
		}
        

	 	if($this->getConfig("source") == "image") { //If source gallery is folder images

	 		$folder = $this->getConfig("image_folder","gallery/upload");
			$path = str_replace( DS.DS,DS, Mage::getBaseDir('media') . DS . str_replace("/",DS, $folder ));		
			$files = array();
			
			
			if( is_dir($path) ){ 
				$files = $this->dirFiles( $path );
			}
			$mediaURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
			//$this->thumbdir = 
			$output = array();
			if( $files ){
				foreach( $files as $file ){
					$tmp 									= array();
					$tmp['title'] 				= $file;
					$tmp['imageURL'] 			= $mediaURL. str_replace(DS,"/",$folder)."/".$file;
					$tmp['thumbnailURL'] 	= $mediaURL.$this->resizeImage( $folder.DS.$file, $this->getConfig("thumbWidth",200), $this->getConfig("thumbHeight",200), $is_crop );
					$tmp['description'] 	= "";
					$tmp['classes'] 		= "";
					$tmp['params']			= array();
					$output[] = $tmp;
				}
			}
			
			$this->setImages($output); 

			if($this->hasData("template")) {
		 		$this->setTemplate($this->getData("template"));	
		 	} else {
		 		if($layout_mode != "default" && $layout_mode) {
		 			$this->_config['template'] = 'ves/gallery/image_layout_'.$layout_mode.'.phtml';
		 		} else {
		 			$this->_config['template'] = 'ves/gallery/image.phtml';
		 		}
		 		
				$this->setTemplate($this->_config['template']);
		 	}
	 	} else { //If source gallery is file banner

	 		$this->__renderSlideShowImagegroup();

	 		if($this->hasData("template")) {
		 		$this->setTemplate($this->getData("template"));	
		 	} else {
		 		if($layout_mode != "default" && $layout_mode) {
		 			$this->_config['template'] = 'ves/gallery/file_layout_'.$layout_mode.'.phtml';
		 		} else {
		 			$this->_config['template'] = 'ves/gallery/file.phtml';
		 		}
				$this->setTemplate($this->_config['template']);
		 	}

	 	}
        return parent::_toHtml();
	}

	/**
	 * render block content for the slideshow using the list of products.
	 */
	private function __renderSlideShowImagegroup() 
	{
		$is_crop = $this->getConfig("crop_image", 0);
		$_model = Mage::getModel('ves_gallery/banner');
		$theme 	= ($this->getConfig('theme')!="") ? $this->getConfig('theme') : "default";
		$categories = $this->getConfig('imagecategory');
		$categories = !is_array($categories)?explode(",", $categories):$categories;
		$list 	= $_model->getCollection()
						->addFieldToFilter('label', array("in" => $categories))
						->addFieldToFilter('is_active', 1)
						->setOrder('position', 'asc')
						->setPageSize( $this->getSourceConfig('limit_item'));
						
		 
		$items 			= array();
		$maxTitle 	= $this->getConfig('titleMaxchar',15);
		$maxDesc 		= $this->getConfig('descMaxchar',200);
		$replacer 	= '...';		
		$isStriped 	= 1;
		$date 			= date("Y-m-d H:i:s");
		$today 			= strtotime($date);
		$mediaURL 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		$thumb_width = $this->getConfig("thumbWidth",200);
		$thumb_height = $this->getConfig("thumbHeight",200);

		foreach($list as $item){			
			$tmp = array();
			$img_thumb_width = $thumb_width;
			$img_thumb_height = $thumb_height;
			$params = $item->getExtra();
			$params = $params?unserialize($params):array();

			if(isset($item['thumb_width'])) {
				$img_thumb_width = (int)$item['thumb_width'];
			} elseif(isset($params['thumb_width']) && $params['thumb_width']) {
				$img_thumb_width = (int)$params['thumb_width'];
			}

			if(isset($item['thumb_width'])) {
				$img_thumb_height = (int)$item['thumb_height'];
			} elseif(isset($params['thumb_height']) && $params['thumb_height']) {
				$img_thumb_height = (int)$params['thumb_height'];
			}

			$tmp['imageURL'] 			= $mediaURL. str_replace( DS, "/", $item->getFile() );
			$crop_mode			= $item->getCropMode();
			$tmp['thumbnailURL'] 	= $mediaURL.$this->resizeImage( $item->getFile(), $img_thumb_width, $img_thumb_height, $is_crop, $crop_mode );
			$tmp['title'] 				= $item->getTitle();
			$tmp['links'] 				= $item->getLinks();
			$tmp['classes'] 			= $item->getClasses();
			$tmp['description'] 	= $item->getDescription();
			$tmp['params']		= $params;
			$items[] = $tmp;
		}
		$this->setImages($items);

	}
	
	

	public function resizeImage( $image, $width, $height, $is_crop = false, $crop_mode = 'bottom' ){
		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}-".(int)$is_crop.DS.$image;
	
		if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->quality(100);
			$imageObj->constrainOnly(TRUE);
			$imageObj->keepFrame(FALSE);

			if($is_crop) {
				$imageObj->keepAspectRatio(false);

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
		        switch ($crop_mode) {
		        	case 'top':
		        		$_topRate = 0;
                  		$_bottomRate = 1;
		        		break;
		        	case 'bottom':
		        		$_topRate = 1;
                  		$_bottomRate = 0;
                  		break;
		        	default:
		        		$_topRate = 0.5;
                  		$_bottomRate = 0.5;
		        		break;
		        }

		        $imageObj->crop(
		            floor($diffHeight * $_topRate),
		            floor($diffWidth / 2),
		            ceil($diffWidth / 2),
		            ceil($diffHeight * $_bottomRate)
		        );
			} else {
				$imageObj->keepAspectRatio(TRUE);
				$imageObj->resize( $width );	
			}
			
			$imageObj->save($imageResized);
			
		}

		return 'resized/'."{$width}x{$height}-".(int)$is_crop."/".str_replace(DS,"/",$image);
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
	
}