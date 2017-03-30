<?php
 /*------------------------------------------------------------------------
  # VenusTheme Block Builder Module
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_BlockBuilder_Helper_Data extends Mage_Core_Helper_Abstract {
    protected static $_list_dragable_blocks = array("Ves_",
                                                    "Mage_Page_Block_Html_Header",
                                                    "Mage_Page_Block_Html_Breadcrumbs",
                                                    "Mage_Reports_Block_Product_Viewed",
                                                    "Mage_Catalog_Block_Layer_View",
                                                    "Mage_Tag_Block_Popular",
                                                    "Mage_Catalog_Block_Category_View",
                                                    "Mage_Catalog_Block_Product_List",
                                                    "Mage_Catalog_Block_Product_Compare_Sidebar",
                                                    "Mage_Poll_Block_ActivePoll",
                                                    "Mage_Paypal_Block_Logo",
                                                    "Mage_Tag_Block_Popular",
                                                    "Mage_Page_Block_Html_Footer"
                                                    );
    protected static $_list_editable_blocks = array("Ves_",
                                                    "Mage_Page_Block_Html_Header",
                                                    "Mage_Page_Block_Html_Breadcrumbs",
                                                    "Mage_Reports_Block_Product_Viewed",
                                                    "Mage_Catalog_Block_Layer_View",
                                                    "Mage_Tag_Block_Popular",
                                                    "Mage_Catalog_Block_Category_View",
                                                    "Mage_Catalog_Block_Product_List",
                                                    "Mage_Catalog_Block_Product_Compare_Sidebar",
                                                    "Mage_Poll_Block_ActivePoll",
                                                    "Mage_Paypal_Block_Logo",
                                                    "Mage_Tag_Block_Popular",
                                                    "Mage_Page_Block_Html_Footer"
                                                    );

    public function getShortCode($key, $alias = "", $settings = array()) {
        if($key) {
            $options = array();
            if($settings) {
                foreach($settings as $k => $v) {
                    if(trim($v)) {
                        $options[] = trim($k). '="'.trim($v).'"';
                    }
                }
            }
            $block_id = '';
            if($alias) {
                $block_id = 'block_id="'.trim($alias).'"';
            }
            return '{{widget type="'.trim($key).'" '.$block_id.' '.implode(" ", $options).'}}';
        }
        return  ;
    }

    public function generateBlockBuilder($alias = "") {
        if($alias) {
            $short_code = $this->getShortCode("ves_blockbuilder/widget_builder", $alias);
            $processor = Mage::helper('cms')->getPageTemplateProcessor();
            return $processor->filter($short_code);
        }
        return ;
    }

     public function runShortcode($short_code = "") {
        if($short_code) {
            $processor = Mage::helper('cms')->getPageTemplateProcessor();
            return $processor->filter($short_code);
        }
        return ;
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

    public function getWidgetFormUrl($target_id = "") {
        $params = array();
        if($target_id) {
            $params['widget_target_id'] = $target_id;
        }

        $admin_route = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $admin_route = $admin_route?$admin_route:"admin";

        $url = Mage::getSingleton('adminhtml/url')->getUrl('*/widget/loadOptions', $params);
        $url = str_replace("/blockbuilder/","/{$admin_route}/", $url);
        return $url;
    }

    public function getListWidgetsUrl($target_id = "") {
        //return Mage::helper("adminhtml")->getUrl("*/*/listwidgets"); 
        $params = array();
        if($target_id) {
            $params['widget_target_id'] = $target_id;
        }

        $admin_route = Mage::getConfig()->getNode('admin/routers/adminhtml/args/frontName');
        $admin_route = $admin_route?$admin_route:"admin";
        
        $url = Mage::getSingleton('adminhtml/url')->getUrl('*/widget/index', $params);
        $url = str_replace("/blockbuilder/","/{$admin_route}/", $url);
        return $url;
    }

    public function getWidgetDataUrl() {
        return Mage::helper("adminhtml")->getUrl("*/*/widgetdata");
    }

    public function getImageUrl($secure = false) {
        if($secure) {
            return str_replace(array('index.php/', 'index.php'), '', Mage::getBaseUrl('media', true));
        } else {
            return str_replace(array('index.php/', 'index.php'), '', Mage::getBaseUrl('media', false));
        }
        
    }

    /**
     * Handles CSV upload
     * @return string $filepath
     */
    public function getUploadedFile( $profile = "", $is_pagebuilder = false, $sub_folder = "") {
        $filepath = null;

        if(isset($_FILES['importfile']['name']) and (file_exists($_FILES['importfile']['tmp_name']))) {
            try {

                $uploader = new Varien_File_Uploader('importfile');
                $uploader->setAllowedExtensions(array('csv','txt', 'json', 'xml')); // or pdf or anything
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::helper('ves_blockbuilder')->getImportPath();
                $file_type = "csv";
                if($_FILES['importfile']['type'] == "application/json") {
                    $file_type = "json";
                }
                $uploader->save($path, "ves_pagebuilder_sample_data.".$file_type);
                $filepath = $path . "ves_pagebuilder_sample_data.".$file_type;

            } catch(Exception $e) {
                // log error
                Mage::logException($e);
            } // end if

        } // end if
        elseif($profile) {
            $profile = $sub_folder?$sub_folder."/".$profile:$profile;
            if($is_pagebuilder) {
                $filepath = Mage::getBaseDir('media')."/pagebuilder/page_profiles/".$profile;
                if(!file_exists($filepath)) {
                    $filepath = false;
                }
            } else {

                $filepath = Mage::getBaseDir('media')."/pagebuilder/block_profiles/".$profile;
                if(!file_exists($filepath)) {
                    $filepath = false;
                }
            }
            
        }
        return $filepath;

    }

    public function getImportPath($theme = ""){
        $path = Mage::getBaseDir('var') . DS . 'cache'.DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
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

    /**
     *
     */
    public function getFileList( $path , $e=null, $filter_pattern = "" ) {
        $output = array();
        $directories = glob( $path.'*'.$e );
        if($directories) {
            foreach( $directories as $dir ){
                if($filter_pattern) {
                    $file_name = basename( $dir );
                    if(strpos($file_name, $filter_pattern) !== false) {
                        $output[] = basename( $dir );
                    }
                    
                } else {
                    $output[] = basename( $dir );
                }
                
            }  
        }
                 
        
        return $output;
    }

     /**
     *
     */
    public function getBlockProfiles() {
        $path = Mage::getBaseDir('media')."/pagebuilder/block_profiles/";
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        $file_type = ".csv";
        $output = array();
        if($dirs) {
            $output["general"] = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
            foreach($dirs as $dir) {
                $file_name = basename( $dir );
                $tmp_path = $path.$file_name."/";
                if($tmp_output = Mage::helper("ves_blockbuilder")->getFileList($tmp_path, $file_type)){
                    $output[$file_name] = $tmp_output;
                }

            }

        } else {
            $output = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
        }

        return $output;
    }
    /**
     *
     */
    public function getPageProfiles() {
        $path = Mage::getBaseDir('media')."/pagebuilder/page_profiles/";
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        $file_type = ".csv";
        $output = array();
        if($dirs) {
            $output["general"] = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
            foreach($dirs as $dir) {
                $file_name = basename( $dir );
                $tmp_path = $path.$file_name."/";
                if($tmp_output = Mage::helper("ves_blockbuilder")->getFileList($tmp_path, $file_type)){
                    $output[$file_name] = $tmp_output;
                }

            }
        } else {
            $output = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
        }
        
        return $output;
    }

    /**
     *
     */
    public function getProductProfiles() {
        $path = Mage::getBaseDir('media')."/pagebuilder/product_profiles/";
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        $file_type = ".csv";
        $output = array();
        if($dirs) {
            $output["general"] = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
            foreach($dirs as $dir) {
                $file_name = basename( $dir );
                $tmp_path = $path.$file_name."/";
                if($tmp_output = Mage::helper("ves_blockbuilder")->getFileList($tmp_path, $file_type)){
                    $output[$file_name] = $tmp_output;
                }

            }
        } else {
            $output = Mage::helper("ves_blockbuilder")->getFileList($path, $file_type);
        }
        
        return $output;
    }

    public function getBlockProfilePath( $profile = "") {
        $path = Mage::getBaseDir('media')."/pagebuilder/block_profiles/".$profile.".csv";

        if(file_exists($path)) {
            return $path;
        }

        return false;
        
    }

    public function getPageProfilePath( $profile = "") {
        $path = Mage::getBaseDir('media')."/pagebuilder/page_profiles/".$profile.".csv";

        if(file_exists($path)) {
            return $path;
        }

        return false;
    }

    public function getProductProfilePath( $profile = "") {
        $path = Mage::getBaseDir('media')."/pagebuilder/product_profiles/".$profile.".csv";

        if(file_exists($path)) {
            return $path;
        }

        return false;
    }

    /**
     *
     */
    public function writeToCache( $folder, $file, $value, $e='css' ){
        $file = $folder  . preg_replace('/[^A-Z0-9\._-]/i', '', $file).'.'.$e ;
        if (file_exists($file)) {
            unlink($file);
        }

        $flocal = new Varien_Io_File();
        $flocal->open(array('path' => $folder));
        $flocal->write($file, $value);
        $flocal->close();
        @chmod($file, 0755);
    }
    
    public function autoBackupLayoutProfile($data = array(), $folder = "vespagebuilder") {
        $backup_dir = Mage::getBaseDir('var')."/{$folder}/";
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir,0777,true);
        }
        if($data) {
            $filename = isset($data['alias'])?$data['alias']:(rand().time());
            $filename = $backup_dir.$filename.".json";
            $content = isset($data['params'])?$data['params']:"";
            if($filename && $content) {
                if (file_exists($filename)) {
                    unlink($filename);
                }
                file_put_contents($filename, $content);
                @chmod($filename, 0777);
                return $filename;
            }
        }
        return false;
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

    public function getBackupLayouts($folder_name = "vespagebuilder") {

        $file_ext = ".json";
        $folder = Mage::getBaseDir('var')."/{$folder_name}/";

        $dirs = glob( $folder.'*'.$file_ext );
        $result = array();
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
 protected function writeFile($content,$file,$type)
    {
        $dir  = Mage::getBaseDir('media') . DS . 'ves' . DS . $type;
        $filename = $dir . DS . $file;
        if (!is_dir($dir)) {
            mkdir($dir,0777,true);
        }
        file_put_contents($filename, $content);
        chmod($filename, 0777);

        $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/ves/'.$type . '/' . $file;
        return $path;
    }
    public function getThemeCustomizePath($theme = "") {
        $tmp_theme = explode("/", $theme);
        if(count($tmp_theme) == 1) {
            $theme = "base/".$theme;
        }
        $customize_path = Mage::getBaseDir('skin')."/frontend/".$theme."/css/customize/";
        if(!file_exists($customize_path)) {
            $file = new Varien_Io_File();
            $file->mkdir($customize_path);
            $file->close();
        }
        return $customize_path;
    }


    public function getSelectorGroups () {
        return array(   'body' => Mage::helper("ves_blockbuilder")->__('Body Content'),
                        'topbar' => Mage::helper('ves_blockbuilder')->__('TopBar'),
                        'header-main' => Mage::helper('ves_blockbuilder')->__('Header'),
                        'mainmenu' => Mage::helper('ves_blockbuilder')->__('MainMenu'),
                        'footer' => Mage::helper('ves_blockbuilder')->__('Footer'),
                        'footer-top' => Mage::helper('ves_blockbuilder')->__('Footer Top'),
                        'footer-center' => Mage::helper('ves_blockbuilder')->__('Footer Center'),
                        'footer-bottom' => Mage::helper('ves_blockbuilder')->__('Footer Bottom'),
                        'product' => Mage::helper('ves_blockbuilder')->__('Products'),
                        'powered' => Mage::helper('ves_blockbuilder')->__('Powered'),
                        'module-sidebar' => Mage::helper('ves_blockbuilder')->__('Modules in Sidebar'),
                        'module-block' => Mage::helper('ves_blockbuilder')->__('Module Blocks'),
                        'cart-block' => Mage::helper('ves_blockbuilder')->__('Cart Blocks'),
                        'checkout' => Mage::helper('ves_blockbuilder')->__('Checkout'),
                        'custom' => Mage::helper('ves_blockbuilder')->__('Custom')
                        );
    }

    public function getSelectorTypes () {
        return array('raw-text' => Mage::helper("ves_blockbuilder")->__('Text'),
                    'text' => Mage::helper("ves_blockbuilder")->__('Color Input'),
                    'image' => Mage::helper('ves_blockbuilder')->__('Image Pattern'),
                    'fontsize' => Mage::helper('ves_blockbuilder')->__('Font Size'),
                    'borderstyle' => Mage::helper('ves_blockbuilder')->__('Border Style'),
                    'textarea' => Mage::helper("ves_blockbuilder")->__('Custom Css Code'),
                );
    }

    public function checkDragableBlock( $block_class_name = "") {
        $exists = in_array($block_class_name, self::$_list_dragable_blocks)?true:false;
        if(!$exists){
            foreach(self::$_list_dragable_blocks as $item) {
                if(strpos($item, $block_class_name)) {
                    $exists = true;
                    break;
                }
            }
        }
        return $exists;
    }

    public function checkEditableBlock( $block_class_name = "") {
        $exists = isset(self::$_list_editable_blocks[ $block_class_name])?true:false;
        if(!$exists){
            foreach(self::$_list_editable_blocks as $item) {
                if(strpos($item, $block_class_name)) {
                    $exists = true;
                    break;
                }
            }
        }
        return $exists;
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

    public function objToArray($obj)
    {
        if (is_object($obj)) $obj = (array)$obj;
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = Mage::helper('ves_blockbuilder')->objToArray($val);
            }
        } else {
            $new = $obj;
        }
        return $new;
    }

    // HTML Minifier
    public function minify_html($input) {
        if(trim($input) === "") return $input;
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));
        // Minify inline CSS declaration(s)
        if(strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . $this->minify_css($matches[3]) . $matches[2];
            }, $input);
        }
        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
        $input);
    }
    // CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
    public function minify_css($input) {
        if(trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
        $input);
    }
}

?>