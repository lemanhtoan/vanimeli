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

class Nwdthemes_Revslider_Block_Adminhtml_Images_Content_Files extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content_Files {

    /**
     * Prepared Files collection for current directory
     *
     * @return Varien_Data_Collection_Filesystem
     */
    public function getFiles() {
        if (! $this->_filesCollection) {
            $this->_filesCollection = Mage::getSingleton('nwdrevslider/images_storage')
				->getFilesCollection( Mage::helper('nwdrevslider/images')->getCurrentPath(), $this->_getMediaType() );
        }
        return $this->_filesCollection;
    }

}
