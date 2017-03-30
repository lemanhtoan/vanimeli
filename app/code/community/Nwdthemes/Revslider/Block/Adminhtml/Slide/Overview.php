<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Block_Adminhtml_Slide_Overview extends Nwdthemes_Revslider_Block_Adminhtml_Block_Template {

	/**
	 * Constructor
	 */

	public function __construct() {

		parent::__construct();
		
		$operations = new RevSliderOperations();
		
		$sliderID = RevSliderFunctions::getGetVar("id");
		
		if(empty($sliderID))
			RevSliderFunctions::throwError("Slider ID not found"); 
		
		$slider = new RevSlider();
		$slider->initByID($sliderID);
		$sliderParams = $slider->getParams();
		
		$arrSliders = $slider->getArrSlidersShort($sliderID);
		$selectSliders = RevSliderFunctions::getHTMLSelect($arrSliders,"","id='selectSliders'",true);
		
		$numSliders = count($arrSliders);
		
		//set iframe parameters	
		$width = $sliderParams["width"];
		$height = $sliderParams["height"];
		
		$iframeWidth = $width+60;
		$iframeHeight = $height+50;
		
		$iframeStyle = "width:".$iframeWidth."px;height:".$iframeHeight."px;";
		
		if($slider->isSlidesFromPosts()){
			$arrSlides = $slider->getSlidesFromPosts(false);
		}elseif($slider->isSlidesFromStream()){
			$arrSlides = $slider->getSlidesFromStream(false);
		}else{
			$arrSlides = $slider->getSlides(false);
		}
		
		$numSlides = count($arrSlides);
		
		$linksSliderSettings = $this->getViewUrl(RevSliderAdmin::VIEW_SLIDER,'id='.$sliderID);
		
		$this->assign(get_defined_vars());
	}
}
