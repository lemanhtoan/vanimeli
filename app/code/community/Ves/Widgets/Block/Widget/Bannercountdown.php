<?php
class Ves_Widgets_Block_Widget_Bannercountdown extends Ves_Widgets_Block_List implements Mage_Widget_Block_Interface{
	
	public function __construct($attributes = array())
	{
		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		}else{
			$my_template = "ves/widgets/bannercountdown.phtml";
		}
		$this->setTemplate($my_template);
		parent::__construct( $attributes );
	}

	public function _toHtml(){
		$this->setListRule($this->getRuleById());
		return parent::_toHtml();
	}
	
	public function getRuleById(){
		$current_id = $this->getConfig('filter_group'); // Sku you are looking for
		$rules = Mage::getModel('salesrule/rule')->load($current_id);
    return $rules;
  }
}