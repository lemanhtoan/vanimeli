<?php
class Ves_Widgets_Block_Widget_Countdowntimer extends Ves_Widgets_Block_List implements Mage_Widget_Block_Interface{
	
	public function __construct($attributes = array())
	{
		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		}else{
			$my_template = "ves/widgets/countdowntimer.phtml";
		}
		$this->setTemplate($my_template);
		parent::__construct( $attributes );
	}
	
	
}