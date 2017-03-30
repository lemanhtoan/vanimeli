<?php
class Ves_Base_Block_Widget_Counting extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/base/counting_number.phtml";
 		}
        $this->setTemplate($my_template);
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		$description = $this->getConfig('html');
		$description = base64_decode($description);

		if($description) {
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$description = $processor->filter($description);
		}

		$this->assign('description', $description);	
		$this->assign('title', $this->getConfig('title'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('icon', $this->getConfig('icon'));
		$this->assign('number', $this->getConfig('number'));
		$this->assign('font_size', $this->getConfig('font_size'));

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