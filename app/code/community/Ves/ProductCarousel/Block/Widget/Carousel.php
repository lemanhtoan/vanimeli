<?php
class Ves_ProductCarousel_Block_Widget_Carousel extends Ves_ProductCarousel_Block_List implements Mage_Widget_Block_Interface
{

	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
	 	parent::__construct($attributes);
		/*Cache Block*/

    	$enable_cache = $this->getConfig("enable_cache", 0 );
    	if(!$enable_cache) {
      		$cache_lifetime = null;
    	} else {
	      	$cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
	      	$cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
    	}

    	$this->addData(array('cache_lifetime' => $cache_lifetime));
    	$this->addCacheTag(array(
	      	Mage_Core_Model_Store::CACHE_TAG,
	      	Mage_Cms_Model_Block::CACHE_TAG,
	      	Ves_ProductCarousel_Model_Config::CACHE_WIDGET_TAG
    	));

    	/*End Cache Block*/
	}

	public function _toHtml() {
		$this->_show = $this->getConfig("show");
		
		if(!$this->_show) return;
		//Override Config
		if(isset($this->_config) && $this->_config && is_array( $this->_config)) {
			foreach($this->_config as $key=>$val) {
				if($this->hasData($key)) {
					$this->setConfig($key, $this->getData($key));
				}
			}
		}
		
		$parameter = array(
			'thumbnail_mode' => 'thumbnailMode',
			'thumb_height' => 'thumbHeight',
			'thumb_width' => 'thumbWidth',
			'title_maxchar' => 'titleMaxchar',
			'desc_maxchar' => 'descMaxchar'
			);

		foreach ($parameter as $k => $v) {
			$this->setConfig($v,$this->getData($k));
		}
	
        return parent::_toHtml();
	}

	/**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'VES_PRODUCTCAROUSEL_BLOCK_WIDGET_TAB',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
        );
    }

}