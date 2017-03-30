<?php
class Ves_Base_Block_Widget_Customblock extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/base/customblock.phtml";
 		}
        $this->setTemplate($my_template);
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$widget_heading = $this->getConfig("title");
		$block_type = $this->getConfig("block_type");
		$block_type = trim($block_type);
		$block_name = $this->getConfig("block_name");
		$block_name = trim($block_name);
		$block_params = $this->getConfig("block_params");
		$block_params = trim($block_params);
		$block_params = base64_decode($block_params);
		$params = explode("\n", $block_params);
		if($params) {
			$tmp = array();
			foreach($params as $key=>$val) {
				$val = trim($val);
				if($val) {
					$tmp_array = explode("=", $val);
					if(isset($tmp_array[0])) {
						$tmp[trim($tmp_array[0])] = isset($tmp_array[1])?trim($tmp_array[1]):"";
					}
				}
			}
			$params = $tmp;
		}
		$block_html = "";
		if($block_type) {
			$block = Mage::app()->getLayout()->createBlock($block_type, $block_name, $params);
			$block_html = $block->toHtml();
		}

		$this->assign('widget_heading', $widget_heading);
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('block_html', $block_html);

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
}