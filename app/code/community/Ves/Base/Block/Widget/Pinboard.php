<?php
class Ves_Base_Block_Widget_Pinboard extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	var $_settings = array();
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		$this->_settings = $attributes;

        $this->setTemplate("ves/base/pinterest_board.phtml");

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
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
	      
	    } elseif(isset($this->_settings[$key]) && ($value = $this->_settings[$key])) {
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