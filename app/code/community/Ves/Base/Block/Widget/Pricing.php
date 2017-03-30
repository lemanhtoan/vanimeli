<?php
class Ves_Base_Block_Widget_Pricing extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/base/pricing.phtml";
 		}
        $this->setTemplate($my_template);
	}



	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}

		$content = $this->getConfig('content');
		$content = base64_decode($content);

		if($content) {
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$content = $processor->filter($content);
		}

		$this->assign('widget_heading', $this->getConfig('title'));	
		$this->assign('class', $this->getConfig('addition_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));

		$currency = $this->getConfig('currency');

		if(!$currency) {
			$currency = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		}
		$this->assign('subtitle', $this->getConfig('subtitle'));	
		$this->assign('currency', $currency);
		$this->assign('price', $this->getConfig('price'));	
		$this->assign('period', $this->getConfig('period'));
		$this->assign('linktitle', $this->getConfig('linktitle'));	
		$this->assign('link', $this->getConfig('link'));
		$this->assign('isfeatured', $this->getConfig('isfeatured'));
		$this->assign('content', $content);

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