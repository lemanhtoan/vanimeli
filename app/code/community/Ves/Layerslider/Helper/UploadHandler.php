<?php

class Ves_Layerslider_Helper_UploadHandler extends Mage_Core_Helper_Abstract
{ 
	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
	private $uploadName;

    protected $_replace_mime_types = array('data:image/jpeg;base64,',
                                            'data:image/gif;base64,',
                                            'data:image/png;base64,',
                                            'data:image/bmp;base64,',
                                            'data:image/tiff;base64,',
                                            'image/vnd.microsoft.icon;base64,');

   public function saveImage($base64img, $file_name = "", $show_full_path = false){
        $base64img = str_replace($this->_replace_mime_types, '', $base64img);
        $base_dir = Mage::helper("ves_layerslider")->getImageBaseDir();
        $this->_createDestinationFolder($base_dir);
        
        $file_name = $file_name?$file_name:"";
        $file = $base_dir . $file_name;
        if($file_name && !file_exists($file)) {
            $data = base64_decode($base64img);
            file_put_contents($file, $data); 
        }
        if($show_full_path) {
            return Mage::helper("ves_layerslider")->getImageBaseUrl().$file_name;
        } else {
            return $file_name;
        }
        
    }

    public function getImage($file_name ){
        $base_dir = Mage::helper("ves_layerslider")->getImageBaseDir();
        $this->_createDestinationFolder($base_dir);

        $file = $base_dir . $file_name;
        if(file_exists($file) && is_file($file)) {
            $mime = $this->getMimeType($file);

            $data = file_get_contents($file);

            $base64img = base64_encode($data);

            $base64img = "data:".$mime.";base64,".$base64img;
            
            return $base64img;  
        }
        return ;
    }

    private function getMimeType($filename) {
        if(!is_file($filename)) {
            return "";
        }
        if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filename);
            finfo_close($finfo);
        } else {
            // Get the mime type
            if (function_exists('exif_imagetype')) {
                $imageType = exif_imagetype($filename);
            } else {
                $imageType = getimagesize($filename);
                $imageType = $imageType[2];
            }
            $mime = image_type_to_mime_type($imageType);
        }
       
        return $mime;
    }

    private function _createDestinationFolder($destinationFolder)
    {
        if (!$destinationFolder) {
            return $this;
        }

        if (substr($destinationFolder, -1) == DIRECTORY_SEPARATOR) {
            $destinationFolder = substr($destinationFolder, 0, -1);
        }

        if (!(@is_dir($destinationFolder) || @mkdir($destinationFolder, 0777, true))) {
            throw new Exception("Unable to create directory '{$destinationFolder}'.");
        }
        return $this;
    }
}
