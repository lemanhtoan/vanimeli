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

class Nwdthemes_Revslider_Block_Adminhtml_Images_Content_Uploader extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Uploader {

    public function __construct() {
        parent::__construct();
        if ($this->getRequest()->getParam('type') == 'video') {
            $this->getConfig()->setFilters(array(
                'media' => array(
                    'label' => Mage::helper('adminhtml')->__('Video (.mp4, .mp3, .webm, .ogv, .avi)'),
                    'files' => array('*.mp4', '*.mp3' , '*.webm', '*.ogv', '*.avi')
                )
            ));
        }
    }

}
