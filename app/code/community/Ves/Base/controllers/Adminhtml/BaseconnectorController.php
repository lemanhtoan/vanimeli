<?php 
 /*------------------------------------------------------------------------
  # VenusTheme Base Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
error_reporting(0); // Set E_ALL for debuging
include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';

// Required for MySQL storage connector
// include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once Mage::getBaseDir().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'ves_base'.DIRECTORY_SEPARATOR.'elfinder'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
        :  null;                                    // else elFinder decide it itself
}

class Ves_Base_Adminhtml_BaseconnectorController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        return $this;
    }

    /**
     * index action
     */ 
    public function indexAction() {
        $root_media_folder = Mage::getStoreConfig('ves_base/general_setting/root_media');
        $path = Mage::getBaseDir('media').'/';
        $url = Mage::getBaseUrl('media');

        if($root_media_folder) {
            $path2 = Mage::getBaseDir('media').'/'.$root_media_folder."/";
            $url2 = Mage::getBaseUrl('media').$root_media_folder."/";
            if(file_exists($path2)) {
               $path = $path2;
               $url = $url2;
            }
        }

        $opts = array(
            // 'debug' => true,
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $path,         // path to files (REQUIRED)
                    'URL'           => $url, // URL to files (REQUIRED)
                    'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();

        exit();
    }
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('vesextensions/base/media');
    }
    
}
?>