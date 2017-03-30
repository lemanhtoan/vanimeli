<?php
 /*------------------------------------------------------------------------
  # VenusTheme Testimonial Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_Testimonial_Block_Testimonial_List extends Ves_Testimonial_Block_List {
	
	
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
      $layout_mode = $this->getListConfig("layout_mode", "list");
      if($layout_mode == "carousel") {
        $this->setTemplate( "ves/testimonial/carousel.phtml" );
      } else {
        $this->setTemplate( "ves/testimonial/list.phtml" );
      }
      
    }

  }
	
  protected function _prepareLayout()
  {
    $title = $this->getConfig("title");
    $this->getLayout()->getBlock('head')->setTitle($title);
    $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
    $breadcrumbs->addCrumb( 'home', array( 'label'=>Mage::helper('ves_testimonial')->__('Home'),
      'title'=>Mage::helper('ves_testimonial')->__('Go to Home Page'),
      'link' => Mage::getBaseUrl()) );

    $extension = "";
    $module = $this->getRequest()->getModuleName();
    $breadcrumbs->addCrumb( 'venus_testimonial', array( 'label' => $this->getConfig("title"),
      'title' => $this->getConfig("title"),
      'link'  =>  Mage::getBaseUrl().$module.$extension ) );
    return parent::_prepareLayout();
  }
	
	public function _toHtml(){
    $cms_block_id = $this->getConfig("cmsblock");
    $limit = (int)$this->getListConfig("list_limit");
    if(!$this->getConfig('show') ) return;
    $testimonials = array();
    $width = $this->getConfig('width');
    $width = empty($width)?200:$width;
    $height = $this->getConfig('height');
    $height = empty($height)?200:$height;
    //get collection
    $collection = $this->getTestimonials();
    $collection2 = Mage::getModel( 'ves_testimonial/testimonial' )
        ->getCollection();
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

    $max_items_page = $this->getListConfig("list_limit", 4);
    $columns = $this->getListConfig("list_columns", 4);
    if($max_items_page) {
      $this->setConfig("max_items_page", $max_items_page);
    }
    if($columns) {
      $this->setConfig("columns", $columns);
    }
    
    Mage::register( "paginateTotals", count($collection2) );
    Mage::register( "paginateLimitPerPages", $limit );
      
    return parent::_toHtml();
    
  }

  public function getTestimonials(){
    $filter_group = $this->getConfig('filter_group');
    $page = $this->getRequest()->getParam('page') ? $this->getRequest()->getParam('page') : 1;
    $limit = (int)$this->getListConfig("list_limit");
    $collection = Mage::getModel( 'ves_testimonial/testimonial' )
        ->getCollection();

    $collection->addFieldToFilter("group_testimonial_id", array("eq" => $filter_group))
          ->addFieldToFilter("is_active", array("eq" => 1))
          ->setOrder("position", "ASC")
          ->setPageSize( $limit )
          ->setCurPage( $page );
    return $collection;

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

 
  public function getTestimonial() {
    return Mage::registry('current_testimonial');
  }
}
?>