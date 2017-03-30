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

class Nwdthemes_Revslider_Block_Adminhtml_Slider_Editor extends Nwdthemes_Revslider_Block_Adminhtml_Block_Template {

	/**
	 * Constructor
	 */

	public function __construct() {

		parent::__construct();

		//get taxonomies with cats
		$postTypesWithCats = RevSliderOperations::getPostTypesWithCatsForClient();
		$jsonTaxWithCats = RevSliderFunctions::jsonEncodeForClientSide($postTypesWithCats);

		//check existing slider data:
		$sliderID = $this->getRequest()->getParam('id');

		$arrFieldsParams = array();

		$uslider = new RevSlider();

		if(!empty($sliderID)){
			$slider = new RevSlider();
			$slider->initByID($sliderID);

			//get setting fields
			$settingsFields = $slider->getSettingsFields();
			$arrFieldsMain = $settingsFields['main'];
			$arrFieldsParams = $settingsFields['params'];

			$linksEditSlides = self::getViewUrl(RevSliderAdmin::VIEW_SLIDE,'id=new&slider='.intval($sliderID));

			$this->assign(get_defined_vars());
			$this->setTemplate('nwdthemes/revslider/templates/edit-slider.phtml');
		}else{
			$this->assign(get_defined_vars());
			$this->setTemplate('nwdthemes/revslider/templates/create-slider.phtml');
		}
	}
}
