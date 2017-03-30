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

class Nwdthemes_Revslider_Model_Sliders extends Mage_Core_Model_Abstract {

	/**
	 * Constructor
	 */

    public function _construct() {
        parent::_construct();
        $this->_init('nwdrevslider/sliders');
    }

	/**
	 * Get max slide sort order
	 *
	 * @param int slider id
     * @return int max order
     */

	public function getMaxOrder($sliderId) {
		$maxOrder = 0;
		$slide = Mage::getModel('nwdrevslider/slides')
			->getCollection()
			->addFieldToFilter('slider_id', $sliderId)
			->setOrder('slide_order', 'desc')
			->setPageSize(1)
			->getFirstItem();
		if ($slide)
		{
			$maxOrder = $slide->getSlideOrder();
		}
		return $maxOrder;
	}

	/**
	 * Shift order of slides in slider
	 *
	 * @param int slider id
	 * @param int from order
     */

	public function shiftOrder($sliderId, $fromOrder) {
		$collection = Mage::getModel('nwdrevslider/slides')
			->getCollection()
			->addFieldToFilter('slider_id', $sliderId)
			->addFieldToFilter('slide_order', array('gteq' => $fromOrder));
		foreach ($collection as $_item) {
			$_item->setSlideOrder( $_item->getSlideOrder() + 1 );
			$_item->save();
		}
	}
	
	/**
	 * Duplicate slider slides
	 *
	 * @param int $sourceSliderId
	 * @param int $targetSliderId
	 */
	
	public function duplicateSlides($sourceSliderId, $targetSliderId) {
		
		$collection = Mage::getModel('nwdrevslider/slides')
			->getCollection()
			->addFieldToFilter('slider_id', $sourceSliderId);
			
		foreach ($collection as $_item)	{
			$_newSlideData = $_item->getData();
			$_newSlideData['slider_id'] = $targetSliderId;
			unset($_newSlideData['id']);
			$_newSlide = Mage::getModel('nwdrevslider/slides');
			$_newSlide
				->setData($_newSlideData)
				->save();
		}
	}

}
