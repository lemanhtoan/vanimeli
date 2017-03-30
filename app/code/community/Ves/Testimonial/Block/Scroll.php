<?php 
/*------------------------------------------------------------------------
 # VenusTheme Brand Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_Scroll extends Ves_Testimonial_Block_List 
{

	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		$show = $this->getConfig("show");
		if(!$show) return;

		parent::__construct( $attributes );
		if($this->hasData("template")) {
			$this->setTemplate( $this->getData("template") );
		} else {
			$this->setTemplate( "ves/testimonial/scroll.phtml" );
		}

	}

	public function _toHtml(){
		//die("tesstttt");
		$cms_block_id = $this->getConfig("cmsblock");
		if($cms_block_id){
			$cms = Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($cms_block_id)->toHtml();
			$this->assign( "cms", $cms );
		}


		$filter_group = $this->getConfig('filter_group');
		if( empty($filter_group) || !$this->getConfig('show') ) return;

		$testimonials = array();
		$width = $this->getConfig('width');
		$width = empty($width)?200:$width;
		$height = $this->getConfig('height');
		$height = empty($height)?200:$height;

		$collection = Mage::getModel( 'ves_testimonial/testimonial' )
				->getCollection();

		$collection->addFieldToFilter("group_testimonial_id", array("eq" => $filter_group))
					->addFieldToFilter("is_active", array("eq" => 1))
					->setOrder("position", "ASC");

		if($collection->getSize() > 0) {
			foreach($collection as $item) {
				$tmp = array();

				$tmp['description'] = $item->getDescription();
				$tmp['profile'] = $item->getProfile();
				$tmp['video_link'] = $item->getVideoLink();
				$tmp['avatar'] = $item->getAvatar();
				$tmp['facebook'] = $item->getFacebook();
				$tmp['twiter'] = $item->getTwiter();
				$tmp['google'] = $item->getGoogle();
				$tmp['youtube'] = $item->getYoutube();
				$tmp['pinterest'] = $item->getPinterest();
				$tmp['vimeo'] = $item->getVimeo();
				$tmp['instagram'] = $item->getInstagram();
				$tmp['linkedin'] = $item->getLinkedIn();
				$tmp['thumb'] = $this->resizeImage($tmp['avatar'], $width, $height);
				$testimonials[] = $tmp;
			}
		}
		$this->assign( 'testimonials', $testimonials );  
		return parent::_toHtml();
		
	}

	public function resizeImage($image, $width = 200, $height = 200) {

	    $_image_dir = Mage::getBaseDir('media') . DS;
	    $cache_dir = Mage::getBaseDir('media') . DS . 'resized' . DS;

	    if($image) {
		    if (file_exists($cache_dir . $image)) {
		        return Mage::getBaseUrl('media') .'/resized/' . $image;
		    } elseif (file_exists($_image_dir . $image)) {
		        if (!is_dir($cache_dir)) {
		            mkdir($cache_dir);
		        }

		        $_image = new Varien_Image($_image_dir . $image);
		        $_image->constrainOnly(true);
		        $_image->keepAspectRatio(true);
		        $_image->keepTransparency(true);
		        $_image->resize((int)$width, (int)$height);
		        $_image->save($cache_dir . $image);

		        return Mage::getBaseUrl('media') . '/resized/'. $image;
		    }
		}
	    return "";
	}
	/**
     * get value of the extension's configuration
     *
     * @return string
     */
    public function getConfig( $key, $panel='general_setting', $default = ""){
        $return = "";
        $value = $this->getData($key);
        //Check if has widget config data
        if($this->hasData($key) && $value !== null) {

          if($value == "true") {
            return 1;
          } elseif($value == "false") {
            return 0;
          }
          
          return $value;
          
        } else {

          if(isset($this->_config[$key])){
            $return = $this->_config[$key];
          }else{
            $return = Mage::getStoreConfig("ves_testimonial/$panel/$key");
          }
          if($return == "" && !$default) {
            $return = $default;
          }

        }

        return $return;
    }
    
    /**
     * overrde the value of the extension's configuration
     *
     * @return string
     */
    function setConfig($key, $value) {
        if($value == "true") {
            $value =  1;
        } elseif($value == "false") {
            $value = 0;
        }
        if($value != "") {
            $this->_config[$key] = $value;
        }
      return $this;
    }
}	