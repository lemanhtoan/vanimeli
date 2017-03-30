<?php
class Ves_Base_Block_Widget_Socialshare extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);
		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}else{
			$my_template = "ves/base/socialshare.phtml";
		}
		$this->setTemplate($my_template);
	}



	protected function _toHtml(){
		$this->assign('widget_heading', $this->getConfig('title'));	
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
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

	public function getProduct() {
		return Mage::registry('current_product');
	}

	public function canEmailToFriend() {
		$sendToFriendModel = Mage::registry('send_to_friend_model');
        return $sendToFriendModel && $sendToFriendModel->canEmailToFriend();
	}
}