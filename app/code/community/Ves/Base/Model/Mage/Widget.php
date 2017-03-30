<?php
class Ves_Base_Model_Mage_Widget extends Mage_Widget_Model_Widget
{
    public function getWidgetDeclaration($type, $params = array(), $asIs = true)
    {
        $field_pattern = array("pretext","description", "shortcode","html","raw_html","content","latestmod_desc","custom_css","block_params");
        $widget_types = array("ves_base/widget_accordionbg");

        foreach ($params as $k => $v) {
            if(in_array($k, $field_pattern) || preg_match("/^description_(.*)/", $k) || preg_match("/^content_(.*)/", $k) || ( preg_match("/^header_(.*)/", $k) && in_array($type, $widget_types) ) ){
                $params[$k] = base64_encode($params[$k]);
            }
        }

        return parent::getWidgetDeclaration($type, $params, $asIs);
    }
}