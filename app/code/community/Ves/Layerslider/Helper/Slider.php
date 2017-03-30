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
class Ves_Layerslider_Helper_Slider extends Mage_Core_Helper_Abstract {
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

	public function resizeImage( $image, $width, $height ){
		$parsed = parse_url($image);
		if (!empty($parsed['scheme'])) {
	        return $image;
	    }
	    
		$image = str_replace("ves_layerslider/upload/", "", $image);
		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::helper("ves_layerslider")->getImageBaseDir().$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}".DS.$image;
		$quality = $this->getConfig("resize_quality");

		if (!file_exists($imageResized) && file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);

			if($quality) {
		    	$imageObj->quality($quality);
		    } else {
		    	$imageObj->quality(100);
			}
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->constrainOnly(true);
			$imageObj->keepAspectRatio(true);
			$imageObj->keepFrame(false);
			$imageObj->keepTransparency(true);
			$imageObj->resize( $width, $height);
			$imageObj->save($imageResized);

		}
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'resized/'."{$width}x{$height}/".str_replace(DS,"/",$image);
	}

	public function getConfig( $val ){
		return Mage::getStoreConfig( "ves_layerslider/general_setting/".$val );
	}

	public function getSliderThumbnail( $banners = array(), $options = array() ) {
		$thumbnail_url = "";
		if($banners){
			$slider_data = array();
			if(isset($banners['slide'])) {
				$slider_data = $banners['slide'];
				$main_image = isset($slider_data['main_image'])?$slider_data['main_image']:"";

				if(!$main_image) {
					/*Get main Slider Image*/
					$tmp_images = array();
					foreach($banners as $key=>$banner) {
						$item_data = isset($banner['itemData'])?$banner['itemData']:array();
						$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;
						if(isset($item_data['type']) && $item_data['type'] == "image") {
							$tmp_images[] = $item_data['src'];
						}
						if(isset($item_data['type']) && $item_data['type'] == "image" && $ignore) {
							$main_image = $item_data['src'];
							break;
						}

					}
					if(!$main_image && $tmp_images) {
						$main_image = $tmp_images[0];
					}

				}
				if( $thumbnail = $main_image ){
					if( isset($options['thumbnail_cropping']) && $options['thumbnail_cropping'] ) {
						$thumbnail_url = $this->resizeImage($thumbnail, $options['thumbnail_width'], $options['thumbnail_height']);
					} else {
						$thumbnail_url = Mage::helper("ves_layerslider")->getImage( $thumbnail );
					}
				}
			}
		}
		return $thumbnail_url;
	}
	public function getSliderMainimage( $banners = array(), $options = array() ) {
		$main_image_url = "";
		if($banners){
			$slider_data = array();
			if(isset($banners['slide'])) {
				$slider_data = $banners['slide'];
				$main_image = isset($slider_data['main_image'])?$slider_data['main_image']:"";

				if(!$main_image) {
					/*Get main Slider Image*/
					$tmp_images = array();
					foreach($banners as $key=>$banner) {
						$item_data = isset($banner['itemData'])?$banner['itemData']:array();
						$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;
						if($item_data['type'] == "image") {
							$tmp_images[] = $item_data['src'];
						}
						if($item_data['type'] == "image" && $ignore) {
							$main_image = $item_data['src'];
							break;
						}

					}
					if(!$main_image && $tmp_images) {
						$main_image = $tmp_images[0];
					}

				}

				if($main_image) {
					if( $main_image && isset($options['image_cropping']) && $options['image_cropping']) {
						$main_image_url = $this->resizeImage($main_image, $options['width'], $options['height']);
					} else {
						$main_image_url = Mage::helper("ves_layerslider")->getImage( $main_image );
					}
				}
			}
		}
		return $main_image_url;
	}
	public function renderBannerElements( $banners = array(), $options = array() ) {
		$html = "";

		if($banners){
			$slider_data = array();
			if(isset($banners['slide'])) {
				$slider_data = $banners['slide'];
				unset($banners['slide']);
				/*If slider was disable, it don't show up on slideshow*/
				if(isset($slider_data['slider_status']) && !$slider_data['slider_status'])
					return "";

				$link = isset($slider_data['slider_link']) && $slider_data['slider_link'] ?' data-link="'.$slider_data['slider_link'].'"':'';

				$sliderDelay = (isset($slider_data['slider_delay']) && (int)$slider_data['slider_delay'])?'data-venuspausetime="'.(int)$slider_data['slider_delay'].'"':"";
				$sliderTransition = isset($slider_data['slider_transition'])?$slider_data['slider_transition']:"";

				$sliderTransition = !is_array($sliderTransition)?$sliderTransition:implode(",", $sliderTransition);

				if($sliderTransition && $sliderTransition != "random") {
					$sliderTransition = 'data-venustransition="'.$sliderTransition.'"';
				} else {
					$sliderTransition = "";
				}


				$sliderEasing = isset($slider_data['slider_easing'])?$slider_data['slider_easing']:"";

				if($sliderEasing  && $sliderEasing != "auto") {
					$sliderEasing = 'data-venuseasing="'.$sliderEasing.'"';
				} else {
					$sliderEasing = "";
				}


				$sliderClass = isset($slider_data['slider_class'])?$slider_data['slider_class']:"";
				$sliderBackgroundColor= isset($slider_data['slider_bgcolor'])?$slider_data['slider_bgcolor']:"";
				$sliderTitle = isset($slider_data['slider_title'])?' data-title="'.$slider_data['slider_title'].'"':"";
				$sliderAttribute = isset($slider_data['slider_attribute'])?$slider_data['slider_attribute']:"";
				$sliderCustomfields = isset($slider_data['slider_customfields'])?$slider_data['slider_customfields']:"";

				$slider_data['slider_videoid'] = isset($slider_data['slider_videoid'])?$slider_data['slider_videoid']:"";
				$sliderVideoplay = isset($slider_data['slider_videoplay'])?$slider_data['slider_videoplay']:0;
				

				$sliderType = "";
				if( isset($slider_data['slider_usevideo']) && ($slider_data['slider_usevideo'] == 'youtube' || $slider_data['slider_usevideo'] == 'vimeo' ) && $slider_data['slider_videoid'] ) {
					$sliderType = 'data-venustype="video"';
					if($sliderVideoplay) {
						$sliderType .= ' data-venusvideoplay="true"';
					}
				}

				$main_image = isset($slider_data['main_image'])?$slider_data['main_image']:"";

				if(!$main_image) {
					/*Get main Slider Image*/
					$tmp_images = array();
					foreach($banners as $key=>$banner) {
						$item_data = isset($banner['itemData'])?$banner['itemData']:array();
						$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;
						if($item_data['type'] == "image") {
							$tmp_images[] = $item_data['src'];
						}
						if($item_data['type'] == "image" && $ignore) {
							$main_image = $item_data['src'];
							break;
						}

					}
					if(!$main_image && $tmp_images) {
						//$main_image = $tmp_images[0];
					}

				}
				//if(!$main_image)
				//	return "";

				$thumbnail = $main_image;

				$base_layerslider_url = Mage::helper("ves_layerslider")->getImageBaseUrl();
				$base_layerslider_url = str_replace("/upload/","/", $base_layerslider_url);
				$dummy_image = $base_layerslider_url."icons/dummy.png";

				if( $main_image && isset($options['image_cropping']) && $options['image_cropping']) {
					$main_image = $this->resizeImage($main_image, $options['width'], $options['height']);
				} else {
					$main_image = Mage::helper("ves_layerslider")->getImage( $main_image );
				}

				if( isset($options['thumbnail_cropping']) && $options['thumbnail_cropping'] ) {
					$thumbnail = $this->resizeImage($thumbnail, $options['thumbnail_width'], $options['thumbnail_height']);
				} else {
					$thumbnail = Mage::helper("ves_layerslider")->getImage( $thumbnail );
				}


				$html .= "\n";
				$html .= '<div'.$link.' ' .$sliderTitle.' '.$sliderDelay.' '.$sliderType.' '.$sliderTransition.' '.$sliderEasing.' data-venusthumbnail="'.$thumbnail.'" data-venusimage="'.$main_image.'" '.$sliderCustomfields.' class="'.$sliderClass.'" data-bgcolor="'.$sliderBackgroundColor.'">';

				$html .= "\n";

				if( isset($slider_data['slider_usevideo']) && ($slider_data['slider_usevideo'] == 'youtube' || $slider_data['slider_usevideo'] == 'vimeo' ) && $slider_data['slider_videoid'] ) {

					$vurl  = '//player.vimeo.com/video/'.$slider_data['slider_videoid'].'/';
					if(  $slider_data['slider_usevideo'] == 'youtube' ){
					 	$vurl  = '//www.youtube.com/embed/'.$slider_data['slider_videoid'].'/';
					}
					$html .= '<iframe src="'.$vurl.'?title=0&amp;byline=0&amp;portrait=0;api=1'.($slider_data['slider_videoplay']?'&amp;autoplay=1&amp;loop=1':'').'" width="100%" height="100%" frameborder="0" webkitAllowFullScreen="webkitAllowFullScreen" mozallowfullscreen="mozallowfullscreen" allowFullScreen="allowFullScreen"></iframe>';
				}

				$html .= "\n";

				/*Render layers*/
				foreach($banners as $key=>$banner) {

					$html .= $this->renderBannerElement( $banner, $options );

				}
				$html .= "\n";
				$html .= '</div>';
			}

		}
		return $html;
	}
	public function renderBannerElement( $banner = array(), $options = array() ) {
		$item_data = isset($banner['itemData'])?$banner['itemData']:array();

		//Duration in/out
		$startAnimation = isset($item_data['in'])?$item_data['in']:array();
		$endAnimation = isset($item_data['out'])?$item_data['out']:array();


		$startAnimation['from'] = isset($startAnimation['from'])?$startAnimation['from']:"wipeLeft";
		$startAnimation['use'] = isset($startAnimation['use'])?$startAnimation['use']:"easeOutExpo";
		$startAnimation['at'] = isset($startAnimation['at'])?$startAnimation['at']: 0;

		if(  $startAnimation['from'] == 'auto' ){
			$startAnimation['from'] = '' ;
		}

		$endAnimation['to'] = isset($endAnimation['to'])?$endAnimation['to']:"auto";
		$endAnimation['use'] = isset($endAnimation['use'])?$endAnimation['use']:"easeOutExpo";
		$endAnimation['at'] = isset($endAnimation['at'])?$endAnimation['at']: 0;

		if(  $endAnimation['to'] == 'auto' ){
			$endAnimation['to'] = '' ;
		}

		//Basic config
		$top = isset($item_data['top'])?$item_data['top']:'0';
		$left = isset($item_data['left'])?$item_data['left']:'0';
		$clase = isset($item_data['clase'])?$item_data['clase']:"";
		$customclase = isset($item_data['customclase'])?$item_data['customclase']:"";
		$style = isset($item_data['style'])?$item_data['style']:"";
		$content = isset($item_data['content'])?$item_data['content']:"";
		$product_sku = isset($item_data['sku'])?$item_data['sku']:"";
		$showimage = isset($item_data['showimage'])?$item_data['showimage']:"1";
		$showprice = isset($item_data['showprice'])?$item_data['showprice']:"1";
		$showrating = isset($item_data['showrating'])?$item_data['showrating']:"0";
		$href = isset($item_data['href'])?$item_data['href']:"";
		$target = isset($item_data['target'])?$item_data['target']:"";
		$opacity = isset($item_data['opacity'])?$item_data['opacity']:"";
		$videosrc = isset($item_data['videosrc'])?$item_data['videosrc']:"";
		$videotype = isset($item_data['videotype'])?$item_data['videotype']:"youtube";
		$videoid = isset($item_data['videoid'])?$item_data['videoid']:"";

		$easing = $startAnimation['use'];
		$transition = $startAnimation['from'];
		if($transition && $transition != "random") {
			$transition = 'data-transition="'.$transition.'"';
		} else {
			$transition = "";
		}

		$endEasing = $endAnimation['use'];
		$endTransition = $endAnimation['to'];
		if($endTransition && $endTransition != "random") {
			$endTransition = 'data-endtransition="'.$endTransition.'"';
		} else {
			$endTransition = "";
		}

		$inoutEffect = "";
		if(strpos("custom.", $startAnimation['from']) === null) {
			$inoutEffect = str_replace("custom.","", $startAnimation['from'])." ";
		}
		if(strpos("custom.", $endAnimation['to']) === null) {
			$inoutEffect = str_replace("custom.","", $endAnimation['to'])." ";
		}

		$base_dir = Mage::helper("ves_layerslider")->getImageBaseDir();

		$html = "";
		if($item_data) {
			$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;
			if(!$ignore) {
				$data_width_height = "";

				if(isset($item_data['width']) && $item_data['width']){
					$data_width_height .= ' data-width = "'.(float)$item_data['width'].'" ';
				}
				if(isset($item_data['height']) && $item_data['height']){
					$data_width_height .= ' data-height = "'.(float)$item_data['height'].'" ';
				}

				$custom_style = "";
				if($style ) {
					$style = htmlspecialchars($style);
					$style = str_replace('"','\"', $style);
				}
				$html .= '<div class="iview-caption tp-caption '.$clase.' '.$customclase.' '.$inoutEffect.' hide" '.$transition.'
							data-easing="'.$easing.'"
							data-speed="'.$startAnimation['during'].'"
							data-endeasing="'.$endEasing.'" '.$endTransition.'
							data-endspeed="'.$endAnimation['during'].'"
							data-x="'.(float)$left.'"
							data-y="'.(float)$top.'" '.$data_width_height.'
							data-start="'.(int)$startAnimation['at'].'"
							data-end="'.(int)$endAnimation['at'].'"'.$custom_style.'>';

				$html .= "\n";

				switch($item_data['type']) {

					case 'text':

					 	$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$css = "";
					 	/*
					 	if(isset($item_data['width']) && $item_data['width']){
					 		$css .= " width:".$item_data['width'].";";
					 	}
					 	if(isset($item_data['height']) && $item_data['height']){
					 		$css .= " height:".$item_data['height'].";";
					 	}*/
					 	$tag = isset($item_data['tag'])?$item_data['tag']:"h3";
					 	$tag = 'div';
					 	$html .= '<'.$tag.' style="'.$css.$style.'">'.$content.'</'.$tag.'>';


					break;

					case 'productsku':

					 	if($product_sku) {
					 		$css = "";
						 	$html .= '<div class="product-hotspot">';
						 	$html .= '<a href="javascript:;" class="hotspot" data-tipelement=".hotspot-content"><img src="'.Mage::helper("ves_layerslider")->getHotspot().'" alt="hotspot" /></a>';

						 	$product_info_block = Mage::app()->getLayout()->createBlock("ves_layerslider/html", 'product-info-navigation')
	                                                            ->setTemplate("ves/layerslider/product_info.phtml")
	                                                            ->assign("product_sku", $product_sku)
	                                                            ->assign("showimage", $showimage)
	                                                            ->assign("showprice", $showprice)
	                                                            ->assign("showrating", $showrating);
	                        $html .= '<div class="hotspot-content" style="display:none">';
	                        $html .= $product_info_block->toHtml();
	                        $html .= '</div>';
						 	$html .= '</div>';
					 	}

					break;
					case 'image':
						$parsed = parse_url($item_data['src']);
						if(!empty($parsed['scheme']) || ($item_data['src'] && (file_exists($base_dir.$item_data['src']) || file_exists(Mage::getBaseDir('media')."/".$item_data['src'])))) {
						 	$width = isset($item_data['width'])?$item_data['width']:"";
						 	$height = isset($item_data['height'])?$item_data['height']:"";

						 	$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;

						 	if(!$ignore) {
						 		$css = "";

							 	if(isset($item_data['width']) && $item_data['width']){
							 		$item_data['width'] = is_numeric($item_data['width'])?$item_data['width']."px":$item_data['width'];
							 		$css .= " width:".$item_data['width'].";";
							 	}
							 	if(isset($item_data['height']) && $item_data['height']){
							 		$item_data['height'] = is_numeric($item_data['height'])?$item_data['height']."px":$item_data['height'];
							 		$css .= " height:".$item_data['height'].";";
							 	}
								$html .= '<img src="'.Mage::helper("ves_layerslider")->getImage( $item_data['src'] ).'" style="'.$css.$style.'" alt="image" />';

						 	}
						}

					break;
					case 'video':
						$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$css = "";

					 	if(isset($item_data['width']) && $item_data['width']){
					 		$css .= " width:".$item_data['width'].";";
					 	}
					 	if(isset($item_data['height']) && $item_data['height']){
					 		$css .= " height:".$item_data['height'].";";
					 	}

					 	$video_link = "";

					 	if($videotype == "youtube") {
					 		$video_link = "//www.youtube.com/embed/".$videoid;
					 	} elseif($videotype == "vimeo") {
					 		$video_link = "//player.vimeo.com/video/".$videoid;
					 	}

					 	$html .= '<iframe src="'.$video_link.'"
					 					width="'.$width.'" height="'.$height.'"
					 					frameborder="0"
						                webkitallowfullscreen="webkitAllowFullScreen"
						                mozallowfullscreen="mozallowfullscreen"
						                allowfullscreen="allowFullScreen"
				                  		style="'.$css.$style.'"></iframe>';
					break;
					case 'imglink':
						$parsed = parse_url($item_data['src']);
						if(!empty($parsed['scheme']) || ($item_data['src'] && (file_exists($base_dir.$item_data['src']) || file_exists(Mage::getBaseDir('media')."/".$item_data['src'])))) {
						 	$width = isset($item_data['width'])?$item_data['width']:"";
						 	$height = isset($item_data['height'])?$item_data['height']:"";

						 	$css = "";

						 	if(isset($item_data['width']) && $item_data['width']){
						 		$css .= " width:".$item_data['width'].";";
						 	}
						 	if(isset($item_data['height']) && $item_data['height']){
						 		$css .= " height:".$item_data['height'].";";
						 	}

						 	$html .= '<a href="'.$href.'" target="'.$target.'" style="'.$style.'"><img src="'.Mage::helper("ves_layerslider")->getImage( $item_data['src'] ).'" style="'.$css.'"/></a>';

						}
					break;
					case 'link':
						$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$css = "";

					 	$html .= '<a href="'.$href.'" target="'.$target.'" style="'.$css.$style.'">'.$content.'</a>';
					break;
					case 'block':

					 	$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$css = "";
					 	if(isset($item_data['width']) && $item_data['width']){
					 		$css .= " width:".$item_data['width'].";";
					 	}
					 	if(isset($item_data['height']) && $item_data['height']){
					 		$css .= " height:".$item_data['height'].";";
					 	}
					 	$css .= ' opacity:'.$opacity.';';

					 	$html .= '<div style="'.$css.$style.'"></div>';

					break;
				}

				$html .= "\n";

				$html .= '</div>';

			}
		}
		return $html;
	}

}
