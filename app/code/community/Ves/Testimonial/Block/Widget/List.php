<?php
class Ves_Testimonial_Block_Widget_List extends Ves_Testimonial_Block_Scroll implements Mage_Widget_Block_Interface
{
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
	 	parent::__construct($attributes);
		
	}

	public function _toHtml() {
		$this->_show = $this->getConfig("show");
		
		if(!$this->_show) return;
		if(isset($this->_config) && $this->_config) {
			foreach($this->_config as $key=>$val) {
				if($this->hasData($key)) {
					$this->setConfig($key, $this->getData($key));
				}
			}
		}
        
        if($this->hasData("template")) {
			$this->setTemplate( $this->getData("template") );
		} else {
			$this->setTemplate( "ves/testimonial/scroll.phtml" );
		} 

        return parent::_toHtml();
	}
}