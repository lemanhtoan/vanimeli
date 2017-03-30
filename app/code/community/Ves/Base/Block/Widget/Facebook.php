<?php
class Ves_Base_Block_Widget_Facebook extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/facebook.phtml" );

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$url = $this->getConfig('facebook_url');
		$url = urlencode($url);
		$app_id = $this->getConfig('app_id', '1451966991726173');
		$app_id = empty($app_id)?'1451966991726173':$app_id;
		$this->assign('url', $url);
		$this->assign('app_id', $app_id);
		$this->assign('width',$this->getConfig('width'));
		$this->assign('title',$this->getConfig('title'));
		$this->assign('height',$this->getConfig('height'));
		$this->assign('theme',$this->getConfig('theme'));
		$this->assign('showfriends',$this->getConfig('enable_showfriends'));
		$this->assign('header',$this->getConfig('enable_header'));
		$this->assign('posts',$this->getConfig('enable_posts'));
		$this->assign('border',$this->getConfig('enable_border'));

		$custom_css = base64_decode($this->getConfig('custom_css'));
		$custom_css = str_replace(array("\r", "\n"), "", $custom_css);
		$this->assign('css',$custom_css);
		
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