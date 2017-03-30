<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */
class Ves_Base_Helper_Data extends Mage_Core_Helper_Abstract{

	public function renderMediaChooser(Varien_Data_Form_Element_Abstract $element) {
		if (Mage::getSingleton('admin/session')->isAllowed('cms/media_gallery')) {

            $layout = $element->getForm()->getParent()->getLayout();
            $id = $element->getHtmlId();

            if ($url = $element->getValue()) {
                $linkStyle = "display:inline;";

                if(!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                    $url = Mage::getBaseUrl('media') . $url;
                }
            }else{
                $linkStyle = "display:none;";
                $url = "#";
            }

            $hiddenField = '<input type="hidden" name="hidden_file" id="hidden_file_'.$id.'" class="hidden-file-path" value=""/>';
            $imagePreview = '<a id="' . $id . '_link" class="image-preview-link" href="' . $url . '" style="text-decoration: none; ' . $linkStyle . '"'
                . ' onclick="imagePreview(\'' . $id . '_image\'); return false;">'
                . ' <img src="' . $url . '" id="' . $id . '_image" title="' . $element->getValue() . '"'
                . ' alt="' . $element->getValue() . '" height="30" class="small-image-preview v-middle"/>'
                . ' </a>';

            $selectButtonId = 'add-image-' . mt_rand();
            $chooserUrl = Mage::getUrl('adminhtml/cms_wysiwyg_images_chooser/index', array('target_element_id' => $id));
            $label = ($element->getValue()) ? $this->__('Change Image') : $this->__('Select Image');


            // Select/Change Image Button
            $chooseButton = $layout->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('add-image')
                ->setId($selectButtonId)
                ->setLabel($label)
                ->setOnclick('openEfinder(this, \'hidden_file_'.$id.'\', \'#'.$id.'\', changeElFieldImage)')
                ->setDisabled($element->getReadonly())
                ->setStyle('display:inline;margin-top:7px');

            // Remove Image Button
            $onclickJs = '
                document.getElementById(\''. $id .'\').value=\'\';
                document.getElementById(\'hidden_file_'. $id .'\').value=\'\';
                if(document.getElementById(\''. $id .'_image\')){
                    document.getElementById(\''. $id .'_image\').parentNode.style.display = \'none\';
                }
                document.getElementById(\''. $selectButtonId .'\').innerHTML=\'<span><span><span>' . addslashes($this->__('Select Image')) . '</span></span></span>\';
            ';

            $removeButton = $layout->createBlock('adminhtml/widget_button')
                ->setType('button')
                ->setClass('delete')
                ->setLabel($this->__('Remove Image'))
                ->setOnclick($onclickJs)
                ->setDisabled($element->getReadonly())
                ->setStyle('margin-top:7px');


            $wrapperStart = '<div id="buttons_' . $id . '" class="buttons-set" style=" width: 325px;">';
            $wrapperEnd = '</div>';

            // Add our custom HTML after the form element
            $element->setAfterElementHtml($wrapperStart . $hiddenField. $imagePreview . $chooseButton->toHtml() . $removeButton->toHtml() . $wrapperEnd);
        }

        return $element;
	}

    public function resizeImage($image, $width = 100, $height = 100, $qualtity = 100, $keep_ratio = true){
        $parsed = parse_url($image);
        if (!empty($parsed['scheme'])) {
            return $image;
        }
        
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
            $imageObj->quality($qualtity);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepTransparency(true);
            $imageObj->keepFrame(FALSE);
            if($keep_ratio) {
              $imageObj->keepAspectRatio(TRUE);
              $imageObj->resize($width, $height);
            } else {
              $imageObj->keepAspectRatio(FALSE);
              $currentRatio = $imageObj->getOriginalWidth() / $imageObj->getOriginalHeight();
              $targetRatio = $width / $height;
              if ($targetRatio > $currentRatio) {
                      $imageObj->resize($width, null);
                  } else {
                      $imageObj->resize(null, $height);
                  }

                  $diffWidth  = $imageObj->getOriginalWidth() - $width;
                  $diffHeight = $imageObj->getOriginalHeight() - $height;

                  /*POSTION Bottom*/
                  $_topRate = 1;
                  $_bottomRate = 0;
                  /*
                  //POSTION Top
                  $_topRate = 0;
                  $_bottomRate = 1;
                  */
                  /*
                  //POSTION Center
                  $_topRate = 0.5;
                  $_bottomRate = 0.5;
                  */

                  $imageObj->crop(
                      floor($diffHeight * $_topRate),
                      floor($diffWidth / 2),
                      ceil($diffWidth / 2),
                      ceil($diffHeight * $_bottomRate)
                  );
            }
            $imageObj->save($_imageResized);
        }
        return Mage::getBaseUrl("media")."resized/".(int)$width."x".(int)$height."/".$image;
    }

    /*
    * Recursively searches and replaces all occurrences of search in subject values replaced with the given replace value
    * @param string $search The value being searched for
    * @param string $replace The replacement value
    * @param array $subject Subject for being searched and replaced on
    * @return array Array with processed values
    */
    public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
        return $subject;
    
        foreach($subject as $key => $value)
        if(is_string($value))
        $subject[$key] = str_replace($search, $replace, $value);
        elseif(is_array($value))
        $subject[$key] = self::recursiveReplace($search, $replace, $value);
    
        return $subject;
    }
    
    public function getCSSAnimations(){
        return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('No Animation')),
                  array('value' => "bounce", 'label'=>Mage::helper('adminhtml')->__('bounce')),
                  array('value' => "flash", 'label'=>Mage::helper('adminhtml')->__('flash')),
                  array('value' => "pulse", 'label'=>Mage::helper('adminhtml')->__('pulse')),
                  array('value' => "rubberBand", 'label'=>Mage::helper('adminhtml')->__('rubberBand')),
                  array('value' => "shake", 'label'=>Mage::helper('adminhtml')->__('shake')),
                  array('value' => "swing", 'label'=>Mage::helper('adminhtml')->__('swing')),
                  array('value' => "tada", 'label'=>Mage::helper('adminhtml')->__('tada')),
                  array('value' => "wobble", 'label'=>Mage::helper('adminhtml')->__('wobble')),
                  array('value' => "bounceIn", 'label'=>Mage::helper('adminhtml')->__('bounceIn')),
                  array('value' => "bounceInDown", 'label'=>Mage::helper('adminhtml')->__('bounceInDown')),
                  array('value' => "bounceInLeft", 'label'=>Mage::helper('adminhtml')->__('bounceInLeft')),
                  array('value' => "bounceInRight", 'label'=>Mage::helper('adminhtml')->__('bounceInRight')),
                  array('value' => "bounceInUp", 'label'=>Mage::helper('adminhtml')->__('bounceInUp')),
                  array('value' => "fadeIn", 'label'=>Mage::helper('adminhtml')->__('fadeIn')),
                  array('value' => "fadeInDown", 'label'=>Mage::helper('adminhtml')->__('fadeInDown')),
                  array('value' => "fadeInDownBig", 'label'=>Mage::helper('adminhtml')->__('fadeInDownBig')),
                  array('value' => "fadeInLeft", 'label'=>Mage::helper('adminhtml')->__('fadeInLeft')),
                  array('value' => "fadeInLeftBig", 'label'=>Mage::helper('adminhtml')->__('fadeInLeftBig')),
                  array('value' => "fadeInRight", 'label'=>Mage::helper('adminhtml')->__('fadeInRight')),
                  array('value' => "fadeInRightBig", 'label'=>Mage::helper('adminhtml')->__('fadeInRightBig')),
                  array('value' => "fadeInUp", 'label'=>Mage::helper('adminhtml')->__('fadeInUp')),
                  array('value' => "fadeInUpBig", 'label'=>Mage::helper('adminhtml')->__('fadeInUpBig')),
                  array('value' => "flip", 'label'=>Mage::helper('adminhtml')->__('flip')),
                  array('value' => "flipInX", 'label'=>Mage::helper('adminhtml')->__('flipInX')),
                  array('value' => "flipInY", 'label'=>Mage::helper('adminhtml')->__('flipInY')),
                  array('value' => "lightSpeedIn", 'label'=>Mage::helper('adminhtml')->__('lightSpeedIn')),
                  array('value' => "rotateIn", 'label'=>Mage::helper('adminhtml')->__('rotateIn')),
                  array('value' => "rotateInDownLeft", 'label'=>Mage::helper('adminhtml')->__('rotateInDownLeft')),
                  array('value' => "rotateInDownRight", 'label'=>Mage::helper('adminhtml')->__('rotateInDownRight')),
                  array('value' => "rotateInUpLeft", 'label'=>Mage::helper('adminhtml')->__('rotateInUpLeft')),
                  array('value' => "rotateInUpRight", 'label'=>Mage::helper('adminhtml')->__('rotateInUpRight')),
                  array('value' => "hinge", 'label'=>Mage::helper('adminhtml')->__('hinge')),
                  array('value' => "rollIn", 'label'=>Mage::helper('adminhtml')->__('rollIn')),
                  array('value' => "zoomIn", 'label'=>Mage::helper('adminhtml')->__('zoomIn')),
                  array('value' => "zoomInDown", 'label'=>Mage::helper('adminhtml')->__('zoomInDown')),
                  array('value' => "zoomInLeft", 'label'=>Mage::helper('adminhtml')->__('zoomInLeft')),
                  array('value' => "zoomInRight", 'label'=>Mage::helper('adminhtml')->__('zoomInRight')),
                  array('value' => "zoomInUp", 'label'=>Mage::helper('adminhtml')->__('zoomInUp'))
                  );
    }

    public function getConnectorUrl() {
        //return Mage::helper("adminhtml")->getUrl("*/*/listwidgets"); 
        $params = array();

        $admin_route = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $admin_route = $admin_route?$admin_route:"admin";
        
        $url = Mage::getSingleton('adminhtml/url')->getUrl('*/adminhtml_baseconnector/index', $params);
        $url = str_replace("/blockbuilder/","/{$admin_route}/", $url);
        return $url;
    }

    public function getDefaultProductLayout() {
        $result = "";
        $folder = Mage::getBaseDir('media')."/pagebuilder/product_profiles/";
        $filepath = $folder."default_layout.json";
        if(!file_exists($filepath)) {
            $filepath = $folder."default.json";
            if(!file_exists($filepath)) {
                $filepath = false;
            }
        }
        $result = $this->readSampleFile( $folder, $filepath );
        return $result;
    }

    public function readSampleFile($folder, $filepath = "") {
        $result = "";
        if($filepath) {
            $flocal = new Varien_Io_File();
            $flocal->open(array('path' => $folder));
            $result = $flocal->read($filepath);
        }
        return $result;
    }

    public function getSampleLayoutParams() {
        $result = array();
        $file_ext = ".json";
        $folder = Mage::getBaseDir('media')."/pagebuilder/";

        if(1 ==  Mage::registry('is_productbuilder')){ //Load sample profile of product when we are managing product layout builder
          $folder .= "product_profiles/";
        } elseif(1 == Mage::registry('is_pagebuilder')){ //Load sample profile of page when we are managing page layout builder
          $folder .= "page_profiles/";
        } else { //Load sample profile of block builder
          $folder .= "block_profiles/";
        }

        $dirs = glob( $folder.'*'.$file_ext );
        if($dirs) { //load 
            foreach($dirs as $dir) {
                $file_name = basename( $dir );
                $filepath = $folder.$file_name;
                $file_name = str_replace(array(" ","."), "-", $file_name);
                $result[$file_name] = $this->readSampleFile($folder, $filepath);
            }
        }

        return $result;
    }

    public function getBackupLayouts($folder_name = "vespagebuilder") {
        $result = array();
        $file_ext = ".json";
        $folder = Mage::getBaseDir('var')."/{$folder_name}/";

        $dirs = glob( $folder.'*'.$file_ext );
        if($dirs) { //load 
            foreach($dirs as $dir) {
                $file_name = basename( $dir );
                $filepath = $folder.$file_name;
                $file_name = str_replace(array(" ","."), "-", $file_name);
                $result[$file_name] = $this->readSampleFile($folder, $filepath);
            }
        }

        return $result;
    }

    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
            $text = ($is_striped==true)?strip_tags($text):$text;
            if(strlen($text) <= $length){
                return $text;
            }
            $text = substr($text,0,$length);
            $pos_space = strrpos($text,' ');
            return substr($text,0,$pos_space).$replacer;
    }
    
}