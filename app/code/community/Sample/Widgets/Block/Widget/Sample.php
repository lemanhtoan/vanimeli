<?php
class Sample_Widgets_Block_Widget_Sample extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		} elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		}else{
			$my_template = "sample/widgets/sample1.phtml";
		}
		$this->setTemplate($my_template);
	}

	public function _toHtml(){
		return parent::_toHtml();
	}

	/**
	 * get value of the extension/widget's configuration
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