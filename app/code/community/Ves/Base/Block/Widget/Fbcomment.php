<?php
class Ves_Base_Block_Widget_Fbcomment extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/facebook_comment.phtml" );

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$use_current_url = $this->getConfig('current_url', 0);
		$url = $this->getConfig('page_url');

		if($use_current_url) {
			$url = Mage::helper('core/url')->getCurrentUrl();
		}

		$app_id = $this->getConfig('app_id', '1451966991726173');
		$app_id = empty($app_id)?'1451966991726173':$app_id;
		$this->assign('url', $url);
		$this->assign('app_id', $app_id);
		$this->assign('width',$this->getConfig('width'));
		$this->assign('title',$this->getConfig('title'));
		$this->assign('height',$this->getConfig('height'));
		$this->assign('number_posts',$this->getConfig('number_posts'));
		$this->assign('theme',$this->getConfig('theme'));
		$this->assign('order_by',$this->getConfig('enable_header'));
		
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