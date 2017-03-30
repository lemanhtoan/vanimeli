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

class Nwdthemes_Revslider_Model_Slides extends Mage_Core_Model_Abstract {

	/**
	 * Constructor
	 */

    public function _construct() {
        parent::_construct();
        $this->_init('nwdrevslider/slides');
    }

	/**
	 * Duplicate slide
	 *
	 * @param int slideId
	 * @return int new slide id
	 */

	public function duplicate($slideId) {

		$slide = $this->load($slideId);

		$newSlideData = $slide->getData();
		unset($newSlideData['id']);

		$model = $this->setData($newSlideData);
		try {
			$model->save();
		} catch (Exception $e) {
			$this->throwError($e->getMessage());
		}

		return $model->getId();
	}

}
