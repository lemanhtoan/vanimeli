<?php

class Ves_Megamenu_Helper_Data extends Mage_Core_Helper_Abstract {
    private $widgets = array();
    public function checkAvaiable($controller_name = "") {
        $arr_controller = array("Mage_Cms",
            "Mage_Catalog",
            "Mage_Tag",
            "Mage_Checkout",
            "Mage_Customer",
            "Mage_Wishlist",
            "Mage_CatalogSearch");
        if (!empty($controller_name)) {
            if (in_array($controller_name, $arr_controller)) {
                return true;
            }
        }
        return false;
    }
    
    public function checkMenuItem($menu_name = "", $config = array()) {
        if (!empty($menu_name) && !empty($config)) {
            $menus = isset($config["menuAssignment"]) ? $config["menuAssignment"] : "all";
            $menus = explode(",", $menus);
            if (in_array("all", $menus) || in_array($menu_name, $menus)) {
                return true;
            }
        }
        return false;
    }

    public function getListMenu() {
        $arrayParams = array(
            'all' => Mage::helper('adminhtml')->__("All"),
            'Mage_Cms_index' => Mage::helper('adminhtml')->__("Mage Cms Index"),
            'Mage_Cms_page' => Mage::helper('adminhtml')->__("Mage Cms Page"),
            'Mage_Catalog_category' => Mage::helper('adminhtml')->__("Mage Catalog Category"),
            'Mage_Catalog_product' => Mage::helper('adminhtml')->__("Mage Catalog Product"),
            'Mage_Customer_account' => Mage::helper('adminhtml')->__("Mage Customer Account"),
            'Mage_Wishlist_index' => Mage::helper('adminhtml')->__("Mage Wishlist Index"),
            'Mage_Customer_address' => Mage::helper('adminhtml')->__("Mage Customer Address"),
            'Mage_Checkout_cart' => Mage::helper('adminhtml')->__("Mage Checkout Cart"),
            'Mage_Checkout_onepage' => Mage::helper('adminhtml')->__("Mage Checkout"),
            'Mage_CatalogSearch_result' => Mage::helper('adminhtml')->__("Mage Catalog Search"),
            'Mage_Tag_product' => Mage::helper('adminhtml')->__("Mage Tag Product")
        );
        return $arrayParams;
    }

    function get($attributes) {
        $data = array();
        $arrayParams = array('enable_jquery',
            'show',
            'enable_cache',
            'cache_lifetime',
            'topCategory',
            'responsive',
            'topTheme',
            'topModuleClass',
            'topMenuItemWidth',
            'topBlockPosition',
            'topCustomBlockPosition',
            'topBlockDisplay',
            'topMenuAssignment',
            'showLeft',
            'leftMenuTitle',
            'showLeftTitle',
            'leftTheme',
            'leftModuleClass',
            'leftMenuItemWidth',
            'leftBlockPosition',
            'leftCustomBlockPosition',
            'leftBlockDisplay',
            'leftMenuAssignment',
            'overTime',
            'outTimeDuration',
            'showDelay',
            'hideDelay'
        );

        foreach ($arrayParams as $var) {
            $tags = array('ves_megamenu', 'top_menu_setting', 'left_menu_setting', 'effect_setting');
            foreach ($tags as $tag) {
                if (Mage::getStoreConfig("ves_megamenu/$tag/$var") != "") {
                    $data[$var] = Mage::getStoreConfig("ves_megamenu/$tag/$var");
                }
            }
            if (isset($attributes[$var])) {
                $data[$var] = $attributes[$var];
            }
        }
        return $data;
    }

