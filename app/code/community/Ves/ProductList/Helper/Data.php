<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getPageLayoutList(){
        $type[''] =  $this->__('No layout updates');
        $type['empty'] =  $this->__('Empty');
        $type['one_column'] =  $this->__('1 column');
        $type['two_columns_left'] =  $this->__('2 columns with left bar');
        $type['two_columns_right'] =  $this->__('2 columns with right bar');
        $type['three_columns'] =  $this->__('3 columns');
        return $type;
    }

    public function convertFlatToRecursive(array $rule, $keys)
    {
        $arr = array();
        foreach ($rule as $key => $value) {
            if (in_array($key, $keys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
            else {
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date(
                        $value, Varien_Date::DATE_INTERNAL_FORMAT, null, false
                        );
                }
            }
        }

        return $arr;
    }

    public function updateChild($array, $from, $to)
    {
        foreach ($array as $k => $rule) {
            foreach ($rule as $name => $param) {
                if ($name == 'type' && $param == $from)
                    $array[$k][$name] = $to;
            }
        }
        return $array;
    }

    public function checkVersion($version, $operator = '>=')
    {
        return version_compare(Mage::getVersion(), $version, $operator);
    }

    public function uploadFiles($filename)
    {
        $mediaPath = Mage::getBaseDir('media'). DS .'productlist'. DS;
        if (isset($_FILES[$filename]['name']))
        {
            $uploader = new Varien_File_Uploader($filename);
            $uploader->setAllowedExtensions(array('gif','jpg','jpeg','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($mediaPath);
            return $result;
        }
    }

    public function removeFile($file)
    {
        //Delete Image
        $mediaPath = Mage::getBaseDir('media'). DS;
        $imgPath = $mediaPath . $file; // echo $imgPath;exit;
        unlink($imgPath);
        return true;
    }

    public function reImageName($imageName) {

        $subname = substr($imageName, 0, 2);
        $array = array();
        $subDir1 = substr($subname, 0, 1);
        $subDir2 = substr($subname, 1, 1);
        $array[0] = $subDir1;
        $array[1] = $subDir2;
        $name = $array[0] . '/' . $array[1] . '/' . $imageName;

        return $imageName;
    }

    public function getAllStores() {
        $allStores = Mage::app()->getStores();
        $stores = array();
        foreach ($allStores as $_eachStoreId => $val)
        {
            $stores[]  = Mage::app()->getStore($_eachStoreId)->getId();
        }
        return $stores;
    }

    public function getImportPath($theme = ""){
        $path = Mage::getBaseDir('var') . DS . 'cache'.DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
    }

    /**
     * Handles CSV upload
     * @return string $filepath
     */
    public function getUploadedFile() {
        $filepath = null;
        if(isset($_FILES['importfile']['name']) and (file_exists($_FILES['importfile']['tmp_name']))) {
            try {
                $uploader = new Varien_File_Uploader('importfile');
                $uploader->setAllowedExtensions(array('csv','txt', 'json', 'xml')); // or pdf or anything
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $path = Mage::helper('productlist')->getImportPath();
                $file_type = "csv";
                if(isset($_FILES['importfile']['tmp_name']['type']) && $_FILES['importfile']['tmp_name']['type'] == "application/json") {
                    $file_type = "json";
                }
                $uploader->save($path, "ves_productlist_import_data.".$file_type);
                $filepath = $path . "ves_productlist_import_data.".$file_type;
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
        return $filepath;
    }

    public function getCustomerGroups()
    {
        $data_array = array();
        $customer_groups = Mage::getModel('customer/group')->getCollection();;

        foreach ($customer_groups as $item_group) {
            $data_array[] = $item_group->getCustomerGroupId();
        }
        return ($data_array);
    }

    public function resizeImage($image, $width = 100, $height = 100){
        if($width == 0 || $height == 0) {
            return Mage::getBaseUrl("media").$image;
        }
        $media_base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $image = str_replace($media_base_url, "", $image);
        $media_base_url = str_replace("https://","http://", $media_base_url);
        $image = str_replace($media_base_url, "", $image);

        $_imageUrl = Mage::getBaseDir('media').DS.$image;
        $_imageResized = Mage::getBaseDir('media').DS."resized".DS.(int)$width."x".(int)$height.DS.$image;

        if (!file_exists($_imageResized)&&file_exists($_imageUrl)){
            $imageObj = new Varien_Image($_imageUrl);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepTransparency(true);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($_imageResized);
        }
        return Mage::getBaseUrl("media")."resized/".(int)$width."x".(int)$height."/".$image;
    }

    public function checkModuleInstalled( $module_name = "") {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        if($modulesArray) {
            $tmp = array();
            foreach($modulesArray as $key=>$value) {
                $tmp[$key] = $value;
            }
            $modulesArray = $tmp;
        }

        if(isset($modulesArray[$module_name])) {

            if((string)$modulesArray[$module_name]->active == "true") {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }

}