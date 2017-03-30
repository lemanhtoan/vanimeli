<?php 
class Ves_ProductCarousel_Model_Mage_Widget extends Mage_Widget_Model_Widget
{
    public function getWidgetDeclaration($type, $params = array(), $asIs = true)
    {

        if( preg_match('~(^ves_productcarousel/widget_carousel)~', $type) )
        {
            $params['pretext'] = base64_encode($params['pretext']);
        }

        return parent::getWidgetDeclaration($type, $params, $asIs);
    }
}