    public function getImageUrl($url = null) {
        return Mage::getSingleton('ves_megamenu/config')->getBaseMediaUrl() . $url;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

    public function getPositionType($default = "top") {
        $position_type = Mage::getSingleton('admin/session')->getMegaPositionType();
        $position_type = empty($position_type) ? $default : $position_type;
        return $position_type;
    }

     public function getElementStores($name, $element_name, $value = array(), $attr = ""){
        
        $html = '<select multiple="multiple" class="select multiselect" size="10" title="Store View" name="'.$element_name.'" id="'.$name.'"'.$attr.'>';
        if(empty($value) || in_array(0,$value)){
            $html .= '<option value="0" selected="selected">'.Mage::helper("ves_megamenu")->__("All Store Views").'</option>';
        }else{
            $html .= '<option value="0">'.Mage::helper("ves_megamenu")->__("All Store Views").'</option>';
        }
        foreach (Mage::app()->getWebsites() as $website) {
            $html .= '<optgroup label="'.$website->getName().'"></optgroup>';
            foreach ($website->getGroups() as $group) {
                $html .= '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;'.$group->getName().'">';
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    //$store is a store object
                    $store_id = $store->getId();
                    if(in_array($store_id, $value)){
                        $html .= '<option value="'.$store->getId().'" selected="selected">&nbsp;&nbsp;&nbsp;&nbsp;'.$store->getName().'</option>';
                    }else{
                        $html .= '<option value="'.$store->getId().'">&nbsp;&nbsp;&nbsp;&nbsp;'.$store->getName().'</option>';  
                    }
                    
                }
                $html .= '</optgroup>';
            }
        }
        $html .= '</select>';
        return $html;
    }
    public function getElementEditor($textarea_name, $element_name, $content = "", $attr = ""){
        $attr = !empty($attr)?$attr:'style="width:500px; height:300px;" rows="2" cols="15"';
        $html ='<span class="field-row">
                    <div id="buttons'.$textarea_name.'" class="buttons-set">
                        <button type="button" class="scalable show-hide" style="" id="toggle'.$textarea_name.'"><span><span><span>'.Mage::helper('ves_megamenu')->__('Show / Hide Editor').'</span></span></span></button>
                        <button type="button" class="scalable add-widget plugin" onclick="widgetTools.openDialog(\''.$this->getWidgetLink(array('widget_target_id'=>$textarea_name)).'\')" style="display:none"><span><span><span>'.Mage::helper('ves_megamenu')->__('Insert Widget...').'</span></span></span></button>
                        <button type="button" class="scalable add-image plugin" onclick="MediabrowserUtility.openDialog(
                        \''.$this->getImagesLink(array('target_element_id'=>$textarea_name)).'\')" style="display:none"><span><span><span>'.Mage::helper('ves_megamenu')->__('Insert Image...').'</span></span></span></button>

                        <button type="button" class="scalable add-variable plugin" onclick="MagentovariablePlugin.loadChooser(\''.$this->getVariablesLink().'\', \''.$textarea_name.'\');" style="display:none;"><span><span><span>'.Mage::helper('ves_megamenu')->__('Insert Variable...').'</span></span></span></button></div>

                    <textarea id="'.$textarea_name.'" class="texteditor" class="textarea " '.$attr.' name="'.$element_name.'">'.$content.'</textarea>
 
                        <script type="text/javascript">
                            //<![CDATA[
                            renderTextEditor("'.$textarea_name.'");
                            //]]>
                        </script>
                    </span>';
        return $html;
    }
    public function initTextEditor(){
        $texteditor_links = array("directive" => $this->getDirectivesLink(),

                         "popup_css" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS)."mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css",

                         "content_css" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css',

                         "magentovariable" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js',

                         "variables" => $this->getCustomLink('*/system_variable/wysiwygPlugin'),

                         "browse_images" => $this->getCustomLink('*/cms_wysiwyg_images/index'),

                         "widget_js" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',

                         "widget_images" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/images/widget/',

                         "widget_window" => $this->getCustomLink('*/widget/index')
                         );
        $html = '';
        ob_start();
        ?>
        <script type="text/javascript">
        //<![CDATA[
        function renderTextEditor(textarea_id){
            if ("undefined" != typeof(Translator)) {
                Translator.add({"Insert Image...":"Insert Image...","Insert Media...":"Insert Media...","Insert File...":"Insert File..."});
            }
            wysiwygblock_content = new tinyMceWysiwygSetup(textarea_id, {"enabled":true,
                "hidden" : false,
                "use_container" : false,
                "add_variables" : true,
                "add_widgets" : true,
                "no_display" : false,
                "translator" : {},
                "encode_directives" : true,
                "directives_url" : "<?php echo $texteditor_links['directive']; ?>",
                "popup_css" : "<?php echo $texteditor_links['popup_css']; ?>",
                "content_css" : "<?php echo $texteditor_links['content_css']; ?>",
                "width" : "100%",
                "plugins" : [{"name":"magentovariable","src":"<?php echo $texteditor_links['magentovariable'];?>",
                            "options":{"title":"Insert Variable...","url":"<?php echo $texteditor_links["variables"]; ?>",
                            "onclick":{"search":["html_id"],"subject":"MagentovariablePlugin.loadChooser('<?php echo $texteditor_links["variables"]; ?>', '{{html_id}}');"},
                            "class":"add-variable plugin"}}],
                "directives_url_quoted" : "<?php echo $texteditor_links['directive']; ?>",
                "add_images" : true,
                "files_browser_window_url" : "<?php echo $texteditor_links['browse_images']; ?>",
                "files_browser_window_width" : 1000,
                "files_browser_window_height" : 600,
                "widget_plugin_src" : "<?php echo $texteditor_links['widget_js'] ?>",
                "widget_images_url" : "<?php echo $texteditor_links['widget_images'] ?>",
                "widget_placeholders" : ["catalog__category_widget_link.gif","catalog__product_widget_link.gif","catalog__product_widget_new.gif","cms__widget_block.gif","cms__widget_page_link.gif","default.gif","reports__product_widget_compared.gif","reports__product_widget_viewed.gif"],
                "widget_window_url" : "<?php echo $texteditor_links['widget_window'];?>",
                "firebug_warning_title" : "Warning",
                "firebug_warning_text" : "Firebug is known to make the WYSIWYG editor slow unless it is turned off or configured properly.",
                "firebug_warning_anchor" : "Hide"
            });
            Event.observe(window, "load", wysiwygblock_content.setup.bind(wysiwygblock_content, "exact"));
            editorFormValidationHandler = wysiwygblock_content.onFormValidation.bind(wysiwygblock_content);
            Event.observe("toggle"+textarea_id, "click", wysiwygblock_content.toggle.bind(wysiwygblock_content));
            varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
            varienGlobalEvents.attachEventHandler("tinymceBeforeSetContent", wysiwygblock_content.beforeSetContent.bind(wysiwygblock_content));
            varienGlobalEvents.attachEventHandler("tinymceSaveContent", wysiwygblock_content.saveContent.bind(wysiwygblock_content));
            varienGlobalEvents.clearEventHandlers("open_browser_callback");
            varienGlobalEvents.attachEventHandler("open_browser_callback", wysiwygblock_content.openFileBrowser.bind(wysiwygblock_content));

            if ("undefined" != typeof(Translator)) {
                Translator.add({"Insert Image...":"Insert Image...","Insert Media...":"Insert Media...","Insert File...":"Insert File..."});
            }
            
        }

