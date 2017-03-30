<?php
class Ves_Base_Block_Widget_Googlemap extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/map.phtml" );

	}
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$content_html = $this->getConfig('description');
		if($this->isBase64Encoded($content_html)) {
			$content_html = base64_decode($content_html);
			if($content_html) {
				$processor = Mage::helper('cms')->getPageTemplateProcessor();
				$content_html = $processor->filter($content_html);
			}
		}
		
		$content_html = str_replace(array("\r", "\n"), "", $content_html);
		$content_html = html_entity_decode($content_html, ENT_QUOTES, 'UTF-8');
		$content_html = preg_replace("/\r\n|\r|\n/", ' ', $content_html);

		$this->assign('google_api', $this->getConfig('google_api'));
		$this->assign('description', $content_html);
        $this->assign('latitude', $this->getConfig('latitude'));
		$this->assign('longitude', $this->getConfig('longitude'));
		$this->assign('zoom', $this->getConfig('zoom'));
		$this->assign('width', $this->getConfig('width'));
		$this->assign('height', $this->getConfig('height'));
		$this->assign('is_preview', 1);
		return parent::_toHtml();
	}
	public function isBase64Encoded($data) {
		if(base64_encode(base64_decode($data)) === $data){
			return true;
		}
		return false;
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