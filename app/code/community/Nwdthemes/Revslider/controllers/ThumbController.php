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

class Nwdthemes_Revslider_ThumbController extends Mage_Core_Controller_Front_Action {

	/**
	 * Get image thumbnail
	 */

	public function indexAction() {

		$mediaDir = Mage_Core_Model_Store::URL_TYPE_MEDIA;

		$imageUrl = base64_decode($this->getRequest()->getParam('i'));
		$imageFile = str_replace(Mage::getBaseUrl($mediaDir), '', $imageUrl);
		$width = $this->getRequest()->getParam('w', 0);
		$height = $this->getRequest()->getParam('h', 0);

		$thumbUrl = Mage::helper('nwdrevslider/images')->resizeImg($imageFile, $width, $height);
		$thumbPath = str_replace(Mage::getBaseUrl($mediaDir), Mage::getBaseDir($mediaDir) . DS, $thumbUrl);

		header('Content-Type: image/jpeg');
		header('Content-Length: ' . filesize($thumbPath));
		readfile($thumbPath);
		exit();
	}

}
