<?php
 /*------------------------------------------------------------------------
  # Ves ContentTab Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Landingpage_Block_Adminhtml_Slider extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
		
        $this->_controller = 'adminhtml_landingpage';
        $this->_blockGroup = 'ves_landingpage';
        $this->_headerText = Mage::helper('ves_landingpage')->__('Landingpage Manager');

        parent::__construct();
        $this->setTemplate('ves_landingpage/slider.phtml');
        $helper =  Mage::helper('ves_landingpage/data');
    }

    public function getEffectConfig( $key ){
      return $this->getConfig( $key, "effect_setting" );
    }
    /**
     * get value of the extension's configuration
     *
     * @return string
     */
    function getConfig( $key, $panel='ves_landingpage' ){
      if(isset($this->_config[$key])) {
        return $this->_config[$key];
      } else {
        return Mage::getStoreConfig("ves_landingpage/$panel/$key");
      }
    }

    protected function _prepareLayout() {
  
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_landingpage')->__('Add Record'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
    
        $this->setChild('grid', $this->getLayout()->createBlock('ves_landingpage/adminhtml_slider_grid', 'slider.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }
    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }
    //public function getStoreSwitcherHtml() {
     //   return $this->getChildHtml('store_switcher');
    //}
}