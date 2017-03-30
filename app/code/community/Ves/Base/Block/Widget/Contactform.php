<?php
class Ves_Base_Block_Widget_Contactform extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

        $this->setTemplate("ves/base/contactform.phtml");
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "contacts/form.phtml";
 		}

 		if($this->hasData("action")) {
        	$action = $this->getData("action");
        }else{
 			$action = "contacts/index/post";
 		}

 		if($action) {
 		    $action = $this->getUrl($action);
 		} else {
 			$action = $this->getUrl("contacts/index/post");
 		}
 		
		$content_html = '{{block type="core/template" name="vesContactForm" form_action="'.$action.'" template="'.$my_template.'"}}';

		$processor = Mage::helper('cms')->getPageTemplateProcessor();
		$html = $processor->filter($content_html);

		$this->assign("html", $html);

		return $html;
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