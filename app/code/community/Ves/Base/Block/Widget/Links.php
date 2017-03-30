<?php
class Ves_Base_Block_Widget_Links extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	var $_config = array();

	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		if(isset($attributes['enable_collapse']) && $attributes['enable_collapse'] == "true") {
			$this->setTemplate( "ves/base/accordion_links.phtml" );
		} else {
			$this->setTemplate( "ves/base/links.phtml" );
		}
		

	}

	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$widget_heading = $this->getConfig("title");
		$limit_links = 50;
		$links = array();
		for($i=1; $i<=$limit_links; $i++) {
			$tmp = array();
			$tmp['link'] = $this->getConfig("link_".$i);
			$parsed = parse_url($tmp['link']);
			if (empty($parsed['scheme']) && $tmp['link'] != "#") {
		        $tmp['link'] = Mage::getUrl('',array('_direct' => $tmp['link'], '_type' => 'direct_link'));
		    }
			$tmp['link_icon'] = $this->getConfig("link_icon_".$i);
			$tmp['text'] = $this->getConfig("text_link_".$i);
			if($tmp['link'] && $tmp['text']) {
				$links[] = $tmp;
			}
		}
        $this->assign('widget_heading', $widget_heading);
		$this->assign('addition_cls', $this->getConfig('addition_cls'));
		$this->assign('ul_cls', $this->getConfig('ul_cls'));
		$this->assign('stylecls', $this->getConfig('stylecls'));
		$this->assign('links', $links);

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