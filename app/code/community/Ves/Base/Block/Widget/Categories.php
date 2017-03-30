<?php
class Ves_Base_Block_Widget_Categories extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

        if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/base/categories.phtml";
 		}
        $this->setTemplate($my_template);
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$widget_heading = $this->getConfig("title");
		$cms = "";

 		$cms_block_id = $this->getConfig('cmsblock');
 		if($cms_block_id){
 			$cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
 		}

		$catsid = $this->getConfig("catsid");

		$this->assign('cms', $cms);
		$this->assign('catsid', $catsid);
		$this->assign('autoplay', $this->getConfig("autoplay"));
		$this->assign('interval', $this->getConfig("interval"));
		$this->assign('image_width', $this->getConfig("image_width"));
		$this->assign('image_height', $this->getConfig("image_height"));
		$this->assign('enable_numbproduct', $this->getConfig("enable_numbproduct"));
		$this->assign('enable_carousel', $this->getConfig("enable_carousel"));
		$this->assign('itemsperpage', $this->getConfig("page_limit"));
		$this->assign('show_navigator', $this->getConfig("show_navigator"));
		$this->assign('cols', $this->getConfig("cols"));
		$this->assign('cate_image', $this->getConfig("cate_image"));
		$this->assign('enable_image', $this->getConfig("enable_image"));
		$this->assign('widget_heading', $widget_heading);
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('animation', $this->getConfig('animation'));

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
	      if($key == "pretext") {
		      $value = base64_decode($value);
		   }
	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    }
	    return $default;
	}
	public function getCategoryImage($category = null, $width = 300, $height = 300, $image_type = "thumbnail")
	{
		if(empty($category) && !is_object($category)) return "";

		if($image_type == "thumbnail") {
			$_file_name = $category->getThumbnail();
		} else {
			$_file_name = $category->getImage();
		}
		
		$_media_dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;
		$cache_dir = $_media_dir . 'cache' . DS;

		if($_file_name) {
			if (file_exists($cache_dir . $_file_name)) {
				return Mage::getBaseUrl('media') .'/catalog/category/cache/' . $_file_name;
			} elseif (file_exists($_media_dir . $_file_name)) {
				if (!is_dir($cache_dir)) {
					mkdir($cache_dir);
				}

				$_image = new Varien_Image($_media_dir . $_file_name);
				$_image->constrainOnly(true);
				$_image->keepAspectRatio(true);
				$_image->keepTransparency(true);
				$_image->resize((int)$width, (int)$height);
				$_image->save($cache_dir . $_file_name);

				return Mage::getBaseUrl('media') . '/catalog/category/cache/'. $_file_name;
			}
		}
		return "";
	} 
}