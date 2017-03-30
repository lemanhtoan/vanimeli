<?php
class Ves_Base_Block_Adminhtml_Builder_Edit_Initform extends Mage_Core_Block_Template
{
    var $_model = null;
    /**
     * Contructor
     */
    public function __construct($attributes = array())
    {

        $value = "";
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
        }
        if(isset($attributes['model'])) {
            $this->_model = $attributes['model'];
        } else {
            $this->_model = Mage::registry("block_data");
        }

        //Get current layout profile params
        $params = $this->_model->getParams();

        if(1 ==  Mage::registry('is_productbuilder') && empty($params)) { //get default layout when create new product profile
            $params = Mage::helper("ves_base")->getDefaultProductLayout();
        }

        $placeholder = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg',array('_area'=>'frontend'));

        //Get list sample layout profile params
        $sample_params = Mage::helper("ves_base")->getSampleLayoutParams();

        $backup_params = array();
        
        if(Mage::getStoreConfig("ves_blockbuilder/ves_blockbuilder/auto_backup_profile") && (1 ==  Mage::registry('is_productbuilder') || 1 == Mage::registry('is_pagebuilder'))) {
            $folder = "";
            if(1 ==  Mage::registry('is_productbuilder')){ //Load sample profile of product when we are managing product layout builder
              $folder = "vesproductbuilder";
            } elseif(1 == Mage::registry('is_pagebuilder')){ //Load sample profile of page when we are managing page layout builder
              $folder = "vespagebuilder";
            }
            $backup_params = Mage::helper("ves_base")->getBackupLayouts( $folder );
        }
        //Get available widgets in magento
        $avaialable_widgets = $this->_getAvailableWidgets();

        $widgets_info = Mage::helper("ves_base/widget")->getListWidgetTypes("array", $avaialable_widgets);
        $widgets_json = $widgets_info?Zend_Json::encode( $widgets_info ): "";
        $widgets_json = str_replace( array('\n','\r','\t') ,"", $widgets_json);

        $block_widgets = $this->_model->getWidgets();
        $tmp_widgets = array();
        if($block_widgets) {
          foreach($block_widgets as $key=>$val) {
              $tmp = array();
              $tmp['wkey'] = $key;
              $tmp['shortcode'] = $val;
              $tmp_widgets[] = $tmp;
          }
        }

        $block_widgets_json = $tmp_widgets?Zend_Json::encode( $tmp_widgets ): "";
        $block_widgets_json = str_replace( array('\n','\r','\t') ,"", $block_widgets_json);
        
        $this->assign("widgets_json", $widgets_json);
        $this->assign("widgets", $widgets_info);
        $this->assign("block_widgets_json", $block_widgets_json);
        $this->assign("placeholder", $placeholder);
        $this->assign("value", $value);
        $this->assign("builder_data", $this->_model);
        $this->assign("sample_params", $sample_params);
        $this->assign("backup_params", $backup_params);
        $this->assign("params", $params);

        $this->setTemplate("ves_base/builder/initform.phtml");

        parent::__construct();
    }
    /**
     * Return array of available widgets based on configuration
     *
     * @return array
     */
    protected function _getAvailableWidgets()
    {
        $result = array();
        $allWidgets = Mage::getModel('widget/widget')->getWidgetsArray();

        $skipped = $this->_getSkippedWidgets();
        foreach ($allWidgets as $widget) {
            if (is_array($skipped) && in_array($widget['type'], $skipped)) {
                continue;
            }
            $result[] = $widget;
        }

        return $result;
    }
    protected function _getSkippedWidgets() {
        return null;
    }
    /**
     * Rendering block content
     *
     * @return string
     */
    function _toHtml() 
    {   
      
        return parent::_toHtml();
    }
    protected function getBlock()
    {
        return $this->_model;
    }

    public function getRowClass() {
        return array(   "default",
                        "primary",
                        "success",
                        "info",
                        "warning",
                        "danger",
                        "highlighted",
                        "darked",
                        "nopadding",
                        "no-padding",
                        "no-margin"
                    );
    }
    public function getRowRepeats() {
        return array(   "" => Mage::helper("ves_base")->__("Theme Default"),
                        "repeat" => Mage::helper("ves_base")->__("Repeat"),
                        "repeat-x" => Mage::helper("ves_base")->__("Repeat X"),
                        "repeat-y" => Mage::helper("ves_base")->__("Repeat Y"),
                        "no-repeat" => Mage::helper("ves_base")->__("No Repeat"),
                        "inherit" => Mage::helper("ves_base")->__("Inherits from parent element")
                    );
    }
    public function getRowAttachments() {
        return array(   "" => Mage::helper("ves_base")->__("Theme Default"),
                        "scroll" => Mage::helper("ves_base")->__("The background scrolls along with the element"),
                        "fixed" => Mage::helper("ves_base")->__("The background is fixed with regard to the viewport"),
                        "local" => Mage::helper("ves_base")->__("The background scrolls along with the elements contents"),
                        "inherit" => Mage::helper("ves_base")->__("Inherits from parent element")
                    );
    }
    public function getRowPositions() {
        return array(   "" => Mage::helper("ves_base")->__("Theme Default"),
                        "left top" => Mage::helper("ves_base")->__("left top"),
                        "left center" => Mage::helper("ves_base")->__("left center"),
                        "left bottom" => Mage::helper("ves_base")->__("left bottom"),
                        "right top" => Mage::helper("ves_base")->__("right top"),
                        "right center" => Mage::helper("ves_base")->__("right center"),
                        "right bottom" => Mage::helper("ves_base")->__("right bottom"),
                        "center top" => Mage::helper("ves_base")->__("center top"),
                        "center center" => Mage::helper("ves_base")->__("center center"),
                        "center bottom"  => Mage::helper("ves_base")->__("center bottom")
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

    public function getWidgetClasses() {
        return array("" => Mage::helper("adminhtml")->__("Default"),
                    "primary" => Mage::helper("adminhtml")->__("Primary"),
                    "danger" => Mage::helper("adminhtml")->__("Danger"),
                    "info" => Mage::helper("adminhtml")->__("Info"),
                    "warning" => Mage::helper("adminhtml")->__("Warning"),
                    "highlighted" => Mage::helper("adminhtml")->__("Highlighted"),
                    "nopadding" => Mage::helper("adminhtml")->__("Nopadding"),
                    "darked" => Mage::helper("adminhtml")->__("Darked"),
                    "no-padding" => Mage::helper("adminhtml")->__("no-padding"),
                    "no-margin" => Mage::helper("adminhtml")->__("no-margin")
                    );
    }

    public function getOffCanvasTypes() {
        return array("" => Mage::helper("adminhtml")->__("Disable"),
                    "left" => Mage::helper("adminhtml")->__("Enable Left Sidebar"),
                    "right" => Mage::helper("adminhtml")->__("Enable Right Sidebar"),
                    "both" => Mage::helper("adminhtml")->__("Enable Both Left & Right Sidebar")
                    );
    }

    public function getOffColTypes() {
        return array("" => Mage::helper("adminhtml")->__("Default"),
                    "left" => Mage::helper("adminhtml")->__("Offcanvas Left"),
                    "right" => Mage::helper("adminhtml")->__("Offcanvas Right"),
                    "main-column" => Mage::helper("adminhtml")->__("Main Column")
                    );
    }

}
