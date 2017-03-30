<?php
/*------------------------------------------------------------------------
 # VenusTheme Layer slider Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Block_Html extends Mage_Catalog_Block_Product_Abstract 
{
    /**
     * @var string $_config
     * 
     * @access protected
     */
    protected $_config = '';
    
    /**
     * @var string $_config
     * 
     * @access protected
     */
    protected $_listDesc = array();
    
    /**
     * @var string $_config
     * 
     * @access protected
     */
    protected $_show = 0;
    protected $_theme = "";

    protected $_banner = null;
    
    /**
     * Contructor
     */
    public function __construct($attributes = array())
    {   
        
        parent::__construct($attributes);      
    }

    public function getConfig( $val ){ 
        return Mage::getStoreConfig( "ves_layerslider/general_setting/".$val );
    }

    public function renderBannerElements( $banners = array(), $options = array()) {
        $html = Mage::helper("ves_layerslider/slider")->renderBannerElements( $banners, $options );
        return $html;
    }

    public function getSliderThumbnail( $banners = array(), $options = array()) {
        $html = Mage::helper("ves_layerslider/slider")->getSliderThumbnail( $banners, $options );
        return $html;
    }

    public function getSliderMainimage( $banners = array(), $options = array()) {
        $html = Mage::helper("ves_layerslider/slider")->getSliderMainimage( $banners, $options );
        return $html;
    }
}
