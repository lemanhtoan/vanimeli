<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2016. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Model_Images_Storage extends Mage_Cms_Model_Wysiwyg_Images_Storage {

    /**
     * Config object
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;

    /**
     * Config object as array
     *
     * @var array
     */
    protected $_configAsArray;

    /**
     *  Set upload file type
     *
     *  @param  string  Type
     */

    public function setUploadType($type = 'image') {
        if (in_array($type, array('image', 'video'))) {
            $this->getSession()->setUploadType($type);
        }
    }

    /**
     *  Set upload file type
     *
     *  @param  string  Type
     */

    public function getUploadType() {
        $type = $this->getSession()->getUploadType();
        return $type ? $type : 'image';
    }
        
    /**
     * Upload and resize new file
     *
     * @param string $targetPath Target directory
     * @param string $type Type of storage, e.g. image, media etc.
     * @throws Mage_Core_Exception
     * @return array File info Array
     */
    public function uploadFile($targetPath, $type = null)
    {
        $uploader = new Mage_Core_Model_File_Uploader('image');
        if ($allowed = $this->getAllowedExtensions($type)) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $result = $uploader->save($targetPath);

        if (!$result) {
            Mage::throwException( Mage::helper('cms')->__('Cannot upload file.') );
        }

        // create thumbnail
        if ($this->getUploadType() == 'image') {
            $this->resizeFile($targetPath . DS . $uploader->getUploadedFileName(), true);
        }

        $result['cookie'] = array(
            'name'     => session_name(),
            'value'    => $this->getSession()->getSessionId(),
            'lifetime' => $this->getSession()->getCookieLifetime(),
            'path'     => $this->getSession()->getCookiePath(),
            'domain'   => $this->getSession()->getCookieDomain()
        );

        return $result;
    }

    /**
     * Prepare allowed_extensions config settings
     *
     * @param string $type Type of storage, e.g. image, media etc.
     * @return array Array of allowed file extensions
     */
    public function getAllowedExtensions($type = null) {
        $extensions = $this->getConfigData('extensions');
        $extensions['video_allowed'] = array(
            'mp4'   => 1,
            'mp3'   => 1,
            'webm'  => 1,
            'ogv'   => 1,
            'avi'   => 1
        );
        if (is_string($type) && array_key_exists("{$type}_allowed", $extensions)) {
            $allowed = $extensions["{$type}_allowed"];
        } else {
            $allowed = $extensions['allowed'];
        }
        return array_keys(array_filter($allowed));
    }

}