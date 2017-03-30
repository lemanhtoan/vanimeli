
<?php
class Ves_Base_Block_Widget_Googleplus extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		$this->setTemplate( "ves/base/google-plus.phtml" );
		parent::__construct( $attributes );
	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$this->assign('url',$this->getConfig('google_url'));
		$this->assign('title',$this->getConfig('title'));
		$this->assign('layout',$this->getConfig('layout'));
		$this->assign('theme',$this->getConfig('theme'));
		$this->assign('width',$this->getConfig('width'));
		$this->assign('tagline',$this->getConfig('enable_tagline'));
		$this->assign('coverphoto',$this->getConfig('enable_coverphoto'));
		$this->assign('badge',$this->getConfig('badge_types'));
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