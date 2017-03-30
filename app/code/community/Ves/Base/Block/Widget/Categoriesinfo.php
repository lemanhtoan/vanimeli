<?php
class Ves_Base_Block_Widget_Categoriesinfo extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}else{
			$my_template = "ves/base/categories_info.phtml";
		}
		$this->setTemplate($my_template);
	}

	public function subString( $text, $length = 100, $replacer = '...', $is_striped=true ){
		$text = ($is_striped==true)?strip_tags($text):$text;
		if(strlen($text) <= $length){
			return $text;
		}
		$text = substr($text,0,$length);
		$pos_space = strrpos($text,' ');
		return substr($text,0,$pos_space).$replacer;
	}

	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		$cms = "";

 		$cms_block_id = $this->getConfig('cmsblock');
 		if($cms_block_id){
 			$cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
 		}

 		$this->assign('cms', $cms);
		$this->assign('widget_heading', $this->getConfig('title'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('resize_image', $this->getConfig("resize_image"));
		$this->assign('image_width', $this->getConfig("image_width"));
		$this->assign('image_height', $this->getConfig("image_height"));
		$this->assign('catsid', $this->getConfig("catsid"));
		$this->assign('show_title', $this->getConfig("show_title"));
		$this->assign('show_description', $this->getConfig("show_description"));
		$this->assign('limit_description', (int)$this->getConfig("limit_description"));
		$this->assign('show_sub_category', $this->getConfig("show_sub_category"));
		$this->assign('limit_subcategory', (int)$this->getConfig("limit_subcategory"));
		$this->assign('show_number_product', $this->getConfig("show_number_product"));
		$this->assign('show_image', $this->getConfig("show_image"));
		$this->assign('limit', (int)$this->getConfig("limit"));
		$this->assign('columns', (int)$this->getConfig("columns"));
		$this->assign('show_viewall', $this->getConfig("show_viewall"));

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