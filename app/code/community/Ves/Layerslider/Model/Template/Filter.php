<?php

class Ves_Layerslider_Model_Template_Filter extends Mage_Widget_Model_Template_Filter{
    public function vessliderDirective($construction){
        $params = $this->_getIncludeParameters($construction[2]); 
        // Determine what name block should have in layout
        
        $alias = null;
        if (isset($params['alias'])) {
            $alias = $params['alias'];
        }
        // define layerslider block and check the type is instance of Block Interface
        $block = Mage::app()->getLayout()->createBlock("ves_layerslider/list", "venuslayerslider.".$alias, $params);

        return $block->toHtml();
    }
   
}