        //]]>
    </script>
     <script type="text/javascript">
    //<![CDATA[
        openEditorPopup = function(url, name, specs, parent) {
            if ((typeof popups == "undefined") || popups[name] == undefined || popups[name].closed) {
                if (typeof popups == "undefined") {
                    popups = new Array();
                }
                var opener = (parent != undefined ? parent : window);
                popups[name] = opener.open(url, name, specs);
            } else {
                popups[name].focus();
            }
            return popups[name];
        }

        closeEditorPopup = function(name) {
            if ((typeof popups != "undefined") && popups[name] != undefined && !popups[name].closed) {
                popups[name].close();
            }
        }
    //]]>
    </script>

        <?php
        $html = ob_get_contents(); 
        ob_end_clean();
        return $html;
    }
    
    public function buildTree(&$elements, $parentId = 0) {
       $branch = array();

       foreach ($elements as $key=>$element) {
           if ($element['parent_id'] == $parentId) {
               $children = $this->buildTree($elements, $key);
               if ($children) {
                   $element['children'] = $children;
               }
               $branch[$key] = $element;
               //unset($elements[$key]);
           }
       }
       return $branch;
   }

    public function genTreeMenuOption($tree_menus = array(), $value = 0, $level = 0) {
        $html = "";
        $prefix_string = "";
        if($level){
            $prefix_string = "|";
            for($i=0; $i < $level; $i++){
                $prefix_string .= "_";
            }
        }

        foreach ($tree_menus as $menu_id=>$menu) {
            if($menu_id == $value){
                $html .= '<option value="'.$menu_id.'" selected="selected">&nbsp;&nbsp;&nbsp;&nbsp;'.$prefix_string.$menu['title'].'</option>';
            }else{
                $html .= '<option value="'.$menu_id.'">&nbsp;&nbsp;&nbsp;&nbsp;'.$prefix_string.$menu['title'].'</option>';  
            }
            if(isset($menu['children'])){
                $level += 1;
                $html .= $this->genTreeMenuOption($menu['children'], $value, $level);
            }
                    
        }
        return $html;
    }

    public function getElementMenus($name, $element_name, $value = 0, $attr = ""){
        $storeId = Mage::app()->getRequest()->getParam('store_id');
        $storeId = !empty($storeId) ? $storeId : 0;

        $menu_id = Mage::app()->getRequest()->getParam('id');
        $menu_id = !empty($menu_id) ? $menu_id : 0;
        $html = '<select class="select" name="'.$element_name.'" id="'.$name.'"'.$attr.'>';

        $collection = Mage::getModel('ves_megamenu/megamenu')->getCollection();
        //if($storeId){
        $collection->addStoreFilter($storeId);
        //}
        $collection->addOrder("position", "ASC");

        $menus = array();
        $parent_id = 0;
        

        foreach($collection as $i => $menu){
            if($storeId && $menu->getParentId() == 1) {
                $parent_id = 1;
            }
            elseif(0 == $menu->getParentId()){
                $parent_id = $menu->getId();
            }
            elseif(1 == $menu->getParentId()){
                $parent_id = 1;
            }
            $menus[$menu->getId()] = array("title" => $menu->getTitle(),
                                          "parent_id" => $menu->getParentId());
        }

        $menu_tree = $this->buildTree($menus, $parent_id);

        $html .= '<option value="1">'.Mage::helper('ves_megamenu')->__('ROOT').'</option>';
        $html .= $this->genTreeMenuOption($menu_tree, $value, 0);

        $html .= '</select>';
        return $html;
    }
    public function resizeImage($image, $width = 100, $height = 100){
        $width = !$width?100:(int)$width;
        $height = !$height?100:(int)$height;
        
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
    public function getCustomLink($route , $params = array()){
        $link =  Mage::helper("adminhtml")->getUrl($route, $params);
        $link = str_replace("/adminhtml/","/", $link);
        $link = str_replace("/megamenu/","/", $link);
        $link = str_replace("//admin","/admin", $link);
        return $link;
    }
    public function getDirectivesLink($params = array()){
       return $this->getCustomLink("*/cms_wysiwyg/directive", $params);
    }
    public function getVariablesLink($params = array()){
       return $this->getCustomLink("*/system_variable/wysiwygPlugin", $params);
    }
    public function getImagesLink($params = array()){
       return $this->getCustomLink("*/cms_wysiwyg_images/index", $params);
    }
    public function getWidgetLink($params = array()){
        return $this->getCustomLink("*/widget/index", $params);
    }
    public function stringtoURL($string){
        $groups = explode('&',$string);
      
        $list = array();
        foreach($groups as $st){
            list($name,$var) = explode('=',$st);
            $name = "$".$name;
            eval($name."=".$var.";");
        }
        return $list;
    }
    
    /**
     * general function to render FORM 
     *
     * @param String $type is form type.
     * @param Array default data values for inputs.
     *
     * @return Text.
     */
    public function getForm( $type, $data=array() ){

        $method = "getWidget".ucfirst($type).'Form';
        $args = array();
        if( method_exists( $this, $method ) ){
            return $this->{$method}( $args, $data ); 
        }
        return Mage::helper("ves_megamenu")->__( 'Error: no form widget!' );
    }

    /**
     * render widget HTML Form.
     */
    public function getWidgetHtmlForm( $args, $data ){
            
        $fields  = array(
            'html' => array( 'type' => 'texteditor', 'value' => '','lang'=>1, 'values'=>array(),  'attrs' => 'cols="40" rows="6"'  )
        );

        return $this->_renderFormByFields( $fields, $data );
         
    }

    /**
     * render widget HTML Form.
     */
    public function getWidgetProduct_categoryForm( $args, $data ){
            
        $fields  = array(
            'category_id' => array( 'type' => 'tree', 'value' => '' ),
            'limit'      => array( 'type' => 'text', 'value' => ''  ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
         
    }

    /**
     * render widget HTML Form.
     */
    public function getWidgetCategory_listForm( $args, $data ){
        $yesno = array();
        $yesno[] = array(
            'value' => '0',
            'text'  => Mage::helper("ves_megamenu")->__('No')
        );
        $yesno[] = array(
            'value' => '1',
            'text'  => Mage::helper("ves_megamenu")->__('Yes')
        );

        $fields  = array(
            'category_id' => array( 'type' => 'tree', 'value' => '' ),
            'show_image'      => array( 'type' => 'select', 'value' => '', 'values'=> $yesno  ),
            'subcategory_level'      => array( 'type' => 'text', 'value' => '' ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
         
    }

   

    public function getWidgetImageForm( $args, $data  ){

        $fields  = array(   
            'image_path' => array( 'type' => 'file', 'value' => '' ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
    }
    /**
     * render widget HTML Form.
     */
    public function getWidgetProduct_carouselForm( $args, $data ){
        $types = array();
        $types[] = array(
            'value' => 'newest',
            'text'  => Mage::helper("ves_megamenu")->__('Products newest')
        );
        $types[] = array(
            'value' => 'bestseller',
            'text'  => Mage::helper("ves_megamenu")->__('Products Bestseller')
        );

        $types[] = array(
            'value' => 'special',
            'text'  => Mage::helper("ves_megamenu")->__('Products Special')
        );

        $types[] = array(
            'value' => 'featured',
            'text'  => Mage::helper("ves_megamenu")->__('Products Featured')
        );

        $yesno = array();
        $yesno[] = array(
            'value' => '0',
            'text'  => Mage::helper("ves_megamenu")->__('No')
        );
        $yesno[] = array(
            'value' => '1',
            'text'  => Mage::helper("ves_megamenu")->__('Yes')
        );

        $fields  = array(
            'category_id' => array( 'type' => 'tree', 'value' => '', 'required' => false ),
            'list_type' => array( 'type' => 'select', 'value' => '', 'values'=>$types ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' ),
            'limit'      => array( 'type' => 'text', 'value' => ''  ),
            'max_items'      => array( 'type' => 'text', 'value' => ''  ),
            'limit_cols'      => array( 'type' => 'text', 'value' => ''  ),
            'auto_play'      => array( 'type' => 'select', 'value' => '', 'values'=>$yesno  ),
            'speed'      => array( 'type' => 'text', 'value' => ''  )
            
        );
        return $this->_renderFormByFields( $fields, $data );
         
    }

    /**
     * render widget HTML Form.
     */
    public function getWidgetProduct_listForm( $args, $data ){
        $types = array();   
        $types[] = array(
            'value' => 'newest',
            'text'  => Mage::helper("ves_megamenu")->__('Products newest')
        );
        $types[] = array(
            'value' => 'bestseller',
            'text'  => Mage::helper("ves_megamenu")->__('Products Bestseller')
        );

        $types[] = array(
            'value' => 'special',
            'text'  => Mage::helper("ves_megamenu")->__('Products Special')
        );


        $fields  = array(
            'list_type' => array( 'type' => 'select', 'value' => '', 'values'=>$types ),
            'limit'      => array( 'type' => 'text', 'value' => ''  ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' )
        );
        return $this->_renderFormByFields( $fields, $data );
         
    }

    /**
     *
     */
    public function getWidgetProductForm( $args, $data ){
        $fields  = array(
            'product_id' => array( 'type' => 'text', 'value' => '' ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
    }   

    public function getWidgetVideo_codeForm( $args, $data  ){  
        $fields  = array(
            'video_code' => array( 'type' => 'textarea', 'value' => '', 'attrs' => 'cols="40" rows="6"'  )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    public function getWidgetStatic_blockForm( $args, $data  ){  
        $fields  = array(
            'static_id' => array( 'type' => 'static_blocks', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    public function getWidgetPage_blockForm( $args, $data  ){  
        $fields  = array(
            'page_id' => array( 'type' => 'page_blocks', 'value' => '' )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    public function getWidgetFeedForm( $args, $data  ){  
        $fields  = array(
            'feed_url' => array( 'type' => 'text', 'value' => '' ),
            'limit' => array( 'type' => 'text', 'value' => ''  )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    public function getWidgetVes_blogForm( $args, $data  ){  
        $fields  = array(
            'limit' => array( 'type' => 'text', 'value' => ''  )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    public function getWidgetVes_brandForm( $args, $data  ){  
         $fields  = array(
            'limit' => array( 'type' => 'text', 'value' => ''  ),
            'image_width' => array( 'type' => 'text', 'value' => '' ),
            'image_height' => array( 'type' => 'text', 'value' => ''),
            'layout' => array( 'type' => 'text', 'value' => '', 'description' => Mage::helper("ves_megamenu")->__("Input your custom layout for the widget. For example: list, it will require the phtml file brands<strong>_list</strong>.phtml in folder /template/ves/megamenu/widgets/")  )
        );

        return $this->_renderFormByFields( $fields, $data );
    }

    /**
     * render widget setting form with passed  fields. And auto fill data values in inputs.
     */
    protected function _renderFormByFields( $fields, $data ){
        $output = '<table class="form">';


        foreach( $fields as $widget => $field ){
            $label = str_replace("_"," ", $widget);
            $label = ucfirst($label);
            $output .= '<tr>';
            $output .=  '<td>'.Mage::helper("ves_megamenu")->__('Widget '.$label).'</td>';
            $input = '';
            $val = isset($data[$widget])?$data[$widget]:"";
            
            $attrs = isset($fields[$widget]['attrs'])?$fields[$widget]['attrs']:""; 
            $required = isset($field['required'])? $field['required'] : true;
            $required_atr = "";
            if($required) {
                $required_atr = "required-entry";
            }

            switch( $field['type']  ){
                case 'tree':
                    $html = "";

                    $input .='    <select id="category" class="myinput-text '.$required_atr.' widthinput" name="params['.$widget.']">';

                    $input .= $this->genCategoriesOption( $val, $required );
                    $input .= '</select>';
                    break;
                case 'static_blocks':
                    $html = "";
                    $input .='    <select id="static_block" class="myinput-text '.$required_atr.' widthinput" name="params['.$widget.']">';

                    $input .= $this->genStaticBlocksOption( $val );
                    $input .= '</select>';
                    break;
                case 'page_blocks':
                    $html = "";
                    $input .='    <select id="page_block" class="myinput-text '.$required_atr.' widthinput" name="params['.$widget.']">';

                    $input .= $this->genPageBlocksOption( $val );
                    $input .= '</select>';
                    break;
                case 'text':
                        $input .= '<input '.$attrs.' size="35" type="text" name="params['.$widget.']" value="'.$val.'">';  
                    break;
                case 'file':
                        $thumb = "";
                        $image = Mage::getBaseDir("media") . DS . str_replace("/", DS, $val);
                        if ($val && file_exists( $image )) {
                            $thumb = $this->resizeImage($val, 100, 100);
                        }

                        $input .= '<div class="image"><img src="'.$thumb.'" alt="" id="thumb'.$widget.'" />
                          <input type="hidden" name="params['.$widget.']" value="'.$val.'" id="image'.$widget.'"  />
                          <br/>
                          <input type="file" name="image" value=""/>
                          <br />
                          <br/>
                          <input type="checkbox" name="image[delete]" id="image_delete" value="1"/> <label for="image_delete">'. Mage::helper("ves_megamenu")->__('Delete Image ').'</label>
                           </div>';
                    
                break;
                case 'select':
                    $input .= '<select '.$attrs.' name="params['.$widget.']">';
                    $default_value = (isset($data['group_id']) && !empty($data['group_id']))?$data['group_id']:'';
                    $default_value = (isset($data['list_type']) && !empty($data['list_type']))?$data['list_type']:$default_value;
                        foreach( $field['values'] as $val ){
                            if($default_value == $val['value']){
                                $input .= '<option value="'.$val['value'].'" selected="selected">'.$val['text'].'</option>';
                            }else{
                                $input .= '<option value="'.$val['value'].'">'.$val['text'].'</option>';
                            }
                             
                        }
                    $input .= '</select>';
                    
                    break;
                case 'textarea':
                    $input .= '<textarea '.$attrs.' name="params['.$widget.']">'.$val.'</textarea>';
                    
                    break;
                case 'texteditor':
                    $input .= $this->initTextEditor();
                    $input .= $this->getElementEditor('params['.$widget.']','params['.$widget.']', $val);
                    break;
            }
            $description = isset($field['description'])?'<br/><div class="field-info">'.$field['description'].'</div>':'';
            $output .= '<td>'.$input.$description.'</td>';
            $output .= '</tr>';
        }   
        
        $output .= '</table>';

        return $output;
    }
    

    public function renderWidgetProductContent( $args, $data ){

        $output = '';
        
        if( $data ){
            
        }   

        return $output;
    }
    /**
     *
     */
    public function getWidgetContent( $type, $data){
        $method = "renderWidget".ucfirst($type).'Content';
        $args = array(); 


        if( method_exists( $this, $method ) ){  
            return $this->{$method}( $args, $data ); 
        }
        return ;
    }

    /**
     *
     */
    public function renderContent( $id ){
        $output = '';
        
        if( isset($this->widgets[$id]) ){
            $output .= $this->getWidgetContent( $this->widgets[$id]['type'], unserialize(base64_decode($this->widgets[$id]['params'])) );
        }
        
        return $output;
    }

    /**
     *
     */ 
    public function loadWidgets(){
        if( empty($this->widgets) ){
            $widgets = $this->getWidgets();
            foreach( $widgets as $widget ){
                $this->widgets[$widget['id']] =$widget;
            }
        }
    }

    function nodeToArray(Varien_Data_Tree_Node $node)
    {
            $result = array();
            $result['category_id'] = $node->getId();
            $result['parent_id'] = $node->getParentId();
            $result['name'] = $node->getName();
            $result['is_active'] = $node->getIsActive();
            $result['position'] = $node->getPosition();
            $result['level'] = $node->getLevel();
            $result['children'] = array();

            foreach ($node->getChildren() as $child) {
                $result['children'][] = $this->nodeToArray($child);
            }
            return $result;
    }

    function load_tree( $parentId = 1) {

            $store = Mage::app()->getStore()->getId();

            $tree = Mage::getResourceSingleton('catalog/category_tree')
                    ->load();

            $root = $tree->getNodeById($parentId);

            if($root && $root->getId() == $parentId) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            $collection = Mage::getModel('catalog/category')->getCollection()
                                ->setStoreId($store)
                                ->addAttributeToSelect('name')
                                ->addAttributeToSelect('is_active');

            $tree->addCollectionData($collection, true);

            return $this->nodeToArray($root);

    }

    function gen_tree($tree,$level, $value=0, $html = '') {
        $level ++;
        foreach($tree as $item) {
            $html .='<option value="'.$item['category_id'].'" '.($item['category_id'] == $value)?'selected="selected"':''.'>'. str_repeat("    ", $level).$item['name']."</option>";
            if($item['children']){
                $this->gen_tree($item['children'],$level, $value, $html);
            }
            
        }
        return $html;
    }

    function genCategoriesOption($select  = 0){
        $html = '';
        $collection = Mage::getModel("catalog/category")->getCollection();
        $categories = Mage::helper("ves_megamenu/treecategories")->getCategoriesTreeArray();
        $html .= '<option value="-1">'.$this->__('--------------------------------').'</option>';
        foreach($categories as $option) {
            $type = Mage::getModel("catalog/category")->load($option['value']);
            if($type->getData( "name" )) {
                if($select) {
                    $selectOption = '';
                    if($option['value'] == $select)
                        $selectOption = 'selected="selected"';
                    $html .= '<option value="'.$option['value'].'" '.$selectOption.'>(ID: '.$option['value'].")".$option['label'].'</option>';
                } else {
                    $html .= '<option value="'.$option['value'].'">(ID: '.$option['value'].")".$option['label'].'</option>';
                }
            }
        }
        return $html;
    }

    function genStaticBlocksOption($select  = 0){
        $html = '';
        $collection = $blocks = Mage::getModel('cms/block')->getCollection()
                                            ->addFilter("is_active", 1)
                                            ->getItems();
        $html .= '<option value="">'.$this->__('--------------------------------').'</option>';
        foreach($collection as $option) {
            if($select) {
                $selectOption = '';
                if($option->getIdentifier() == $select)
                    $selectOption = 'selected="selected"';
                $html .= '<option value="'.$option->getIdentifier().'" '.$selectOption.'>'.$option->getTitle().'</option>';
            } else {
                $html .= '<option value="'.$option->getIdentifier().'">'.$option->getTitle().'</option>';
            }
        }
        return $html;
    }

    function genPageBlocksOption($select  = 0){
        $html = '';
        $collection = $blocks = Mage::getModel('cms/page')->getCollection()
                                            ->addFilter("is_active", 1)
                                            ->getItems();
        $html .= '<option value="">'.$this->__('--------------------------------').'</option>';
        foreach($collection as $option) {
            if($select) {
                $selectOption = '';
                if($option->getIdentifier() == $select)
                    $selectOption = 'selected="selected"';
                $html .= '<option value="'.$option->getIdentifier().'" '.$selectOption.'>'.$option->getTitle().'</option>';
            } else {
                $html .= '<option value="'.$option->getIdentifier().'">'.$option->getTitle().'</option>';
            }
        }
        return $html;
    }

    public function getStoreId(){
        $store_id = Mage::app()->getRequest()->getParam('store_id');
        if(!$store_id) {
            if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getStore())) // store level
            {
                $store_id = Mage::getModel('core/store')->load($code)->getId();
            }
            elseif (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite())) // website level
            {
                $website_id = Mage::getModel('core/website')->load($code)->getId();
                $store_id = Mage::app()->getWebsite($website_id)->getDefaultStore()->getId();
            }
            else // default level
            {
                $store_id = 0;
            }
        }
        
        return $store_id;
    }



    public function renderwidget(){
        $widgets = Mage::app()->getRequest()->getParam('widgets');
        
        $output = "";
        if( $widgets ){
            $widgets = explode( '|wid-', '|'.$widgets );

            if( !empty($widgets) ){
                unset( $widgets[0] );
                $output = '';
                foreach( $widgets as $wid ){
                    $output .= Mage::getModel('ves_megamenu/widget')->renderContent( $wid );
                }
            }
         
        }
        return $output;
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
                $uploader->setAllowedExtensions(array('csv','txt', 'json')); // or pdf or anything
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::helper('ves_megamenu/data')->getImportPath();
                $file_type = "csv";
                if($_FILES['importfile']['tmp_name']['type'] == "application/json") {
                    $file_type = "json";
                }
                $uploader->save($path, "ves_magento_sample_data.".$file_type);
                $filepath = $path . "ves_magento_sample_data.".$file_type;

            } catch(Exception $e) {
                // log error
                Mage::logException($e);
            } // end if

        } // end if

        return $filepath;

    }

    public function getImportPath($theme = ""){
        $path = Mage::getBaseDir('var') . DS . 'cache'.DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
    }

    /*Import sample data from json*/
    public function importSample( $content = "", $module ="", $type = "json", $override = true) {
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');
         
        /**
         * Retrieve the write connection
         */
        $writeConnection = $resource->getConnection('core_write');

        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');

        switch ($type) {
            case 'csv' :

            break;
            case 'json':
            default:
               
                $data = Mage::helper('core')->jsonDecode($content);

                if(!empty($data) && is_array($data)) {
                    foreach($data as $key=>$val) {
                        /*Import Module Config*/
                        if($key == "config" && $val) {
                            foreach($val as $tmp_key => $tmp_val) {
                               if($tmp_key == "import_stores") { //Check multil stores to import
                                    /*For each config field group to store data for fields*/
                                    foreach($tmp_val['import_stores'] as $k2=>$v2) {
                                        foreach($tmp_val as $config_key => $config_value) {
                                            Mage::getConfig()->saveConfig($module.'/'.$tmp_key.'/'.$config_key, $config_value );
                                        }
                                    }
                                    
                               } else {
                                    /*For each config field group to store data for fields*/
                                    foreach($tmp_val as $config_key => $config_value) {
                                        Mage::getConfig()->saveConfig($module.'/'.$tmp_key.'/'.$config_key, $config_value );
                                    }
                               }
                               
                            }
                           
                            
                        } else if($val) { //Import Table Data
                            $table_name = $resource->getTableName($key);
                            if($table_name) {

                                foreach($val as $item_query){
                                    if($item_query) {
                                        $query = $this->buildQueryImport( $item_query, $table_name, $override);
                                        $writeConnection->query($query);
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }
        return true;
    }

    public function buildQueryImport($data = array(), $table_name = "", $override = true) {
        $query = false;
        if($data) {
            if($override) {
                $query = "REPLACE INTO `".$table_name."` ";
                
            } else {
                $query = "INSERT IGNORE INTO `".$table_name."` ";
            }
            $fields = array();
            $values = array();

            foreach($data as $key=>$val) {
                if($val) {
                   $fields[] = "`".$key."`";
                   $values[] = "'".str_replace("'", "\'", $val)."'"; 
                }
            }
            $query .= " (".implode(",", $fields).") VALUES (".implode(",", $values).")";
        }
        return $query;
    }

    /*Export module sample data: support CSV and JSON*/
    public function exportSample($type = "json") {
        $data = array();
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');
         
        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');
         
        $query = 'SELECT * FROM ' . $resource->getTableName('ves_megamenu/megamenu');
         
        /**
         * Execute the query and store the results in $results
         */
        $megamenus = $readConnection->fetchAll($query);

        $data['ves_megamenu/megamenu'] = $megamenus;

        $query = 'SELECT * FROM ' . $resource->getTableName('ves_megamenu/megamenu_widget');
         
        /**
         * Execute the query and store the results in $results
         */
        $widgets = $readConnection->fetchAll($query);

        $data['ves_megamenu/megamenu_widget'] = $widgets;

        $query = 'SELECT * FROM ' . $resource->getTableName('ves_megamenu/megamenu_store');
         
        /**
         * Execute the query and store the results in $results
         */
        $stores = $readConnection->fetchAll($query);

        $data['ves_megamenu/megamenu_store'] = $stores;

        $config = Mage::getStoreConfig('ves_megamenu'); //array

        $data['config'] = $config;
        
        return Mage::helper('core')->jsonEncode($data);
    }
}

?>