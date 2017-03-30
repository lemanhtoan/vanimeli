<?php
class Ves_Base_Block_Widget_Flickr extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		parent::__construct( $attributes );
		$this->setTemplate( "ves/base/flickr.phtml" );
	}

	
	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$this->assign('userId',$this->getConfig('user_id'));
		$this->assign('speed',$this->getConfig('speed'));
		$this->assign('title',$this->getConfig('title'));
		$this->assign('thumbnail',$this->getConfig('thumbnail'));
		$this->assign('popup',$this->getConfig('popup'));
		$this->assign('popupImageWidth',$this->getConfig('popup_image_width'));
		$this->assign('popupImageHeight',$this->getConfig('popup_image_height'));
		$this->assign('thumbnailImageWidth',$this->getConfig('thumbnail_image_width'));
		$this->assign('thumbnailImageHeight',$this->getConfig('thumbnail_image_height'));


		$params = array(
			'api_key'	=> $this->getConfig('api_id'),
			'method'	=> 'flickr.people.getPhotos',
			'user_id'   => $this->getConfig('user_id'),
			'per_page'  => $this->getConfig('number_show'),
			'format'	=> 'php_serial',
			);

		$encoded_params = array();

		foreach ($params as $k => $v){

			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}
		try{
			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$rsp = file_get_contents($url);

			$rsp_obj = unserialize($rsp);

			if ($rsp_obj['stat'] == 'ok'){

				$this->assign('photos',$rsp_obj['photos']['photo']);

			}
		}catch(Exception $e){
			throw new Exception('Disconnect');
		}
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

