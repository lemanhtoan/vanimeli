<?php
class Ves_Base_Block_Widget_Newsletter extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$block_type = "newsletter/subscribe";
		$block_name = "vesnewsletter".rand().time();

		$params = array();
		if($this->hasData("template") && $this->getData("template")) {
			$my_template = $this->getData("template");
		}else{
			$my_template = "ves/base/newsletter.phtml";
		}
		$params["template"] = $my_template;

		$block_html = "";
		if($block_type) {
			$block = Mage::app()->getLayout()->createBlock($block_type, $block_name, $params);
			$block->setData("widget_heading", $this->getConfig("title"));
			$block->setData("signup_text", $this->getConfig("signup_text"));
			$block->setData("button_text", $this->getConfig("button_text"));
			$block->setData("addition_cls", $this->getConfig("addition_cls"));
			$block->setData("title_class", $this->getConfig("title_class"));
			$block->setData("button_class", $this->getConfig("button_class"));
			$block->setData("input_class", $this->getConfig("input_class"));
			$block->setTemplate($my_template);
			$block_html = $block->toHtml();
		}

		return $block_html;
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