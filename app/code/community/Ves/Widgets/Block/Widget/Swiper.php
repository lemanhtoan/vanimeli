<?php
class Ves_Widgets_Block_Widget_Swiper extends Ves_Widgets_Block_List implements Mage_Widget_Block_Interface{
	
	public function __construct($attributes = array())
	{
		if($this->hasData("template")) {
			$my_template = $this->getData("template");
		}elseif(isset($attributes['template']) && $attributes['template']) {
			$my_template = $attributes['template'];
		}else{
			$my_template = "ves/widgets/swiper.phtml";
		}
		$this->setTemplate($my_template);
		parent::__construct( $attributes );
	}

	public function _toHtml(){
		$carousels = array();
		$limit = 30;
		$processor = Mage::helper('cms')->getPageTemplateProcessor();

		for($i=1; $i<=$limit; $i++) {
			$tmp = array();
			$tmp['content'] = $this->getConfig("content_".$i);
			$tmp['size'] = $this->getConfig("size_".$i);
			if($tmp['content']) {
				$tmp['content'] = base64_decode($tmp['content']);
				$tmp['content'] = $processor->filter($tmp['content']);
				$itemObject = new Varien_Object();
                $itemObject->setData($tmp);
				$carousels[] = $itemObject;
			}
		}
		$this->setDataItems($carousels);

		return parent::_toHtml();
	}
}