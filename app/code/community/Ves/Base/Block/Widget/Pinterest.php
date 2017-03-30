<?php
class Ves_Base_Block_Widget_Pinterest extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/pinterest.phtml" );

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		$description = $this->getConfig('description');
		$description = strip_tags($description);
		$description = str_replace(" ","%20", $description);
		
		$this->assign('description', $this->getConfig('description'));
        $this->assign('select_type', $this->getConfig('select_type'));
		$this->assign('widget_heading', $this->getConfig('title'));
		$this->assign('url', $this->getConfig('url'));
		$this->assign('media', $this->getConfig('media'));
		$this->assign('description', $description);
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