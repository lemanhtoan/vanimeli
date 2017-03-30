<?php
class Ves_Base_Block_Widget_Sociallinks extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);
		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}else{
			$my_template = "ves/base/sociallinks.phtml";
		}
		$this->setTemplate($my_template);
	}



	protected function _toHtml(){
		$this->assign('widget_heading', $this->getConfig('title'));	
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('facebook_link', $this->getConfig('facebook_link'));	
		$this->assign('twitter_link', $this->getConfig('twitter_link'));
		$this->assign('google_plus', $this->getConfig('google_plus'));
		$this->assign('youtube', $this->getConfig('youtube'));
		$this->assign('skype', $this->getConfig('skype'));
		$this->assign('vimeo', $this->getConfig('vimeo'));
		$this->assign('instagram', $this->getConfig('instagram'));
		$this->assign('linkedin', $this->getConfig('linkedin'));
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