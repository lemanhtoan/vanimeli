<?php
class Ves_Base_Block_Widget_Accordionbg extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{


	public function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if($this->hasData("template")) {
        	$my_template = $this->getData("template");
        }else{
 			$my_template = "ves/base/accordion_bg.phtml";
 		}
        $this->setTemplate($my_template);
	}

	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$accordions = array();
		$limit = 50;
		$processor = Mage::helper('cms')->getPageTemplateProcessor();

		for($i=1; $i<=$limit; $i++) {
			$tmp = array();
			$tmp['cms'] = $this->getConfig("cms_".$i);
			$tmp['content'] = $this->getConfig("content_".$i);
			$tmp['header'] = base64_decode( $this->getConfig("header_".$i) );
			$tmp['class'] = $this->getConfig("class_".$i);
			$tmp['image'] = $this->getConfig("image_".$i);
			$parsed = parse_url($tmp['image']);
			if (empty($parsed['scheme']) && $tmp['image']) {
				$tmp['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$tmp['image'];
			}
			if($tmp['cms']) {
		 		$tmp['content'] = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($tmp['cms'])->toHtml();
			} elseif($tmp['content'] && $tmp['header']) {
				$tmp['content'] = base64_decode($tmp['content']);
				$tmp['content'] = $processor->filter($tmp['content']);

			}
			if($tmp['content'] && $tmp['header'] ) {
				$accordions[] = $tmp;
			}
			
		}

		$this->assign('heading_type', $this->getConfig('heading_type', 3));
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('accordions', $accordions );
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('widget_heading', $this->getConfig('title'));

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