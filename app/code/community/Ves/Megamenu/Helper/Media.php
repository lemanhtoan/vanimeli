<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_Megamenu_Helper_Media extends Mage_Core_Helper_Abstract {

    /**
     * 
     * Add media file ( js, css ) ...
     * @param $type string media type (js, skin_css)
     * @param $source string source path
     * @param $before boolean true/false
     * @param $params mix 
     * @param $if string
     * @param $cond string
     */
    function addMediaFile($type = "", $source = "", $before = false, $params = null, $if = "", $cond = "") {
        $_head = Mage::getSingleton('core/layout')->getBlock('head');
        if (is_object($_head) && !empty($source)) {
            $items = $_head->getData('items');
            $tmpItems = array();
            $search = $type . "/" . $source;
            if (is_array($items)) {
                $key_array = array_keys($items);
                foreach ($key_array as &$_key) {
                    if ($search == $_key) {
                        $tmpItems[$_key] = $items[$_key];
                        unset($items[$_key]);
                    }
                }
            }
            if ($type == 'skin_css' && empty($params)) {
                $params = 'media="all"';
            }
            if (empty($tmpItems)) {
                $tmpItems[$type . '/' . $source] = array(
                    'type' => $type,
                    'name' => $source,
                    'params' => $params,
                    'if' => $if,
                    'cond' => $cond,
                );
            }
            if ($before) {
                $items = array_merge($tmpItems, $items);
            } else {
                $items = array_merge($items, $tmpItems);
            }
            $_head->setData('items', $items);
        }
    }
     public function loadMedia(){
        if ( !defined("_LOAD_JQUERY_") ) {
            $this->addMediaFile("js", "venustheme/ves_megamenu/jquery/jquery-1.7.1.min.js");
             $this->addMediaFile("js", "venustheme/ves_megamenu/jquery/conflict.js");
            define( "_LOAD_JQUERY_",1 );
        }
        $this->addMediaFile("js", "venustheme/ves_megamenu/jquery/ui/jquery-ui-1.8.16.custom.min.js");
        $this->addMediaFile("js", "venustheme/ves_megamenu/jquery/tabs.js");
        $this->addMediaFile("js", "venustheme/ves_megamenu/jquery/jquerycookie.js");
        $this->addMediaFile("js", "venustheme/ves_megamenu/admin/megamenu/jquery.nestable.js");

        $this->addMediaFile("skin_css", "ves_megamenu/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css");

        $this->addMediaFile("skin_css", "ves_megamenu/css/stylesheet.css");
        $this->addMediaFile("skin_css", "ves_megamenu/css/megamenu.css");
    }
    public function loadMediaLiveEdit(){
        $this->addMediaFile("js_css", "venustheme/ves_megamenu/admin/megamenu/css/bootstrap.css");
        $this->addMediaFile("js_css", "venustheme/ves_megamenu/admin/megamenu/css/font-awesome.min.css");
        $this->addMediaFile("skin_css", "ves_megamenu/css/megamenu_live.css");
        $this->addMediaFile("skin_css", "ves_megamenu/css/stylesheet.css");
        $this->addMediaFile("skin_css", "ves_megamenu/css/style.css");

        $this->addMediaFile("js", "venustheme/ves_megamenu/admin/megamenu/bootstrap.js");
        $this->addMediaFile("js", "venustheme/ves_megamenu/admin/megamenu/editor.js");

        return;
    }

}