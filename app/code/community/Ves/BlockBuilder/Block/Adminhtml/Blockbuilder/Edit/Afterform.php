<?php
class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Afterform extends Mage_Adminhtml_Block_Widget_Container
{
    public function getBlockData()
    {
        return Mage::registry('block_data');
    }

    public function getRowClass() {
    	return array( 	"default",
	    				"primary",
	    				"success",
	    				"info",
	    				"warning",
	    				"danger"
	    			);
    }
    public function getRowRepeats() {
        return array(   "" => Mage::helper("ves_blockbuilder")->__("Theme Default"),
                        "repeat" => Mage::helper("ves_blockbuilder")->__("Repeat"),
                        "repeat-x" => Mage::helper("ves_blockbuilder")->__("Repeat X"),
                        "repeat-y" => Mage::helper("ves_blockbuilder")->__("Repeat Y"),
                        "no-repeat" => Mage::helper("ves_blockbuilder")->__("No Repeat"),
                        "inherit" => Mage::helper("ves_blockbuilder")->__("Inherits from parent element")
                    );
    }
    public function getRowAttachments() {
        return array(   "" => Mage::helper("ves_blockbuilder")->__("Theme Default"),
                        "scroll" => Mage::helper("ves_blockbuilder")->__("The background scrolls along with the element"),
                        "fixed" => Mage::helper("ves_blockbuilder")->__("The background is fixed with regard to the viewport"),
                        "local" => Mage::helper("ves_blockbuilder")->__("The background scrolls along with the elements contents"),
                        "inherit" => Mage::helper("ves_blockbuilder")->__("Inherits from parent element")
                    );
    }
    public function getRowPositions() {
        return array(   "" => Mage::helper("ves_blockbuilder")->__("Theme Default"),
                        "left top" => Mage::helper("ves_blockbuilder")->__("left top"),
                        "left center" => Mage::helper("ves_blockbuilder")->__("left center"),
                        "left bottom" => Mage::helper("ves_blockbuilder")->__("left bottom"),
                        "right top" => Mage::helper("ves_blockbuilder")->__("right top"),
                        "right center" => Mage::helper("ves_blockbuilder")->__("right center"),
                        "right bottom" => Mage::helper("ves_blockbuilder")->__("right bottom"),
                        "center top" => Mage::helper("ves_blockbuilder")->__("center top"),
                        "center center" => Mage::helper("ves_blockbuilder")->__("center center"),
                        "center bottom"  => Mage::helper("ves_blockbuilder")->__("center bottom")
                    );
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
                  array('value' => "slideInUp", 'label'=>Mage::helper('adminhtml')->__('slideInUp')),
                  array('value' => "slideInDown", 'label'=>Mage::helper('adminhtml')->__('slideInDown')),
                  array('value' => "slideInLeft", 'label'=>Mage::helper('adminhtml')->__('slideInLeft')),
                  array('value' => "slideInRight", 'label'=>Mage::helper('adminhtml')->__('slideInRight')),
                  array('value' => "slideOutUp", 'label'=>Mage::helper('adminhtml')->__('slideOutUp')),
                  array('value' => "slideOutDown", 'label'=>Mage::helper('adminhtml')->__('slideOutDown')),
                  array('value' => "slideOutLeft", 'label'=>Mage::helper('adminhtml')->__('slideOutLeft')),
                  array('value' => "slideOutRight", 'label'=>Mage::helper('adminhtml')->__('slideOutRight')),
                  array('value' => "hinge", 'label'=>Mage::helper('adminhtml')->__('hinge')),
                  array('value' => "rollIn", 'label'=>Mage::helper('adminhtml')->__('rollIn')),
                  array('value' => "zoomIn", 'label'=>Mage::helper('adminhtml')->__('zoomIn')),
                  array('value' => "zoomInDown", 'label'=>Mage::helper('adminhtml')->__('zoomInDown')),
                  array('value' => "zoomInLeft", 'label'=>Mage::helper('adminhtml')->__('zoomInLeft')),
                  array('value' => "zoomInRight", 'label'=>Mage::helper('adminhtml')->__('zoomInRight')),
                  array('value' => "zoomInUp", 'label'=>Mage::helper('adminhtml')->__('zoomInUp'))
                  );
    }

    public function getWidgetClasses() {
        return array("" => Mage::helper("adminhtml")->__("Default"),
                    "primary" => Mage::helper("adminhtml")->__("Primary"),
                    "danger" => Mage::helper("adminhtml")->__("Danger"),
                    "info" => Mage::helper("adminhtml")->__("Info"),
                    "warning" => Mage::helper("adminhtml")->__("Warning"),
                    "highlighted" => Mage::helper("adminhtml")->__("Highlighted"),
                    "nopadding" => Mage::helper("adminhtml")->__("Nopadding")
                    );
    }

                                                                                                                                                                                        
}
