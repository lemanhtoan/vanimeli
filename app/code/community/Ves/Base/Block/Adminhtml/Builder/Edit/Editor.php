<?php
class Ves_Base_Block_Adminhtml_Builder_Edit_Editor extends Mage_Core_Block_Template
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

        $this->assign("params", $params);

        $this->setTemplate("ves_base/builder/editor.phtml");

        parent::__construct();
    }
}
