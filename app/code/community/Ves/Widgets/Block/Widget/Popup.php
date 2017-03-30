<?php
class Ves_Widgets_Block_Widget_Popup extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	public function __construct($attributes = array())
	{
		parent::__construct($attributes);
	}

	public function _toHtml(){

		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}else{
			$my_template = "ves/widgets/form_popup.phtml";
		}
		$this->setTemplate($my_template);

		$form_widget_type = $this->getData('form_type');

		$form_template = '';
		$form_type = '';
		switch ($form_widget_type) {
			case 'newsletter':
			$form_type = 'newsletter/subscribe';
			$form_template = 'newsletter/subscribe.phtml';
			break;
			case 'login':
			$form_type = 'customer/form_login';
			$form_template = 'customer/form/login.phtml';
			break;
			case 'register':
			$form_type = 'customer/form_register';
			$form_template = 'customer/form/register.phtml';
			break;
			case 'forgotpassword':
			$form_type = 'customer/account_forgotpassword';
			$form_template = 'customer/form/forgotpassword.phtml';
			break;
		}
		$html = '';

		if(trim($this->hasData('form_template')) != ''){
			$form_template = $this->getData('form_template');
		}
		if($form_template){
			$html = Mage::app()->getLayout()->createBlock($form_type)->setTemplate($form_template)->toHtml();
		}else{
			if (base64_decode($this->getData('html'), true) == true){
				$customHtml = html_entity_decode(base64_decode($this->getData('html')));
			}else{
				$customHtml = $this->getData('html');
			}
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$customHtml = $processor->filter($customHtml);
			$html = $customHtml;
		}

		$this->assign('html',$html);
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