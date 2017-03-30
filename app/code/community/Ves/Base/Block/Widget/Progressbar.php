<?php
class Ves_Base_Block_Widget_Progressbar extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/progress_bar.phtml" );

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
        $this->assign('widget_heading', $this->getConfig('title'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('prog_type', $this->getConfig('prog_type'));
		$this->assign('success', $this->getConfig('success'));
		$this->assign('info', $this->getConfig('info'));
		$this->assign('warning', $this->getConfig('warning'));
		$this->assign('danger', $this->getConfig('danger'));
		$this->assign('prog_label', $this->getConfig('prog_label'));
		
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