<?php
class Ves_Widgets_Block_Widget_Singleproduct extends Mage_Catalog_Block_Product_View implements Mage_Widget_Block_Interface{

	var $_product = null;

	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );

		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		} elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		} else{
			$my_template = "ves/widgets/single_product.phtml";
		}
		$this->setTemplate($my_template);
		
	}

	public function _toHtml(){
		return parent::_toHtml();
	}

	public function getProduct(){
		if(!$this->_product) {
			$id_path = $this->getData('id_path');
			$arr = explode('/', $id_path);
			$product_id = end($arr);
			if($product_id){
				$this->_product = Mage::getModel('catalog/product')->load($product_id);
			}
		}
		return $this->_product;
	}

	public function setProduct($_product = null) {
		if(is_numeric($_product)){
			$this->_product = Mage::getModel('catalog/product')->load((int)$_product);
		} else {
			$this->_product = $_product;
		}
	}

	protected function _prepareLayout()
	{
		$this->getLayout()->getBlock('head')->addJs('ves/widgets/countdown.js');
		return parent::_prepareLayout();
	}

	public function getProductImage($_product, $image_index = 0, $image_width = 200, $image_height = 200){
		$collection = $_product->getMediaGalleryImages();
		if ( count($collection) > 0) {
			$image = null;
			$i = 0;
			foreach($collection as $_image){
				if($i == $image_index){
					$image = $_image;
					break;
				}
				$i++;
			}
			if($image){

				return (string)Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($image_width, $image_height);
			}

		}
		return;
	}

	public function checkProductIsNew( $_product = null ) {
        $from_date = $_product->getNewsFromDate();
        $to_date = $_product->getNewsToDate();
        $is_new = false;
        $is_new = $this->isNewProduct( $from_date, $to_date);
        $today = strtotime("now");

        if($from_date && $to_date) {
            $from_date = strtotime($from_date);
            $to_date = strtotime($to_date);
            if($from_date <= $today && $to_date >= $today) {
                $is_new =true;
            }
        } elseif( $from_date && !$to_date) {
            $from_date = strtotime($from_date);
            if($from_date <= $today) {
                $is_new =true;
            }
        } elseif( !$from_date && $to_date) {
            $to_date = strtotime($to_date);
            if($to_date >= $today) {
                $is_new =true;
            }
        }
        return $is_new;
    }
    
    public function isNewProduct( $created_date, $num_days_new = 3) {
        $check = false;

        $startTimeStamp = strtotime($created_date);
        $endTimeStamp = strtotime("now");

        $timeDiff = abs($endTimeStamp - $startTimeStamp);

        $numberDays = $timeDiff/86400;  // 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
            $check = true;
        }

        return $check;
    }


    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
            $text = ($is_striped==true)?strip_tags($text):$text;
            if(strlen($text) <= $length){
                return $text;
            }
            $text = substr($text,0,$length);
            $pos_space = strrpos($text,' ');
            return substr($text,0,$pos_space).$replacer;
    }

    public function checkModuleInstalled( $module_name = "") {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        if($modulesArray) {
            $tmp = array();
            foreach($modulesArray as $key=>$value) {
                $tmp[$key] = $value;
            }
            $modulesArray = $tmp;
        }

        if(isset($modulesArray[$module_name])) {

            if((string)$modulesArray[$module_name]->active == "true") {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }


}