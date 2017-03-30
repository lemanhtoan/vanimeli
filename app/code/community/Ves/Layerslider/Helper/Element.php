<?php

class Ves_Layerslider_Helper_Element extends Mage_Core_Helper_Abstract {

	public function getElementList($name, $element_name, $value = "", $attr = ""){
    	
    }
	public function getElementTextarea($name, $element_name, $value = "", $attr = ""){
    	
    }
    public function getElementInput($name, $element_name, $value = "", $attr = ""){

    }
    public function getElementStores($name, $element_name, $value = array(), $attr = ""){
    	if(!is_array($value)) {
    		$value = array();
    	}
    	$html = '<select multiple="multiple" class="select multiselect" size="10" title="Store View" name="'.$element_name.'" id="'.$name.'"'.$attr.'>';
    	if(empty($value) || in_array(0,$value)){
    		$html .= '<option value="0" selected="selected">'.Mage::helper("ves_layerslider")->__("All Store Views").'</option>';
    	}else{
    		$html .= '<option value="0">'.Mage::helper("ves_layerslider")->__("All Store Views").'</option>';
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
                        <button type="button" class="scalable show-hide" style="" id="toggle'.$textarea_name.'"><span><span><span>'.Mage::helper('ves_layerslider')->__('Show / Hide Editor').'</span></span></span></button>
                        <button type="button" class="scalable add-widget plugin" onclick="widgetTools.openDialog(\''.$this->getWidgetLink(array('widget_target_id'=>$textarea_name)).'\')" style="display:none"><span><span><span>'.Mage::helper('ves_layerslider')->__('Insert Widget...').'</span></span></span></button>
                        <button type="button" class="scalable add-image plugin" onclick="MediabrowserUtility.openDialog(
                        \''.$this->getImagesLink(array('target_element_id'=>$textarea_name)).'\')" style="display:none"><span><span><span>'.Mage::helper('ves_layerslider')->__('Insert Image...').'</span></span></span></button>

                        <button type="button" class="scalable add-variable plugin" onclick="MagentovariablePlugin.loadChooser(\''.$this->getVariablesLink().'\', \''.$textarea_name.'\');" style="display:none;"><span><span><span>'.Mage::helper('ves_layerslider')->__('Insert Variable...').'</span></span></span></button></div>

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

						 "content_css" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css',

						 "magentovariable" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/mage/adminhtml/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js',

						 "variables" => $this->getCustomLink('*/system_variable/wysiwygPlugin'),

						 "browse_images" => $this->getCustomLink('*/cms_wysiwyg_images/index'),

						 "widget_js" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'/mage/adminhtml/wysiwyg/tiny_mce/plugins/magentowidget/editor_plugin.js',

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
		<?php
		$html = ob_get_contents(); 
		ob_end_clean();
		return $html;
    }

    public function resizeImage($image, $width = 100, $height = 100){
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$_imageResized = Mage::getBaseDir('media').DS."resized".DS.$image;
		if (!file_exists($_imageResized)&&file_exists($_imageUrl)){
		    $imageObj = new Varien_Image($_imageUrl);
		    $imageObj->constrainOnly(TRUE);
		    $imageObj->keepAspectRatio(TRUE);
		    $imageObj->keepFrame(FALSE);
		    $imageObj->resize($width, $height);
		    $imageObj->save($_imageResized);
		}
		return Mage::getBaseUrl("media")."resized/".$image;
	}

   	public function getCustomLink($route , $params = array()){
        $link =  Mage::helper("adminhtml")->getUrl($route, $params);
        $link = str_replace("/adminhtml/","/", $link);
        $link = str_replace("/layerslider/","/", $link);
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

}